<?php
// process_login.php
// Habilitar la visualización de errores para depuración (¡Quitar en producción!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Iniciar la sesión para almacenar datos del usuario

// Definir las constantes de conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'alex03'); // Asegúrate de que este usuario tenga permisos para SELECT
define('DB_PASS', '0123456789'); 
define('DB_NAME', 'biblioteca_sencilla'); // Nombre de tu base de datos

// Establecer conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    // Si la conexión falla, redirige al login con un mensaje de error
    // Esta redirección podría no funcionar si el error de conexión es muy temprano.
    header("Location: login.php?error=" . urlencode("Error de conexión a la base de datos: " . $conn->connect_error));
    exit();
}

// Verificar si la solicitud es de tipo POST (es decir, si el formulario fue enviado)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y sanear los datos del formulario
    $username_or_email = $conn->real_escape_string($_POST['username'] ?? ''); // El nombre del campo en login.php es 'username'
    $password = $_POST['password'] ?? '';

    // Validaciones básicas
    if (empty($username_or_email) || empty($password)) {
        header("Location: login.php?error=" . urlencode("Por favor, ingresa tu nombre de usuario/email y contraseña."));
        exit();
    }

    // Buscar usuario por nombre de usuario o email
    // Seleccionamos todas las columnas necesarias para la sesión y verificación
    $stmt = $conn->prepare("SELECT id, primer_nombre, primer_apellido, email, password, role FROM users WHERE email = ? OR cedula = ?");
    
    // Es importante usar 'email' o 'cedula' aquí, dependiendo de cómo quieres que el usuario inicie sesión.
    // Si el campo de login es "username" pero quieres que acepte email o cedula,
    // entonces el bind_param debe usar la misma variable para ambos placeholders.
    if ($stmt === false) {
        header("Location: login.php?error=" . urlencode("Error al preparar la consulta de login: " . $conn->error));
        exit();
    }
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verificar la contraseña hasheada
        if (password_verify($password, $user['password'])) {
            // Contraseña correcta, iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['primer_nombre'] . ' ' . $user['primer_apellido']; // Usar nombre completo para la sesión
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirigir al usuario a la página principal (index.php)
            // La ruta es relativa a process_login.php, que está en views/auth/
            header("Location: ../../index.php"); 
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: login.php?error=" . urlencode("Credenciales incorrectas. Por favor, inténtalo de nuevo."));
            exit();
        }
    } else {
        // Usuario o email no encontrado
        header("Location: login.php?error=" . urlencode("Credenciales incorrectas. Por favor, inténtalo de nuevo."));
        exit();
    }

    $stmt->close();

} else {
    // Si se accede directamente a este archivo sin POST, redirige al formulario de login
    header("Location: login.php");
    exit();
}

// Cerrar la conexión (opcional, PHP la cierra automáticamente al finalizar el script)
$conn->close();
?>
