<?php
require_once __DIR__ . '/../config/Database.php';

class BookAdminController {
    private $conn;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getBookById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM libros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBook($id, $data) {
        $sql = "UPDATE libros SET titulo = :titulo, autor = :autor, isbn = :isbn, descripcion = :descripcion, categoria_id = :categoria_id, cantidad = :cantidad, cantidad_disponible = :cantidad_disponible, anio_publicacion = :anio_publicacion WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':titulo' => $data['titulo'],
            ':autor' => $data['autor'],
            ':isbn' => $data['isbn'],
            ':descripcion' => $data['descripcion'],
            ':categoria_id' => $data['categoria_id'],
            ':cantidad' => $data['cantidad'],
            ':cantidad_disponible' => $data['cantidad_disponible'],
            ':anio_publicacion' => $data['anio_publicacion'],
            ':id' => $id
        ]);
    }

    public function deleteBook($id) {
        $stmt = $this->conn->prepare("DELETE FROM libros WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
