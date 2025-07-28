<?php
require_once __DIR__ . '/../../class/UniversalSatinizer.php';

// Habilitar errores solo en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'biblioteca');

try {
    // Establecer conexión
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new RuntimeException("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    // Inicializar sanitizador
    $sanitizer = new UniversalSanitizer($conn);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitizar y validar inputs
        $username_or_email = $sanitizer->basicString($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // La contraseña no se sanitiza, se verifica directamente

        // Validar campos requeridos
        if (empty($username_or_email) || empty($password)) {
            throw new InvalidArgumentException("Por favor, ingresa tu nombre de usuario/email y contraseña.");
        }

        // Buscar usuario (usando prepared statements)
        $stmt = $conn->prepare("SELECT id, primer_nombre, primer_apellido, email, password, role FROM usuarios WHERE email = ? OR cedula = ?");
        
        if ($stmt === false) {
            throw new RuntimeException("Error al preparar la consulta de login: " . $conn->error);
        }

        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Sanitizar datos antes de guardar en sesión
                $_SESSION['user_id'] = $sanitizer->integer($user['id']);
                $_SESSION['username'] = $sanitizer->name($user['primer_nombre'] . ' ' . $user['primer_apellido']);
                $_SESSION['user_email'] = $sanitizer->email($user['email']);
                $_SESSION['user_role'] = $sanitizer->role($user['role']);
                
                header("Location: ../../index.php");
                exit();
            }
        }

        // Mensaje genérico para evitar enumeración de usuarios
        throw new RuntimeException("Credenciales incorrectas. Por favor, inténtalo de nuevo.");
    }

    // Si no es POST, redirigir
    header("Location: login.php");
    exit();

} catch (InvalidArgumentException $e) {
    // Errores de validación
    header("Location: login.php?error=" . urlencode($e->getMessage()));
    exit();
} catch (RuntimeException $e) {
    // Errores de sistema/database
    header("Location: login.php?error=" . urlencode("Ocurrió un error. Por favor intenta nuevamente."));
    exit();
} finally {
    // Cerrar conexión si existe
    if (isset($conn)) {
        $conn->close();
    }
}