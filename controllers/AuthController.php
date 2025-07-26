<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $user = $this->userModel->login($username, $password);
            
            if($user) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header('Location: ../views/dashboard.php');
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
                require_once '../views/auth/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/auth/login.php';
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: ../index.php');
        exit();
    }

    public function checkAuth() {
        session_start();
        if(!isset($_SESSION['user_id'])) {
            header('Location: ../index.php');
            exit();
        }
    }

    public function checkAdmin() {
        $this->checkAuth();
        if($_SESSION['role'] !== 'admin') {
            header('Location: ../views/dashboard.php');
            exit();
        }
    }
}
?>