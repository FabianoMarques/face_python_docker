<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<?php

class Database {
    private $host = "mysql";
    private $user = "usuario";
    private $password = "senha";
    private $database = "face_recognition_db";
    private $port = 3306;
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
        if ($this->conn->connect_error) {
            die("Erro: Não foi possível conectar ao banco de dados. " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

?>
