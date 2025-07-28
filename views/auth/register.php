<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/register_styles.css"> 
</head>
<body>
    <div class="register-container">
        <div class="left-panel">
            <div class="user-image-placeholder">
                <!-- Aquí se mostrará la imagen cargada o un icono por defecto -->
                <img id="userImagePreview" src="" alt="Vista previa de imagen" style="display: none;">
                <span id="defaultUserIcon" class="icon">👤</span> <!-- Icono por defecto -->
            </div>
            <h2>¡Crea tu Perfil!</h2>
            <p>Únete a nuestra comunidad y comienza tu aventura en la biblioteca.</p>
        </div>
        <div class="right-panel">
            <h2>Registro de Usuario</h2>

            <?php
           
            if (isset($_GET['error'])) {
                echo '<div class="mensaje error">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>
            <form action="process_registration.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="primer_nombre">Primer Nombre:</label>
                        <input type="text" id="primer_nombre" name="primer_nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="segundo_nombre">Segundo Nombre:</label>
                        <input type="text" id="segundo_nombre" name="segundo_nombre">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="primer_apellido">Primer Apellido:</label>
                        <input type="text" id="primer_apellido" name="primer_apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="segundo_apellido">Segundo Apellido:</label>
                        <input type="text" id="segundo_apellido" name="segundo_apellido">
                    </div>
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula (Formato: 8-0000-0000):</label>
                    <input type="text" id="cedula" name="cedula" pattern="^\d-\d{4}-\d{4}$" placeholder="Ej: 8-1234-5678" required>
                    <small id="cedulaError" style="color: red; display: none;">Formato de cédula incorrecto.</small>
                </div>
                <div class="form-group">
                    <label for="telefono">Número de Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" required>
                </div>
                <div class="form-group">
                    <label for="carrera">Carrera actual:</label>
                    <input type="text" id="carrera" name="carrera">
                </div>
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role" required>
                        <option value="estudiante">Estudiante</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="user_image">Imagen de Usuario:</label>
                    <input type="file" id="user_image" name="user_image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='login.php'">Cancelar</button>
                    <button type="submit" class="btn btn-register">Registrarse</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enlace al archivo JavaScript externo -->
    <script src="../../js/register_script.js"></script>
</body>
</html>

</html>