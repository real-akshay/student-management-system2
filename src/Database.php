<?php

class Database {
    private static $instance = null;
    private $conn;

    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $name = DB_NAME;

    private function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        } catch (mysqli_sql_exception $e) {
            // Should be handled by a proper error logging/reporting mechanism
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * Prepare a SQL statement for execution
     * @param string $sql The SQL statement to prepare.
     * @return mysqli_stmt|false
     */
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    /**
     * Execute a simple query
     * @param string $sql The SQL query to execute.
     * @return mysqli_result|bool
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }

    /**
     * Get the last inserted ID
     * @return int|string
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    /**
     * Close the database connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
