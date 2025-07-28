<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../class/UniversalSatinizer.php';

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
                    $_SESSION['role'] = $this->sanitizer->role($user['role']);
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
        $this->checkAuth();
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'full_name' => !empty($_SESSION['full_name']) ? $this->sanitizer->name($_SESSION['full_name']) : 'No especificado',
            'email' => !empty($_SESSION['email']) ? $this->sanitizer->email($_SESSION['email']) : 'no-email@example.com',
            'cedula' => $_SESSION['cedula'] ?? '',
            'telefono' => $_SESSION['telefono'] ?? '',
            'imagen_perfil' => $_SESSION['imagen_perfil'] ?? ''
        ];
    }
}
?>