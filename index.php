<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Database.php';

require __DIR__ . '/src/Comments.php';

// Import info from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set up router
$router = new \Bramus\Router\Router();

// Set the headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header('Content-Type: application/json');

// We need this for any CORS requests, doesn't have to do anything
$router->options('.*', function() {
  echo '👍';
});

// Now we can set up the routes
// Starting with the one to get all the comments
$router->get('/comments/getComments', function() {
  // Let's make a new DB connection
  $databaseHandler = new Database();
  $db = $databaseHandler->getConnection();

  // Then just get all the comments here
  $commentsHandler = new Comments($db);
  $comments = $commentsHandler->getComments();

  $databaseHandler->closeConnection();

  // So we've now got the comments, so let's just encode and output them
  die(json_encode($comments));
});

// Now one to post a new comment
$router->post('/comments/postComment', function() {
  // Let's make a new DB connection
  $databaseHandler = new Database();
  $db = $databaseHandler->getConnection();

  $commentsHandler = new Comments($db);
  $commentsHandler->postComment();

  $databaseHandler->closeConnection();

  // If we've got this far without dying then the comment has probably posted
  die(json_encode(['message' => 'Comment successfully posted!']));
});

$router->run();
?>