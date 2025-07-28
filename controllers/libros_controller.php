<?php
require_once __DIR__ . '/../config/database.php';

class LibrosController {
    private $conn;

    public function __construct() {
        $db = new Database();
    }

    // Agregar un nuevo libro
    public function agregarLibro($datos, $imagen) {
        // Validar datos
        if (empty($datos['titulo']) || empty($datos['autor']) || empty($datos['descripcion']) || empty($datos['categoria'])) {
            return ['error' => 'Todos los campos son obligatorios'];
        }

        // Procesar la imagen
        $imagenPath = $this->procesarImagen($imagen);
        if (isset($imagenPath['error'])) {
            return $imagenPath;
        }

        // Generar ISBN ficticio (en un sistema real sería único y validado)
        $isbn = $this->generarISBN();

        try {
            // Verificar si la categoría existe, si no, crearla
            $categoriaId = $this->obtenerOCrearCategoria($datos['categoria']);

            // Insertar el libro
            $query = "INSERT INTO libros (titulo, autor, isbn, categoria_id, descripcion, cantidad, cantidad_disponible, imagen_path) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            $cantidad = $datos['stock'] ?? 1;
            $stmt->bind_param("sssisiss", 
                $datos['titulo'], 
                $datos['autor'], 
                $isbn, 
                $categoriaId, 
                $datos['descripcion'], 
                $cantidad, 
                $cantidad, 
                $imagenPath
            );

            if ($stmt->execute()) {
                return ['mensaje' => 'Libro agregado exitosamente'];
            } else {
                return ['error' => 'Error al agregar el libro: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['error' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    // Obtener todos los libros
    public function obtenerLibros($filtros = []) {
        $query = "SELECT l.*, c.nombre as categoria_nombre 
                  FROM libros l 
                  LEFT JOIN categorias_libros c ON l.categoria_id = c.id 
                  WHERE 1=1";
        
        $params = [];
        $types = "";

        // Aplicar filtros
        if (!empty($filtros['search'])) {
            $query .= " AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ?)";
            $searchTerm = "%" . $filtros['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        if (!empty($filtros['category'])) {
            $query .= " AND l.categoria_id = ?";
            $params[] = $filtros['category'];
            $types .= "i";
        }

        $query .= " ORDER BY l.titulo ASC";

        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $libros = [];
        while ($row = $result->fetch_assoc()) {
            $libros[] = $row;
        }

        return $libros;
    }

    // Obtener todas las categorías
    public function obtenerCategorias() {
        $query = "SELECT * FROM categorias_libros ORDER BY nombre ASC";
        $result = $this->conn->query($query);

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }

        return $categorias;
    }

    // Métodos auxiliares
    private function procesarImagen($imagen) {
        if (!$imagen || $imagen['error'] != UPLOAD_ERR_OK) {
            return 'default_book.jpg'; // Imagen por defecto
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $imagen['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return ['error' => 'Formato de imagen no permitido. Use JPG, PNG o GIF.'];
        }

        $uploadDir = __DIR__ . '/../uploads/libros/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFilename = uniqid('libro_') . '.' . $ext;
        $destination = $uploadDir . $newFilename;

        if (move_uploaded_file($imagen['tmp_name'], $destination)) {
            return 'uploads/libros/' . $newFilename;
        } else {
            return ['error' => 'Error al subir la imagen.'];
        }
    }

    private function generarISBN() {
        return 'ISBN-' . uniqid();
    }

    private function obtenerOCrearCategoria($nombreCategoria) {
        // Primero verificar si ya existe
        $query = "SELECT id FROM categorias_libros WHERE nombre = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nombreCategoria);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }

        // Si no existe, crear la categoría
        $query = "INSERT INTO categorias_libros (nombre) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nombreCategoria);
        $stmt->execute();

        return $stmt->insert_id;
    }
}
?>