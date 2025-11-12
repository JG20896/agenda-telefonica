<?php
class Database {
    private $host = 'sql106.infinityfree.com';
    private $db_name = 'if0_40400706_agenda_telefonica'; 
    private $username = 'if0_40400706';
    private $password = '0vfBhImgPf8';
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
            return $this->conn;
        } catch(PDOException $e) {
            echo "<div style='color:red; padding:20px; border:2px solid red;'>";
            echo "<h3>❌ Error de Conexión a MySQL</h3>";
            echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>Host:</strong> " . $this->host . "</p>";
            echo "<p><strong>Base de datos:</strong> " . $this->db_name . "</p>";
            echo "<p><strong>Usuario:</strong> " . $this->username . "</p>";
            echo "<p><strong>¿Base de datos existe?:</strong> " . (function_exists('mysqli_connect') ? 'Verificar en panel' : 'No se puede verificar') . "</p>";
            echo "</div>";
            exit;
        }
    }
}
?>