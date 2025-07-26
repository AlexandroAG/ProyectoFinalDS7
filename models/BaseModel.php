<?php
require_once __DIR__ . '/../config/Database.php';

class BaseModel {
    protected $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error en la consulta: " . $e->getMessage());
            return false;
        }
    }
}
?>