<?php
// controllers/auth_middleware.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Usamos la ruta absoluta al proyecto y luego la ruta a la vista de login.
    // Asumiendo que tu proyecto se llama 'ProyectoFinalDS7' y está en la raíz de localhost.
    header("Location: /ProyectoFinalDS7/views/auth/login.php");
    exit();
}
// NO hay etiqueta de cierre ?>