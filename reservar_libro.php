<?php
require_once __DIR__ . '/controllers/auth_middleware.php';
require_once './config/Database.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: prueba.php?error=not_logged_in');
    exit;
}

// Verificar método POST y libro_id recibido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['libro_id'])) {
    $libroId = $_POST['libro_id'];
    $usuarioId = $_SESSION['user_id'];

    // Conectar a base de datos
    $database = new Database();
    $conn = $database->connect();

    // Verificar que el libro existe y está disponible
    $checkSql = "SELECT id, cantidad_disponible FROM libros WHERE id = :libro_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([':libro_id' => $libroId]);
    $libro = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$libro || $libro['cantidad_disponible'] <= 0) {
        header('Location: prueba.php?error=no_disponible');
        exit;
    }

    // Verificar que el usuario no tenga ya este libro prestado
    $existingSql = "SELECT id FROM prestamos WHERE usuario_id = :usuario_id AND libro_id = :libro_id AND estado = 'activo'";
    $existingStmt = $conn->prepare($existingSql);
    $existingStmt->execute([':usuario_id' => $usuarioId, ':libro_id' => $libroId]);
    
    if ($existingStmt->fetch()) {
        header('Location: prueba.php?error=ya_reservado');
        exit;
    }

    // Calcular fecha de devolución (15 días desde hoy)
    $fechaDevolucion = date('Y-m-d', strtotime('+15 days'));

    try {
        $conn->beginTransaction();

        // Insertar préstamo
        $sql = "INSERT INTO prestamos (usuario_id, libro_id, fecha_devolucion_esperada, estado) VALUES (:usuario_id, :libro_id, :fecha_devolucion, 'activo')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':libro_id' => $libroId,
            ':fecha_devolucion' => $fechaDevolucion
        ]);

        // Actualizar cantidad disponible del libro
        $updateSql = "UPDATE libros SET cantidad_disponible = cantidad_disponible - 1 WHERE id = :libro_id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([':libro_id' => $libroId]);

        $conn->commit();
        
        // Redirigir con éxito
        header('Location: prueba.php?success=reservado');
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        header('Location: prueba.php?error=sql_error');
        exit;
    }
} else {
    // Redirigir si no es POST o falta libro_id
    header('Location: prueba.php?error=no_libro');
    exit;
}
