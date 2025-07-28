<?php
require_once __DIR__ . '/../../controllers/auth_middleware.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../class/UniversalSatinizer.php';

// Verificar que el usuario sea admin
$authController = new AuthController();
$userData = $authController->getProfileData();

if (empty($userData) || $userData['role'] !== 'admin') {
    header('Location: ../../index.php?error=acceso_denegado');
    exit;
}

$message = '';
$messageType = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $conn = $database->connect();
    $sanitizer = new UniversalSanitizer();
    
    try {
        // Sanitizar y validar datos
        $primer_nombre = $sanitizer->name($_POST['primer_nombre']);
        $segundo_nombre = $sanitizer->name($_POST['segundo_nombre'] ?? '', false);
        $primer_apellido = $sanitizer->name($_POST['primer_apellido']);
        $segundo_apellido = $sanitizer->name($_POST['segundo_apellido'] ?? '', false);
        $cedula = $sanitizer->cedulaPanama($_POST['cedula']);
        $telefono = $sanitizer->phoneNumber($_POST['telefono']);
        $email = $sanitizer->email($_POST['email']);
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $carrera = $sanitizer->basicString($_POST['carrera'] ?? '', false);
        $password = $sanitizer->password($_POST['password']);
        $confirm_password = $sanitizer->password($_POST['confirm_password']);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'estudiante'; // Solo admin puede crear admins
        
        // Validaciones
        if (empty($primer_nombre) || empty($primer_apellido) || empty($cedula) || 
            empty($telefono) || empty($email) || empty($password) || empty($fecha_nacimiento)) {
            throw new Exception('Todos los campos obligatorios deben estar completos.');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Las contraseñas no coinciden.');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres.');
        }
        
        // Verificar si el email ya existe
        $checkEmailSql = "SELECT id FROM usuarios WHERE email = :email";
        $checkStmt = $conn->prepare($checkEmailSql);
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetch()) {
            throw new Exception('Ya existe un usuario con ese email.');
        }
        
        // Verificar si la cédula ya existe
        $checkCedulaSql = "SELECT id FROM usuarios WHERE cedula = :cedula";
        $checkStmt = $conn->prepare($checkCedulaSql);
        $checkStmt->execute(['cedula' => $cedula]);
        if ($checkStmt->fetch()) {
            throw new Exception('Ya existe un usuario con esa cédula.');
        }
        
        // Manejar subida de imagen
        $imagen_perfil = '';
        if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/profile_images/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['user_image']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extension, $allowedExtensions)) {
                $fileName = 'profile_' . uniqid() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['user_image']['tmp_name'], $uploadPath)) {
                    $imagen_perfil = 'uploads/profile_images/' . $fileName;
                }
            }
        }
        
        // Crear username basado en primer nombre y primer apellido
        $username = strtolower($primer_nombre . '.' . $primer_apellido);
        $username = preg_replace('/[^a-z0-9.]/', '', $username);
        
        // Verificar si el username ya existe y agregar número si es necesario
        $originalUsername = $username;
        $counter = 1;
        do {
            $checkUsernameSql = "SELECT id FROM usuarios WHERE username = :username";
            $checkStmt = $conn->prepare($checkUsernameSql);
            $checkStmt->execute(['username' => $username]);
            if ($checkStmt->fetch()) {
                $username = $originalUsername . $counter;
                $counter++;
            } else {
                break;
            }
        } while (true);
        
        // Insertar usuario en la base de datos
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (username, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, 
                cedula, telefono, email, fecha_nacimiento, carrera, password, role, imagen_perfil) 
                VALUES (:username, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, 
                :cedula, :telefono, :email, :fecha_nacimiento, :carrera, :password, :role, :imagen_perfil)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'username' => $username,
            'primer_nombre' => $primer_nombre,
            'segundo_nombre' => $segundo_nombre,
            'primer_apellido' => $primer_apellido,
            'segundo_apellido' => $segundo_apellido,
            'cedula' => $cedula,
            'telefono' => $telefono,
            'email' => $email,
            'fecha_nacimiento' => $fecha_nacimiento,
            'carrera' => $carrera,
            'password' => $hashedPassword,
            'role' => $role,
            'imagen_perfil' => $imagen_perfil
        ]);
        
        if ($result) {
            $message = "Usuario creado exitosamente. Username asignado: $username";
            $messageType = 'success';
        } else {
            throw new Exception('Error al crear el usuario.');
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            background: #f8f9fa;
            padding: 1rem 2rem;
            border-bottom: 1px solid #dee2e6;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .form-container {
            padding: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group small {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        .role-selection {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .role-selection h3 {
            color: #495057;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .role-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .role-option {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            border-color: #667eea;
        }

        .role-option input[type="radio"] {
            margin-right: 0.5rem;
        }

        .role-option.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .image-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #667eea;
            object-fit: cover;
            margin: 1rem auto;
            display: none;
        }

        .image-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem auto;
            color: #6c757d;
            font-size: 2rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .role-options {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
            <p>Panel de administración - Gestión de usuarios</p>
        </div>

        <div class="breadcrumb">
            <a href="rol.php">
                <i class="fas fa-arrow-left"></i> Volver a la lista de usuarios
            </a>
        </div>

        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" id="createUserForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="primer_nombre">
                            <i class="fas fa-user"></i> Primer Nombre *
                        </label>
                        <input type="text" id="primer_nombre" name="primer_nombre" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="segundo_nombre">
                            <i class="fas fa-user"></i> Segundo Nombre
                        </label>
                        <input type="text" id="segundo_nombre" name="segundo_nombre" maxlength="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="primer_apellido">
                            <i class="fas fa-user"></i> Primer Apellido *
                        </label>
                        <input type="text" id="primer_apellido" name="primer_apellido" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="segundo_apellido">
                            <i class="fas fa-user"></i> Segundo Apellido
                        </label>
                        <input type="text" id="segundo_apellido" name="segundo_apellido" maxlength="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cedula">
                            <i class="fas fa-id-card"></i> Cédula *
                        </label>
                        <input type="text" id="cedula" name="cedula" pattern="^\d-\d{4}-\d{4}$" 
                               placeholder="Ej: 8-1234-5678" required>
                        <small>Formato: 8-1234-5678</small>
                    </div>
                    <div class="form-group">
                        <label for="telefono">
                            <i class="fas fa-phone"></i> Teléfono *
                        </label>
                        <input type="tel" id="telefono" name="telefono" required maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input type="email" id="email" name="email" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">
                            <i class="fas fa-calendar"></i> Fecha de Nacimiento *
                        </label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="carrera">
                        <i class="fas fa-graduation-cap"></i> Carrera
                    </label>
                    <input type="text" id="carrera" name="carrera" maxlength="100" 
                           placeholder="Ej: Ingeniería en Sistemas">
                </div>

                <div class="role-selection">
                    <h3><i class="fas fa-user-tag"></i> Tipo de Usuario</h3>
                    <div class="role-options">
                        <div class="role-option selected" onclick="selectRole('estudiante')">
                            <input type="radio" id="role_estudiante" name="role" value="estudiante" checked>
                            <label for="role_estudiante">
                                <strong>Estudiante</strong><br>
                                <small>Acceso básico para reservar libros</small>
                            </label>
                        </div>
                        <div class="role-option" onclick="selectRole('admin')">
                            <input type="radio" id="role_admin" name="role" value="admin">
                            <label for="role_admin">
                                <strong>Administrador</strong><br>
                                <small>Acceso completo al sistema</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Contraseña *
                        </label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <small>Mínimo 6 caracteres</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirmar Contraseña *
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="user_image">
                        <i class="fas fa-camera"></i> Foto de Perfil
                    </label>
                    <div class="image-placeholder" id="imagePlaceholder">
                        <i class="fas fa-camera"></i>
                    </div>
                    <img id="imagePreview" class="image-preview" alt="Vista previa">
                    <input type="file" id="user_image" name="user_image" accept="image/*" 
                           onchange="previewImage(this)">
                    <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                </div>

                <div class="form-actions">
                    <a href="rol.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectRole(role) {
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            if (role === 'estudiante') {
                document.getElementById('role_estudiante').checked = true;
                document.querySelector('[onclick="selectRole(\'estudiante\')"]').classList.add('selected');
            } else {
                document.getElementById('role_admin').checked = true;
                document.querySelector('[onclick="selectRole(\'admin\')"]').classList.add('selected');
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('imagePlaceholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
            }
        }

        // Validación de contraseñas
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validación de cédula
        document.getElementById('cedula').addEventListener('input', function() {
            const cedula = this.value;
            const pattern = /^\d-\d{4}-\d{4}$/;
            
            if (cedula && !pattern.test(cedula)) {
                this.setCustomValidity('Formato de cédula incorrecto. Use: 8-1234-5678');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
