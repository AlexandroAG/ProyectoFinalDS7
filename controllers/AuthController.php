<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/UniversalSatinizer.php';
require_once __DIR__ . '/../config/Database.php';

class AuthController {
    private $userModel;
    private $sanitizer;

    public function __construct() {
        $this->userModel = new User();
        $this->sanitizer = new UniversalSanitizer();
        
        // Iniciar sesión solo si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Sanitización y validación de inputs
                $username = $this->sanitizer->basicString($_POST['username'] ?? '');
                $password = $this->sanitizer->password($_POST['password'] ?? '');
                
                // Validación adicional
                if(empty($username) || empty($password)) {
                    throw new InvalidArgumentException("Usuario y contraseña son requeridos");
                }

                $user = $this->userModel->login($username, $password);
                
                if($user) {
                    // Guardar todos los datos necesarios para el perfil
                    $_SESSION['user_id'] = $this->sanitizer->integer($user['id']);
                    $_SESSION['username'] = $this->sanitizer->htmlSpecialChars($user['username']);
                    $_SESSION['usuario_rol'] = $this->sanitizer->role($user['role']);
                    $_SESSION['full_name'] = $this->sanitizer->name($user['full_name']);
                    $_SESSION['email'] = $this->sanitizer->email($user['email'] ?? '');
                    $_SESSION['cedula'] = $this->sanitizer->cedulaPanama($user['cedula'] ?? '');
                    $_SESSION['telefono'] = $this->sanitizer->phoneNumber($user['telefono'] ?? '');
                    $_SESSION['imagen_perfil'] = $user['imagen_perfil'] ?? '';
                    
                    // Redirigir según el rol
                    if ($_SESSION['role'] === 'admin') {
                        header('Location: ../views/admin/dashboard.php');
                    } else {
                        header('Location: ../views/user/dashboard.php');
                    }
                    exit();
                } else {
                    throw new RuntimeException("Usuario o contraseña incorrectos");
                }
                
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
                require_once '../views/auth/login.php';
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
                require_once '../views/auth/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/auth/login.php';
        }
    }

    public function logout() {
        // Limpiar y destruir la sesión más completamente
        $_SESSION = array();
        
        // Borrar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        session_destroy();
        
        // Redirigir al login con mensaje opcional
        header('Location: ../index.php?logout=1');
        exit();
    }

    public function checkAuth() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: ../index.php?error=not_logged_in');
            exit();
        }
        
        // Sanitizar todos los valores de sesión con manejo de valores nulos
        $_SESSION['user_id'] = UniversalSanitizer::integer($_SESSION['user_id'] ?? 0);
        $_SESSION['username'] = UniversalSanitizer::htmlSpecialChars($_SESSION['username'] ?? '');
        $_SESSION['role'] = UniversalSanitizer::role($_SESSION['role'] ?? 'estudiante');
        $_SESSION['full_name'] = UniversalSanitizer::name($_SESSION['full_name'] ?? '');

        try {
            $_SESSION['email'] = UniversalSanitizer::email($_SESSION['email'] ?? '');
        } catch (InvalidArgumentException $e) {
            $_SESSION['email'] = '';
        }

        $_SESSION['cedula'] = UniversalSanitizer::cedulaPanama($_SESSION['cedula'] ?? '', false);
        $_SESSION['telefono'] = UniversalSanitizer::phoneNumber($_SESSION['telefono'] ?? '', false);
        $_SESSION['imagen_perfil'] = UniversalSanitizer::basicString($_SESSION['imagen_perfil'] ?? '');
    }

    public function checkAdmin() {
        $this->checkAuth();
        if($_SESSION['role'] !== 'admin') {
            header('Location: ../views/dashboard.php?error=unauthorized');
            exit();
        }
    }

    public function getProfileData() {
    if (!isset($_SESSION['user_id'])) {
        return [];
    }

    $id = $_SESSION['user_id'];
    $conn = (new Database())->connect();
$sql = "SELECT primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, imagen_perfil, email, role, cedula, telefono FROM usuarios WHERE id = :id LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Armar nombre completo
        $user['full_name'] = $user['primer_nombre'] . ' ' . $user['primer_apellido'];
        return $user;
    }

    return [];
}

    public function updateProfile($data) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $id = $_SESSION['user_id'];
        $conn = (new Database())->connect();

        try {
            // Separar el nombre completo en primer nombre y primer apellido
            $fullName = trim($data['full_name']);
            $nameParts = explode(' ', $fullName, 2);
            $primerNombre = $nameParts[0];
            $primerApellido = isset($nameParts[1]) ? $nameParts[1] : '';

            $sql = "UPDATE usuarios SET 
                    primer_nombre = :primer_nombre,
                    primer_apellido = :primer_apellido,
                    telefono = :telefono,
                    imagen_perfil = :imagen_perfil
                    WHERE id = :id";

            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                'primer_nombre' => $this->sanitizer->name($primerNombre),
                'primer_apellido' => $this->sanitizer->name($primerApellido),
                'telefono' => $this->sanitizer->phoneNumber($data['telefono'], false),
                'imagen_perfil' => $this->sanitizer->basicString($data['imagen_perfil']),
                'id' => $id
            ]);

            // Actualizar la sesión con los nuevos datos
            if ($result) {
                $_SESSION['full_name'] = $fullName;
                $_SESSION['telefono'] = $data['telefono'];
                $_SESSION['imagen_perfil'] = $data['imagen_perfil'];
            }

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }


}
?>