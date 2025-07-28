<?php
require_once __DIR__ . '/../controllers/auth_middleware.php';
require_once __DIR__ . '/../config/Database.php';

if (!isset($_GET['id'])) {
    die("ID de usuario no proporcionado.");
}

$id = intval($_GET['id']);

try {
    $db = new Database();
    $conn = $db->connect();

    // Eliminar usuario usando PDO
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirigir con éxito
        header("Location: ../views/auth/rol.php?eliminado=1");
    } else {
        // Redirigir con error
        header("Location: ../views/auth/rol.php?eliminado=0");
    }

    exit;
} catch (Exception $e) {
    die("Error al eliminar usuario: " . $e->getMessage());
}