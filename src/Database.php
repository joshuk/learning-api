<?php
class Database {
  public $connection = '';

  function __construct() {
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false
    ];

    try {
      $this->connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $options);
    } catch (\PDOException $e) {
      throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
  }

  function getConnection() {
    return $this->connection;
  }

  function closeConnection() {
    $this->connection = null;
  }
}