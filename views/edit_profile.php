<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/auth_middleware.php';

$authController = new AuthController();
$userData = $authController->getProfileData();

$message = '';
$messageType = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $telefono = trim($_POST['telefono']);
    
    // Validaciones básicas
    if (empty($full_name)) {
        $message = 'El nombre completo es obligatorio.';
        $messageType = 'error';
    } elseif (strlen($full_name) < 2) {
        $message = 'El nombre debe tener al menos 2 caracteres.';
        $messageType = 'error';
    } elseif (!empty($telefono) && !preg_match('/^[0-9+\-\s()]+$/', $telefono)) {
        $message = 'El teléfono solo puede contener números, espacios y los caracteres +, -, ().';
        $messageType = 'error';
    } else {
        // Manejar subida de imagen
        $imagen_perfil = $userData['imagen_perfil']; // Mantener la imagen actual por defecto
        
        if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/profile_images/';
            
            // Crear directorio si no existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileInfo = pathinfo($_FILES['imagen_perfil']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extension, $allowedExtensions)) {
                $fileName = 'profile_' . uniqid() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $uploadPath)) {
                    $imagen_perfil = 'uploads/profile_images/' . $fileName;
                    
                    // Eliminar imagen anterior si existe
                    if (!empty($userData['imagen_perfil']) && file_exists('../' . $userData['imagen_perfil'])) {
                        unlink('../' . $userData['imagen_perfil']);
                    }
                } else {
                    $message = 'Error al subir la imagen.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Solo se permiten archivos de imagen (JPG, PNG, GIF).';
                $messageType = 'error';
            }
        }
        
        // Actualizar perfil si no hay errores
        if (empty($message)) {
            $updateResult = $authController->updateProfile([
                'full_name' => $full_name,
                'telefono' => $telefono,
                'imagen_perfil' => $imagen_perfil
            ]);
            
            if ($updateResult) {
                $message = '¡Perfil actualizado correctamente!';
                $messageType = 'success';
                // Recargar datos del usuario
                $userData = $authController->getProfileData();
            } else {
                $message = 'Error al actualizar el perfil.';
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Biblioteca Virtual</title>
    <link rel="stylesheet" href="../assets/css/main_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding-top: 80px;
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
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

        .edit-profile-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .breadcrumb {
            background: #e9ecef;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .breadcrumb a {
            color: #4a6fa5;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a6fa5;
        }

        .form-group input[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .current-image {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .current-image img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid #4a6fa5;
        }

        .current-image .no-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            border: 2px solid #4a6fa5;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-right: 1rem;
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

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: bold;
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

        .readonly-info {
            background: #e9ecef;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .readonly-info h4 {
            margin: 0 0 0.5rem 0;
            color: #495057;
        }

        .readonly-info p {
            margin: 0.25rem 0;
            color: #6c757d;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .edit-profile-container {
                margin: 1rem;
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                margin-right: 0;
                text-align: center;
            }
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
            <?php if (!empty($userData) && $userData['role'] === 'admin'): ?>
                <a href="/ProyectoFinalDS7/views/auth/rol.php">Roles</a>
            <?php endif; ?>
            <a href="/ProyectoFinalDS7/views/profile.php">Perfil</a>
        </nav>
    </header>

    <main class="edit-profile-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="profile.php">
                <i class="fas fa-arrow-left"></i> Volver al perfil
            </a>
        </div>

        <h1><i class="fas fa-user-edit"></i> Editar Perfil</h1>

        <!-- Mensajes -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Información no editable -->
        <div class="readonly-info">
            <h4><i class="fas fa-info-circle"></i> Información no editable</h4>
            <p><strong>Email:</strong> <?= htmlspecialchars($userData['email']) ?></p>
            <p><strong>Cédula:</strong> <?= htmlspecialchars($userData['cedula']) ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($userData['role'])) ?></p>
        </div>

        <!-- Formulario de edición -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-user"></i> Nombre Completo *
                </label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       value="<?= htmlspecialchars($userData['full_name']) ?>" 
                       required 
                       maxlength="100">
            </div>

            <div class="form-group">
                <label for="telefono">
                    <i class="fas fa-phone"></i> Teléfono
                </label>
                <input type="tel" 
                       id="telefono" 
                       name="telefono" 
                       value="<?= htmlspecialchars($userData['telefono']) ?>" 
                       maxlength="20"
                       placeholder="Ejemplo: +507 1234-5678">
            </div>

            <div class="form-group">
                <label for="imagen_perfil">
                    <i class="fas fa-camera"></i> Foto de Perfil
                </label>
                
                <?php if (!empty($userData['imagen_perfil'])): ?>
                    <div class="current-image">
                        <img src="../<?= htmlspecialchars($userData['imagen_perfil']) ?>" alt="Foto actual">
                        <span>Foto actual</span>
                    </div>
                <?php else: ?>
                    <div class="current-image">
                        <div class="no-image">
                            <i class="fas fa-user" style="font-size: 1.5rem; color: #999;"></i>
                        </div>
                        <span>Sin foto de perfil</span>
                    </div>
                <?php endif; ?>
                
                <input type="file" 
                       id="imagen_perfil" 
                       name="imagen_perfil" 
                       accept="image/jpeg,image/jpg,image/png,image/gif">
                <small style="color: #6c757d; font-size: 0.875rem;">
                    Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </main>
</body>
</html>
