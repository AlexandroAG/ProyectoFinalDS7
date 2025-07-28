<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();

    $query = "SELECT id, titulo, autor, isbn, categoria_id, descripcion, cantidad, cantidad_disponible, anio_publicacion, imagen_path, thumbnail_path, fecha_creacion FROM libros ORDER BY anio_publicacion ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=libros_exportados.csv');

    $output = fopen('php://output', 'w');

    // Cambiar la coma por punto y coma para Excel regionales en español
    fputcsv($output, [
        'ID', 'Título', 'Autor', 'ISBN', 'Categoría ID', 'Descripción', 'Cantidad Total', 'Cantidad Disponible', 'Año Publicación', 'Imagen Path', 'Thumbnail Path', 'Fecha Creación'
    ], ';');

    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $fila, ';');
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    echo "Error al exportar datos: " . $e->getMessage();
    exit;
}
