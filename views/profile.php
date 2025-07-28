<?php

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/auth_middleware.php';

$authController = new AuthController();
$userData = $authController->getProfileData();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Biblioteca Virtual</title>
    <link rel="stylesheet" href="../assets/css/main_styles.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1.5rem;
            border: 3px solid #4a6fa5;
        }

        .profile-info h2 {
            margin: 0;
            color: #333;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        html, body {
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #000;
            color: white;
            padding: 1rem;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        nav a {
            color: white;
            margin-right: 1rem;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">📚Sistema de Biblioteca</div>
        <nav>
            <a href="/ProyectoFinalDS7/index.php">Inicio</a>
            <a href="/ProyectoFinalDS7/prueba.php">Libros</a>
            <a href="/ProyectoFinalDS7/views/reservation.php">Mis Reservas</a>
            <a href="/ProyectoFinalDS7/views/auth/rol.php">Roles</a>
            <a href="/ProyectoFinalDS7/views/profile.php">Perfil</a>
        </nav>
    </header>

    <main class="profile-container">
        <div class="profile-header">
            <?php if (!empty($userData['imagen_perfil'])): ?>
                <img src="../<?php echo htmlspecialchars($userData['imagen_perfil']); ?>" alt="Foto de perfil" class="profile-pic">
            <?php else: ?>
                <div class="profile-pic" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 2rem;">👤</span>
                </div>
            <?php endif; ?>

            <div class="profile-info">
                <h2><?php echo !empty($userData['full_name']) ? htmlspecialchars($userData['full_name']) : 'Usuario'; ?></h2>
                <p><?php echo !empty($userData['email']) && $userData['email'] != 'no-email@example.com' 
                    ? htmlspecialchars($userData['email']) 
                    : 'Email no proporcionado'; ?></p>
                <p><strong>Rol:</strong> <?php echo htmlspecialchars(ucfirst($userData['role'])); ?></p>
            </div>
        </div>

        <div class="profile-details">
            <h3>Información Personal</h3>
            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($userData['cedula']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($userData['telefono']); ?></p>

            <div class="action-buttons">
                <a href="edit_profile.php" class="btn btn-primary">Editar Perfil</a>
                <a href="../controllers/logout.php" class="btn btn-danger">Cerrar Sesión</a>
                <!-- <a href="../controllers/exportar_libros.php" class="btn btn-success">📥 Exportar libros a CSV</a> -->
            </div>
        </div>
    </main>

    <?php include './partials/footer.php'; ?>
</body>
</html>
