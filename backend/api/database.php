<?php
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "tournament_db";
    private $connection;

    public function getConnection()
    {
        $this->connection = null;
        try {
            $this->connection = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Connection error: " . $e->getMessage()]);
            exit();
        }
        return $this->connection;
    }
}
