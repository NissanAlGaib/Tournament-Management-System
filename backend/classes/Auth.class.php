<?php
    class Authentication
    {
        private $conn;
        private $table_name = "users";

        public $id;
        public $username;
        public $email;
        public $password;

        public function __construct($db)
        {
            $this->conn = $db;
        }

        public function registerUser()
        {
            $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";

            $stmt = $this->conn->prepare($query);

            $this->username = htmlspecialchars(strip_tags($this->username));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);

            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        }
    }