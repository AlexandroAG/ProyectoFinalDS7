<?php
require_once __DIR__ . '/../../controllers/auth_middleware.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';

// Verificar que el usuario sea admin
$authController = new AuthController();
$userData = $authController->getProfileData();

if (empty($userData) || $userData['role'] !== 'admin') {
    header('Location: ../../index.php?error=acceso_denegado');
    exit;
}

$database = new Database();
$conn = $database->connect();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles de Usuario</title>
    <link rel="stylesheet" href="../../assets/css/main_styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;family=Montserrat:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/rol_styles.css">
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

    <main>

        <h1>Lista de Usuarios Registrados</h1>

        <!-- Botón para crear nuevo usuario -->
        <div style="margin-bottom: 20px;">
            <a href="create_user.php" class="btn-crear" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; transition: all 0.3s ease;">
                ➕ Crear Nuevo Usuario
            </a>
        </div>

         <?php if (isset($_GET['eliminado'])): ?>
            <div class="mensaje" id="mensajeEliminacion"
                style="padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; background-color: <?= $_GET['eliminado'] == 1 ? '#d4edda' : '#f8d7da' ?>; color: <?= $_GET['eliminado'] == 1 ? '#155724' : '#721c24' ?>;">
                <?php if ($_GET['eliminado'] == 1): ?>
                    ✅ Usuario eliminado correctamente.
                <?php else: ?>
                    ❌ Error al eliminar el usuario.
                <?php endif; ?>
            </div>
            <script>
                // Ocultar el mensaje después de 3 segundos (3000 milisegundos)
                setTimeout(function() {
                    let mensaje = document.getElementById('mensajeEliminacion');
                    if (mensaje) {
                        mensaje.style.display = 'none';
                    }
                }, 800); // Puedes ajustar este tiempo
            </script>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Primer Nombre</th>
                    <th>Segundo Nombre</th>
                    <th>Primer Apellido</th>
                    <th>Segundo Apellido</th>
                    <th>Rol</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT id, imagen_perfil, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, role, cedula, telefono, email, fecha_nacimiento FROM usuarios ORDER BY id ASC";
                $result = $conn->query($sql);

                if ($result && $result->rowCount() > 0):
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td>
                                <?php if (!empty($row['imagen_perfil'])): ?>
                                    <img src="/ProyectoFinalDS7/<?= htmlspecialchars($row['imagen_perfil']) ?>" alt="Perfil">
                                <?php else: ?>
                                    <span>Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['primer_nombre']) ?></td>
                            <td><?= htmlspecialchars($row['segundo_nombre']) ?></td>
                            <td><?= htmlspecialchars($row['primer_apellido']) ?></td>
                            <td><?= htmlspecialchars($row['segundo_apellido']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td><?= htmlspecialchars($row['cedula']) ?></td>
                            <td><?= htmlspecialchars($row['telefono']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_nacimiento']) ?></td>
                            <td>
                                <a href="Edit_user.php?id=<?= $row['id'] ?>" class="btn-editar">✏ Editar</a>
                                <a href="../../models/Delete_user.php?id=<?= $row['id'] ?>" class="btn-eliminar"
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">🗑
                                    Eliminar</a>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="12">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>

</body>

</html>