<?php
require_once __DIR__ . '/auth_middleware.php';
require_once __DIR__ . '/BookAdminController.php';
require_once __DIR__ . '/AuthController.php';

session_start();
$authController = new AuthController();
$userData = $authController->getProfileData();

if (empty($userData) || $userData['role'] !== 'admin') {
    header('Location: ../prueba.php?error=acceso_denegado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['libro_id'])) {
    $libroId = $_POST['libro_id'];
    $bookAdminController = new BookAdminController();
    $bookAdminController->deleteBook($libroId);
    header('Location: ../prueba.php?success=eliminado');
    exit;
} else {
    header('Location: ../prueba.php?error=no_libro');
    exit;
}
