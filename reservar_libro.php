<?php
session_start();

require_once './config/Database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: prueba.php?error=not_logged_in');
    exit;
}

// Verificar método POST y libro_id recibido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['libro_id'])) {
    $libroId = $_POST['libro_id'];
    $usuarioId = $_SESSION['usuario_id'];

    // Conectar a base de datos
    $database = new Database();
    $conn = $database->connect();

    // Preparar la consulta de inserción
    $sql = "INSERT INTO reservas (libro_id, usuario_id, fecha_reserva, estado) VALUES (:libro_id, :usuario_id, NOW(), 'pendiente')";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([
            ':libro_id' => $libroId,
            ':usuario_id' => $usuarioId
        ]);
        // Redirigir con éxito
        header('Location: prueba.php?success=reservado');
        exit;
    } catch (PDOException $e) {
        // Puedes hacer log del error con $e->getMessage() si quieres
        header('Location: prueba.php?error=sql_error');
        exit;
    }
} else {
    // Redirigir si no es POST o falta libro_id
    header('Location: prueba.php?error=no_libro');
    exit;
}
