<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'agenda_telefonica';
    private $username = 'root';
    private $password = 'MyNewPass1';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->conn;
        } catch(PDOException $exception) {
            echo "<div style='color: red; padding: 20px; border: 2px solid red;'>";
            echo "<h3>❌ Error de conexión a MySQL</h3>";
            echo "<p><strong>Mensaje:</strong> " . $exception->getMessage() . "</p>";
            echo "<p><strong>Host:</strong> " . $this->host . "</p>";
            echo "<p><strong>Base de datos:</strong> " . $this->db_name . "</p>";
            echo "<p><strong>Usuario:</strong> " . $this->username . "</p>";
            echo "<p><strong>Contraseña:</strong> " . (empty($this->password) ? '(vacía)' : '*****') . "</p>";
            echo "</div>";
            exit;
        }
    }
}
?>