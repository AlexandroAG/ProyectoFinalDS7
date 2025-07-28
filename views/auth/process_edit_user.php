<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../controllers/auth_middleware.php'; 
// Incluye la conexión a la base de datos
require_once __DIR__ . '/../../config/Database.php'; 

// Inicializa la conexión a la base de datos
$database = new Database();
$conn = $database->connect();

// Base URL de tu proyecto. Asegúrate de que coincida con lo que usas en el HTML para las imágenes.
// Si tu proyecto se accede via http://localhost/ProyectoFinalDS7/, esto debería ser '/ProyectoFinalDS7/'
// Si está en la raíz del servidor (ej. http://localhost/), simplemente '/'
$base_project_url = '/ProyectoFinalDS7/'; // <-- ¡Confirma que esta sea la base de tu URL de proyecto!

// Carpeta donde se guardarán las imágenes de perfil (relativa a la raíz del proyecto)
$upload_dir = __DIR__ . '/../public/uploads/profile_images/'; 

// Asegúrate de que el directorio de subida exista
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); 
}

// Función auxiliar para redirigir y terminar el script
function redirect_with_message($location, $type, $message) {
    header("Location: " . $location . "?" . $type . "=" . urlencode($message));
    exit();
}

// Verifica si la solicitud es un POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $primer_nombre = filter_input(INPUT_POST, 'primer_nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $segundo_nombre = filter_input(INPUT_POST, 'segundo_nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $primer_apellido = filter_input(INPUT_POST, 'primer_apellido', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $segundo_apellido = filter_input(INPUT_POST, 'segundo_apellido', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cedula = filter_input(INPUT_POST, 'cedula', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $carrera = filter_input(INPUT_POST, 'carrera', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fecha_nacimiento = filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? '';
    $old_image_path = filter_input(INPUT_POST, 'old_image_path', FILTER_SANITIZE_URL);

    // --- Validación de datos ---
    if (!$user_id || !$primer_nombre || !$primer_apellido || !$cedula || !$telefono || !$email || !$role || !$fecha_nacimiento) {
        redirect_with_message("edit_user.php", "error", "Todos los campos requeridos deben ser completados.");
    }

    // Validar formato de cédula (PHP backend)
    if (!preg_match('/^\d-\d{4}-\d{4}$/', $cedula)) {
        redirect_with_message("edit_user.php", "error", "Formato de cédula incorrecto. Use 0-0000-0000.");
    }

    // Validar contraseñas si se intenta cambiar
    $hashed_password = null; // Inicializar en null
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            redirect_with_message("edit_user.php", "error", "Las contraseñas nuevas no coinciden.");
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // Si no se proporciona una nueva contraseña, mantener la antigua
        try {
            $stmt_old_pass = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt_old_pass->execute([$user_id]);
            $current_user_data = $stmt_old_pass->fetch(PDO::FETCH_ASSOC);
            if ($current_user_data) {
                $hashed_password = $current_user_data['password'];
            } else {
                // Usuario no encontrado (aunque ya se verificó al inicio)
                redirect_with_message("rol.php", "error", "Usuario no encontrado para obtener contraseña antigua.");
            }
        } catch (PDOException $e) {
            error_log("Error al obtener contraseña antigua: " . $e->getMessage());
            redirect_with_message("edit_user.php", "error", "Error interno al procesar la contraseña.");
        }
    }

    // --- Manejo de la imagen de perfil ---
    $new_image_path_db = $old_image_path; // Por defecto, mantiene la imagen existente

    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['user_image']['tmp_name'];
        $file_name = basename($_FILES['user_image']['name']);
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $unique_file_name = uniqid('profile_', true) . '.' . $file_extension;
            $destination_path = $upload_dir . $unique_file_name;

            if (move_uploaded_file($file_tmp_name, $destination_path)) {
                // Ruta para guardar en la DB (relativa a la raíz del proyecto web)
                $new_image_path_db = 'public/uploads/profile_images/' . $unique_file_name; 

                // Elimina la imagen antigua si es diferente y no es una imagen por defecto
                if (!empty($old_image_path) && strpos($old_image_path, 'default') === false && $old_image_path !== $new_image_path_db) {
                    $full_old_image_path = __DIR__ . '/../' . $old_image_path; // Asume que old_image_path es relativa a la raíz del proyecto
                    if (file_exists($full_old_image_path) && is_file($full_old_image_path)) {
                        unlink($full_old_image_path); 
                    }
                }
            } else {
                redirect_with_message("edit_user.php", "error", "Error al mover la imagen subida.");
            }
        } else {
            redirect_with_message("edit_user.php", "error", "Formato de imagen no permitido. Solo JPG, JPEG, PNG, GIF.");
        }
    } 
    // Si no se subió una nueva imagen y no se marcó para eliminar (si tuvieras esa opción)
    // la $new_image_path_db ya contiene la $old_image_path por defecto.

    // --- Actualizar usuario en la base de datos ---
    try {
        $sql = "UPDATE usuarios SET
                    primer_nombre = ?,
                    segundo_nombre = ?,
                    primer_apellido = ?,
                    segundo_apellido = ?,
                    cedula = ?,
                    telefono = ?,
                    carrera = ?,
                    fecha_nacimiento = ?,
                    email = ?,
                    role = ?,";
        
        $params = [
            $primer_nombre,
            $segundo_nombre,
            $primer_apellido,
            $segundo_apellido,
            $cedula,
            $telefono,
            $carrera,
            $fecha_nacimiento,
            $email,
            $role
        ];

        // Solo actualiza la contraseña si se proporcionó una nueva
        if ($hashed_password !== null) { // Si se hasheó una nueva contraseña o se recuperó la antigua
            $sql .= " password = ?,";
            $params[] = $hashed_password;
        }

        $sql .= " imagen_perfil = ?
                  WHERE id = ?";
        
        $params[] = $new_image_path_db;
        $params[] = $user_id;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // --- Redirigir con mensaje de éxito a rol.php ---
        redirect_with_message("/ProyectoFinalDS7/views/auth/rol.php", "success", "Usuario actualizado exitosamente.");

    } catch (PDOException $e) {
        error_log("Error al actualizar usuario: " . $e->getMessage()); 
        redirect_with_message("edit_user.php", "error", "Error al actualizar el usuario: " . urlencode($e->getMessage()));
    }

} else {
    // Si no es una solicitud POST, redirige con error o a la página de inicio
    redirect_with_message("rol.php", "error", "Método de solicitud no permitido.");
}
?>