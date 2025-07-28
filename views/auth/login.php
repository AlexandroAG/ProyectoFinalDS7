<?php
// login.php
// Puedes iniciar la sesión aquí si planeas usarla para mensajes o redirecciones.
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/login_styles.css"> 
    <style>
        /* Estilos para mensajes de error */
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 15px;
            text-align: center;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-panel">
            <div class="user-icon-placeholder">
                <span class="icon">🔒</span> <!-- Icono de candado para login -->
            </div>
            <h2>¡Bienvenido!</h2>
            <p>Inicia sesión para acceder a tu biblioteca personal y gestionar tus libros.</p>
        </div>
        <div class="right-panel">
            <h2>Iniciar Sesión</h2>

            <?php
            // Mostrar mensajes de error si existen
            if (isset($_GET['error'])) {
                echo '<div class="message error">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>

            <form action="process_login.php" method="POST">
                <div class="form-group">
                    <label for="username">Nombre de Usuario o Email:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="options-links">
                    <a href="forgot_password.html">¿Olvidaste tu contraseña?</a>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn button-login">Iniciar Sesión</button>
                </div>
            </form>
            <div class="register-link">
                ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
            </div>
        </div>
    </div>
</body>
</html>
