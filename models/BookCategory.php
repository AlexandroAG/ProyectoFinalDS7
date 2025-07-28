<?php
require_once __DIR__ . '/../config/Database.php';

class BookCategory {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Obtener todas las categorías
    public function getAll() {
        $query = "SELECT * FROM categorias_libros ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener una categoría por ID
    public function getById($id) {
        $query = "SELECT * FROM categorias_libros WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear una nueva categoría
    public function create($data) {
        $query = "INSERT INTO categorias_libros (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        return $stmt->execute();
    }

    // Actualizar una categoría
    public function update($id, $data) {
        $query = "UPDATE categorias_libros SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        return $stmt->execute();
    }

    // Eliminar una categoría
    public function delete($id) {
        $query = "DELETE FROM categorias_libros WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Verificar si una categoría existe por nombre
    public function exists($nombre) {
        $query = "SELECT COUNT(*) FROM categorias_libros WHERE nombre = :nombre";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>