<?php
class Comments {
  public $db = null;

  function __construct($db) {
    $this->db = $db;
  }

  function getComments($parent = -1, $recursive = true) {
    // This one is pretty simple, we just figure out if there is a parent set or not
    // If there is, get children. Otherwise, just get all comments.
    if ($parent === -1) {
      $parent = isset($_GET['replyTo']) ? $_GET['replyTo'] : -1;
    }

    if (isset($_GET['onlyParents'])) {
      $recursive = false;
    }

    // Right so we know what we're getting now, let's build the query
    $query = 'SELECT
                id, 
                author,
                comment,
                timestamp
              FROM
                comments
              WHERE
                replyTo = :replyTo';

    // Then prepare/execute it
    $stmt = $this->db->prepare($query);
    $stmt->execute([
      // It's weird that it doesn't let you use the same param twice in one statement
      ':replyTo' => $parent
    ]);
    $comments = $stmt->fetchAll();

    if (count($comments) && $recursive) {
      // So we wanna recursively get the children here
      // So let's loop through all the comments and get them
      foreach ($comments as $index => $comment) {
        $comments[$index]['children'] = $this->getComments($comment['id']);
      }
    }

    return $comments;
  }
  
  function postComment() {
    $input = json_decode(file_get_contents('php://input'));

    $author = $input->author;
    $comment = $input->comment;

    if (!isset($author) || empty($author) || !isset($comment) || empty($comment)) {
      header("HTTP/1.1 400 Bad Request");

      die(json_encode(['error' => 'Please provide required fields']));
    }

    $replyTo = $input->replyTo ?? -1;

    if ($replyTo !== -1) {
      // Do a check if the parent exists
      $query = 'SELECT
                  comment
                FROM
                  comments
                WHERE
                  id = :id';
      
      $stmt = $this->db->prepare($query);
      $stmt->execute([
        ':id' => $replyTo
      ]);
      $parentComment = $stmt->fetchAll();

      if (empty($parentComment)) {
        header("HTTP/1.1 400 Bad Request");

        die(json_encode(['error' => 'Please provide a valid parent comment']));
      }
    }

    $query = 'INSERT INTO 
                comments(replyTo, author, comment, timestamp)
              VALUES 
                (:replyTo, :author, :comment, :timestamp)';
    
    $stmt = $this->db->prepare($query);
    $success = $stmt->execute([
      ':replyTo' => $replyTo,
      ':author' => $author,
      ':comment' => $comment,
      ':timestamp' => time()
    ]);

    if (!$success) {
      header("HTTP/1.1 500 Bad Request");

      die(json_encode(['error' => 'Unknown issue posting comment']));
    }

    return true;
  }
}
?>