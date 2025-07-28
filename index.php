
<?php
require_once __DIR__ . '/controllers/auth_middleware.php';
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Virtual</title>
    <!-- Google Fonts: Roboto (ya estaba) y Montserrat (para el logo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;family=Montserrat:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main_styles.css"> 
</head>

<body>

    <header>
        <div class="logo">📚Sistema de Biblioteca</div>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="prueba.php">Libros</a>
            <a href="#">Reservados</a>
            <a href="#">Roles</a>
            <a href="/ProyectoFinalDS7/views/profile.php">Perfil</a>
        </nav>
    </header>

    <section class="hero">
        <h1>Crea Tu Propia Historia</h1>
        <p>Descubre el Mundo, Desvela tu imaginación : Tu Aventura Comienza Aquí</p>
        <!-- MODIFICADO: El botón ahora apunta directamente a Formulario.html -->

        <a href="Formulario.php" class="btn">Crear Nuevo Libro</a>

    </section>

    <section class="features">
        <div class="feature">
            <h3>Amplia Colección</h3>
            <p>Explora miles de títulos de diversos géneros y autores.</p>
        </div>
        <div class="feature">
            <h3>Reserva Fácil</h3>
            <p>Reserva tus libros favoritos en línea con solo unos clics.</p>
        </div>
        <div class="feature">
            <h3>Acceso Personalizado</h3>
            <p>Gestiona tus reservas y perfil de usuario de forma sencilla.</p>
        </div>


    </section>
</body>
</html>
