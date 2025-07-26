<?php
// Habilitar la visualización de errores para depuración (¡Quitar en producción!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Iniciar la sesión PHP

// Definir las constantes de conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'alex03');
define('DB_PASS', '0123456789'); 
define('DB_NAME', 'biblioteca_sencilla');

// Establecer conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    header("Location: registration_status.php?error=" . urlencode("Error de conexión a la base de datos: " . $conn->connect_error));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y sanear los datos del formulario
    $primer_nombre = $conn->real_escape_string($_POST['primer_nombre'] ?? '');
    $segundo_nombre = $conn->real_escape_string($_POST['segundo_nombre'] ?? '');
    $primer_apellido = $conn->real_escape_string($_POST['primer_apellido'] ?? '');
    $segundo_apellido = $conn->real_escape_string($_POST['segundo_apellido'] ?? '');
    $cedula = $conn->real_escape_string($_POST['cedula'] ?? '');
    $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
    $carrera = $conn->real_escape_string($_POST['carrera'] ?? '');
    $fecha_nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $role = $conn->real_escape_string($_POST['role'] ?? 'estudiante');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validaciones de datos ---
    if (empty($primer_nombre) || empty($primer_apellido) || empty($cedula) || empty($telefono) || empty($fecha_nacimiento) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: registration_status.php?error=" . urlencode("Todos los campos obligatorios deben ser completados."));
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: registration_status.php?error=" . urlencode("Las contraseñas no coinciden."));
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: registration_status.php?error=" . urlencode("La contraseña debe tener al menos 6 caracteres."));
        exit();
    }

    if (!preg_match('/^\d-\d{4}-\d{4}$/', $cedula)) {
        header("Location: registration_status.php?error=" . urlencode("Formato de cédula incorrecto. Use 8-0000-0000."));
        exit();
    }

    // --- Verificar si el email o la cédula ya existen ---
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? OR cedula = ?");
    if ($stmt_check === false) {
        header("Location: registration_status.php?error=" . urlencode("Error al preparar la verificación de usuario/cédula: " . $conn->error));
        exit();
    }
    $stmt_check->bind_param("ss", $email, $cedula);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header("Location: registration_status.php?error=" . urlencode("El correo electrónico o la cédula ya están registrados."));
        $stmt_check->close();
        exit();
    }
    $stmt_check->close();

    // --- Procesar imagen de usuario (opcional) ---
    // La ruta debe ser relativa a process_registration.php
    // Para ir de views/auth/ a SISTEMA-BIBLIOTECA-MAIN/uploads/profile_images/
    $target_dir = "../../uploads/profile_images/"; 
    $image_path_for_db = null; // Esto es lo que se guardará en la base de datos

    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] == 0) {
        // Asegurarse de que el directorio exista y sea escribible
        if (!is_dir($target_dir)) {
            // Intenta crear el directorio si no existe
            if (!mkdir($target_dir, 0755, true)) { // 0755 es un permiso común para directorios
                header("Location: registration_status.php?error=" . urlencode("Error: El directorio de destino para imágenes no existe y no se pudo crear. Verifique permisos de la carpeta padre. Ruta: " . $target_dir));
                exit();
            }
        }
        if (!is_writable($target_dir)) {
            header("Location: registration_status.php?error=" . urlencode("Error: El directorio de destino para imágenes no tiene permisos de escritura. Verifique los permisos de la carpeta. Ruta: " . $target_dir));
            exit();
        }

        $image_name = basename($_FILES["user_image"]["name"]);
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Verificar si es una imagen real
        $check = getimagesize($_FILES["user_image"]["tmp_name"]);
        if($check === false) {
            header("Location: registration_status.php?error=" . urlencode("El archivo subido no es una imagen válida."));
            $uploadOk = 0;
        }

        // Permitir ciertos formatos de archivo
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            header("Location: registration_status.php?error=" . urlencode("Solo se permiten archivos JPG, JPEG, PNG y GIF para la imagen de perfil."));
            $uploadOk = 0;
        }

        // Verificar tamaño del archivo (ej. 5MB máximo)
        if ($_FILES["user_image"]["size"] > 5000000) { // 5MB
            header("Location: registration_status.php?error=" . urlencode("La imagen de perfil es demasiado grande. Máximo 5MB."));
            $uploadOk = 0;
        }

        // Si todas las validaciones pasan, intentar subir la imagen
        if ($uploadOk == 1) {
            // Generar un nombre único para la imagen para evitar colisiones
            $new_image_name = uniqid('profile_', true) . '.' . $imageFileType;
            $target_file_full_path = $target_dir . $new_image_name;

            if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $target_file_full_path)) {
                // Guardar la ruta relativa desde la raíz del proyecto para la base de datos
                // Ejemplo: uploads/profile_images/profile_uniqueid.png
                $image_path_for_db = "uploads/profile_images/" . $new_image_name; 
            } else {
                $upload_error_code = $_FILES["user_image"]["error"];
                $php_upload_errors = array(
                    UPLOAD_ERR_OK         => "No hay error, el archivo se subió correctamente.",
                    UPLOAD_ERR_INI_SIZE   => "El archivo subido excede la directiva upload_max_filesize en php.ini.",
                    UPLOAD_ERR_FORM_SIZE  => "El archivo subido excede la directiva MAX_FILE_SIZE que fue especificada en el formulario HTML.",
                    UPLOAD_ERR_PARTIAL    => "El archivo subido solo se cargó parcialmente.",
                    UPLOAD_ERR_NO_FILE    => "No se cargó ningún archivo.",
                    UPLOAD_ERR_NO_TMP_DIR => "Falta una carpeta temporal.",
                    UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
                    UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la carga del archivo."
                );
                $error_message = $php_upload_errors[$upload_error_code] ?? "Error desconocido al mover la imagen.";
                header("Location: registration_status.php?error=" . urlencode("Error al subir la imagen: " . $error_message));
                exit();
            }
        } else {
            // Si uploadOk es 0 por alguna de las validaciones, ya se habrá redirigido con un error específico.
            exit(); 
        }
    }

    // --- Hashear la contraseña ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- Insertar nuevo usuario en la base de datos ---
    $sql_insert = "INSERT INTO users (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, cedula, telefono, carrera, fecha_nacimiento, email, password, role, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);

    if ($stmt_insert === false) {
        header("Location: registration_status.php?error=" . urlencode("Error al preparar la inserción: " . $conn->error));
        exit();
    }

    $stmt_insert->bind_param("ssssssssssss", 
        $primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, 
        $cedula, $telefono, $carrera, $fecha_nacimiento, $email, 
        $hashed_password, $role, $image_path_for_db // Usar la ruta para la DB aquí
    );

    if ($stmt_insert->execute()) {
        header("Location: registration_status.php?mensaje=" . urlencode("¡Registro exitoso! Ahora puedes iniciar sesión."));
        exit();
    } else {
        header("Location: registration_status.php?error=" . urlencode("Error al registrar el usuario: " . $stmt_insert->error));
        exit();
    }

    $stmt_insert->close();

} else {
    header("Location: register.php");
    exit();
}

$conn->close();
?>
