<?php
require_once __DIR__ . '/../../controllers/auth_middleware.php'; // Asegura que el usuario esté autenticado y autorizado
require_once __DIR__ . '/../../config/Database.php'; // Conexión a la base de datos

$database = new Database();
$conn = $database->connect();

$user = null; // Inicializa la variable para almacenar los datos del usuario

// Verifica si se proporcionó un ID de usuario en la URL
if (isset($_GET['id'])) {
    $user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Valida que el ID sea un entero

    if ($user_id === false || $user_id <= 0) {
        // ID inválido, redirige a la lista de roles con un mensaje de error
        header("Location: rol.php?error=ID de usuario inválido.");
        exit();
    }

    try {
        // Prepara y ejecuta la consulta para obtener los datos del usuario
        $stmt = $conn->prepare("SELECT id, imagen_perfil, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, role, cedula, telefono, email, fecha_nacimiento, carrera FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Si el usuario no se encuentra, redirige con un mensaje de error
            header("Location: rol.php?error=Usuario no encontrado.");
            exit();
        }
    } catch (PDOException $e) {
        // Maneja cualquier error de la base de datos
        error_log("Error al cargar usuario para edición: " . $e->getMessage()); // Registra el error para depuración
        header("Location: rol.php?error=Error al cargar datos del usuario.");
        exit();
    }
} else {
    // Si no se proporcionó ningún ID, redirige con un mensaje de error
    header("Location: rol.php?error=Se requiere un ID de usuario para editar.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/register_styles.css"> <style>
     
    </style>
</head>
<body>
    <div class="register-container">
        <div class="left-panel">
            <div class="user-image-placeholder">
                <?php if (!empty($user['imagen_perfil'])): ?>
                    <img id="userImagePreview" src="/ProyectoFinalDS7/<?= htmlspecialchars($user['imagen_perfil']) ?>" alt="Vista previa de imagen">
                <?php else: ?>
                    <img id="userImagePreview" src="" alt="Vista previa de imagen" style="display: none;">
                    <span id="defaultUserIcon" class="icon">👤</span>
                <?php endif; ?>
            </div>
            <h2>¡Edita tu Perfil!</h2>
            <p>Actualiza la información del usuario.</p>
        </div>
        <div class="right-panel">
            <h2>Editar Usuario</h2>

            <?php
            // Muestra mensajes de éxito o error si vienen de la redirección
            if (isset($_GET['success'])) {
                echo '<div class="mensaje success">' . htmlspecialchars($_GET['success']) . '</div>';
            } elseif (isset($_GET['error'])) {
                echo '<div class="mensaje error">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>
            <form action="/ProyectoFinalDS7/views/auth/process_edit_user.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                <input type="hidden" name="old_image_path" value="<?= htmlspecialchars($user['imagen_perfil']) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="primer_nombre">Primer Nombre:</label>
                        <input type="text" id="primer_nombre" name="primer_nombre" value="<?= htmlspecialchars($user['primer_nombre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="segundo_nombre">Segundo Nombre:</label>
                        <input type="text" id="segundo_nombre" name="segundo_nombre" value="<?= htmlspecialchars($user['segundo_nombre']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="primer_apellido">Primer Apellido:</label>
                        <input type="text" id="primer_apellido" name="primer_apellido" value="<?= htmlspecialchars($user['primer_apellido']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="segundo_apellido">Segundo Apellido:</label>
                        <input type="text" id="segundo_apellido" name="segundo_apellido" value="<?= htmlspecialchars($user['segundo_apellido']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula (Formato: 8-0000-0000):</label>
                    <input type="text" id="cedula" name="cedula" pattern="^\d-\d{4}-\d{4}$" placeholder="Ej: 8-1234-5678" value="<?= htmlspecialchars($user['cedula']) ?>" required>
                    <small id="cedulaError" style="color: red; display: none;">Formato de cédula incorrecto.</small>
                </div>
                <div class="form-group">
                    <label for="telefono">Número de Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($user['telefono']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="carrera">Carrera actual:</label>
                    <input type="text" id="carrera" name="carrera" value="<?= htmlspecialchars($user['carrera']) ?>">
                </div>
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($user['fecha_nacimiento']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role" required>
                        <option value="estudiante" <?= ($user['role'] == 'estudiante') ? 'selected' : '' ?>>Estudiante</option>
                        <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña (dejar en blanco para no cambiar):</label>
                    <input type="password" id="password" name="password"> </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <div class="form-group">
                    <label for="user_image">Imagen de Usuario (subir nueva o dejar actual):</label>
                    <input type="file" id="user_image" name="user_image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='rol.php'">Cancelar</button>
                    <button type="submit" class="btn btn-update">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/ProyectoFinalDS7/js/register_script.js"></script>
    <script>
        // Adapta register_script.js o crea un nuevo script para la vista previa de la imagen
        // Este script es para la vista previa de la imagen al subir una nueva en la edición
        document.addEventListener('DOMContentLoaded', function() {
            const userImageInput = document.getElementById('user_image');
            const userImagePreview = document.getElementById('userImagePreview');
            const defaultUserIcon = document.getElementById('defaultUserIcon');

            if (userImageInput) {
                userImageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            userImagePreview.src = e.target.result;
                            userImagePreview.style.display = 'block';
                            if (defaultUserIcon) {
                                defaultUserIcon.style.display = 'none';
                            }
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Si no se selecciona un archivo, muestra el icono por defecto o la imagen antigua
                        const oldImagePath = document.querySelector('input[name="old_image_path"]').value;
                        if (oldImagePath) {
                            userImagePreview.src = /ProyectoFinalDS7/${oldImagePath};
                            userImagePreview.style.display = 'block';
                            if (defaultUserIcon) {
                                defaultUserIcon.style.display = 'none';
                            }
                        } else {
                            userImagePreview.style.display = 'none';
                            if (defaultUserIcon) {
                                defaultUserIcon.style.display = 'block';
                            }
                        }
                    }
                });
            }

            // Lógica para el formato de cédula (del register_script.js original)
            const cedulaInput = document.getElementById('cedula');
            const cedulaError = document.getElementById('cedulaError');
            if (cedulaInput) {
                cedulaInput.addEventListener('input', function() {
                    const pattern = /^\d-\d{4}-\d{4}$/;
                    if (!pattern.test(this.value) && this.value !== '') {
                        cedulaError.style.display = 'block';
                    } else {
                        cedulaError.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>