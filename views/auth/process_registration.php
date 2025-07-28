<?php
require_once __DIR__ . '/../../class/UniversalSatinizer.php';

// Configuración de entorno
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

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        header("Location: register.php");
        exit();
    }

    // Sanitizar y validar datos del formulario
    $data = [
        'primer_nombre' => $sanitizer->name($_POST['primer_nombre'] ?? ''),
        'segundo_nombre' => $sanitizer->name($_POST['segundo_nombre'] ?? ''),
        'primer_apellido' => $sanitizer->name($_POST['primer_apellido'] ?? ''),
        'segundo_apellido' => $sanitizer->name($_POST['segundo_apellido'] ?? ''),
        'cedula' => $sanitizer->cedulaPanama($_POST['cedula'] ?? ''),
        'telefono' => $sanitizer->phoneNumber($_POST['telefono'] ?? ''),
        'carrera' => $sanitizer->textArea($_POST['carrera'] ?? ''),
        'fecha_nacimiento' => $sanitizer->date($_POST['fecha_nacimiento'] ?? ''),
        'email' => $sanitizer->email($_POST['email'] ?? ''),
        'role' => $sanitizer->role($_POST['role'] ?? 'estudiante'),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];

    // Validaciones adicionales
    if (empty($data['primer_nombre'])) {
        throw new InvalidArgumentException("El primer nombre es obligatorio");
    }

    if (empty($data['primer_apellido'])) {
        throw new InvalidArgumentException("El primer apellido es obligatorio");
    }

    if ($data['password'] !== $data['confirm_password']) {
        throw new InvalidArgumentException("Las contraseñas no coinciden");
    }

    if (strlen($data['password']) < 6) {
        throw new InvalidArgumentException("La contraseña debe tener al menos 6 caracteres");
    }

    // Verificar si el email o la cédula ya existen
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? OR cedula = ?");
    if ($stmt_check === false) {
        throw new RuntimeException("Error al preparar la verificación de usuario/cédula");
    }

    $stmt_check->bind_param("ss", $data['email'], $data['cedula']);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        throw new InvalidArgumentException("El correo electrónico o la cédula ya están registrados");
    }
    $stmt_check->close();

    // Procesar imagen de usuario
    $image_path_for_db = handleProfileImageUpload($sanitizer);


    // Hashear la contraseña
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $stmt_insert = $conn->prepare("INSERT INTO usuarios 
        (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, 
         cedula, telefono, carrera, fecha_nacimiento, email, 
         password, role, imagen_perfil) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt_insert === false) {
        throw new RuntimeException("Error al preparar la inserción: " . $conn->error);
    }

    $stmt_insert->bind_param("ssssssssssss", 
        $data['primer_nombre'], $data['segundo_nombre'], $data['primer_apellido'], $data['segundo_apellido'], 
        $data['cedula'], $data['telefono'], $data['carrera'], $data['fecha_nacimiento'], $data['email'], 
        $hashed_password, $data['role'], $image_path_for_db
    );

    if (!$stmt_insert->execute()) {
        throw new RuntimeException("Error al registrar el usuario: " . $stmt_insert->error);
    }

    $stmt_insert->close();
    
    // Redirigir a página de éxito
    header("Location: registration_status.php?mensaje=" . urlencode("¡Registro exitoso! Ahora puedes iniciar sesión."));
    exit();

} catch (InvalidArgumentException $e) {
    // Errores de validación
    header("Location: registration_status.php?error=" . urlencode($e->getMessage()));
    exit();
} catch (RuntimeException $e) {
    // Errores de sistema/database
    header("Location: registration_status.php?error=" . urlencode("Ocurrió un error. Por favor intenta nuevamente."));
    exit();
} finally {
    // Cerrar conexión si existe
    if (isset($conn)) {
        $conn->close();
    }
}

/**
 * Maneja la subida de la imagen de perfil
 */
 function handleProfileImageUpload(UniversalSanitizer $sanitizer): ?string {
    if (!isset($_FILES['user_image']) || $_FILES['user_image']['error'] != 0) {
        return null;
    }

    $target_dir = "../../uploads/profile_images/";
    
    // Verificar y crear directorio si no existe
    if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true)) {
        throw new RuntimeException("No se pudo crear el directorio para imágenes");
    }

    if (!is_writable($target_dir)) {
        throw new RuntimeException("El directorio de imágenes no tiene permisos de escritura");
    }

    // Validar archivo
    $image_name = $sanitizer->fileName($_FILES["user_image"]["name"]);
    $imageFileType = $sanitizer->imageExtension($image_name);

    // Verificar tamaño (5MB máximo)
    if ($_FILES["user_image"]["size"] > 5000000) {
        throw new InvalidArgumentException("La imagen de perfil es demasiado grande. Máximo 5MB");
    }

    // Generar nombre único y mover archivo
    $new_image_name = uniqid('profile_', true) . '.' . $imageFileType;
    $target_file = $target_dir . $new_image_name;

    if (!move_uploaded_file($_FILES["user_image"]["tmp_name"], $target_file)) {
        throw new RuntimeException("Error al guardar la imagen de perfil");
    }

    return "uploads/profile_images/" . $new_image_name;
}
?>