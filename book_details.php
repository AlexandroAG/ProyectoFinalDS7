<?php
require_once __DIR__ . '/controllers/auth_middleware.php';
require_once __DIR__ . '/controllers/ReservationController.php';
require_once './config/Database.php';
require_once __DIR__ . '/controllers/BookAdminController.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Verificar que se haya proporcionado un ID de libro
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: prueba.php?error=libro_no_encontrado');
    exit;
}

$libroId = $_GET['id'];

// Conexión a la base de datos
$database = new Database();
$conn = $database->connect();

// Obtener detalles del libro
$sql = "SELECT libros.*, categorias_libros.nombre AS categoria_nombre 
        FROM libros 
        LEFT JOIN categorias_libros ON libros.categoria_id = categorias_libros.id 
        WHERE libros.id = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $libroId]);
$libro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$libro) {
    header('Location: prueba.php?error=libro_no_encontrado');
    exit;
}

// Verificar si el usuario ya tiene este libro reservado
// Obtener datos de usuario para verificar rol
$reservationController = new ReservationController();
$authController = new AuthController();
$userData = $authController->getProfileData();
$yaReservado = false;
if (isset($_SESSION['user_id'])) {
    $yaReservado = $reservationController->hasActiveReservation($libroId);
}

// Procesar edición si es admin y se envió el formulario
$bookAdminController = new BookAdminController();
$editSuccess = null;
if (!empty($userData) && $userData['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
    $updateData = [
        'titulo' => $_POST['titulo'],
        'autor' => $_POST['autor'],
        'isbn' => $_POST['isbn'],
        'descripcion' => $_POST['descripcion'],
        'categoria_id' => $_POST['categoria_id'],
        'cantidad' => $_POST['cantidad'],
        'cantidad_disponible' => $_POST['cantidad_disponible'],
        'anio_publicacion' => $_POST['anio_publicacion']
    ];
    $editSuccess = $bookAdminController->updateBook($libroId, $updateData);
    // Refrescar datos del libro
    $libro = $bookAdminController->getBookById($libroId);
}

// Procesar eliminación si es admin y se envió el formulario de eliminar
if (!empty($userData) && $userData['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
    $bookAdminController->deleteBook($libroId);
    header('Location: prueba.php?success=eliminado');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($libro['titulo']) ?> - Biblioteca Virtual</title>
    <link rel="stylesheet" href="./assets/css/main_styles.css">
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

        .book-details-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .book-details-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .book-image-section {
            text-align: center;
        }

        .book-image {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }

        .book-info-section h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .book-meta {
            display: grid;
            gap: 1rem;
            margin: 2rem 0;
        }

        .meta-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #4a6fa5;
        }

        .meta-icon {
            font-size: 1.2rem;
            color: #4a6fa5;
            margin-right: 1rem;
            width: 20px;
        }

        .meta-label {
            font-weight: bold;
            color: #555;
            margin-right: 0.5rem;
        }

        .meta-value {
            color: #333;
        }

        .description-section {
            margin: 2rem 0;
        }

        .description-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #4a6fa5;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .description-text {
            line-height: 1.6;
            color: #555;
            font-size: 1.1rem;
        }

        .availability-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin: 2rem 0;
        }

        .availability-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .availability-text {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
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

        .btn-back {
            background: #28a745;
            color: white;
        }

        .btn-back:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .book-details-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .book-info-section h1 {
                font-size: 2rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .availability-number {
                font-size: 2rem;
            }
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

    <main class="book-details-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/ProyectoFinalDS7/prueba.php">
                <i class="fas fa-arrow-left"></i> Volver a la biblioteca
            </a>
        </div>

        <!-- Alertas -->
        <?php if ($yaReservado): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> Ya tienes este libro reservado.
            </div>
        <?php endif; ?>

        <?php if ($libro['cantidad_disponible'] <= 0): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Este libro no está disponible actualmente.
            </div>
        <?php endif; ?>

        <!-- Detalles del libro -->
        <div class="book-details-grid">
            <!-- Imagen del libro -->
            <div class="book-image-section">
                <?php if (!empty($libro['imagen_path'])): ?>
                    <img src="<?= htmlspecialchars($libro['imagen_path']) ?>" 
                         alt="Portada de <?= htmlspecialchars($libro['titulo']) ?>" 
                         class="book-image">
                <?php else: ?>
                    <div class="book-image" style="background: #ddd; display: flex; align-items: center; justify-content: center; min-height: 400px;">
                        <i class="fas fa-book" style="font-size: 4rem; color: #999;"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información del libro -->
            <div class="book-info-section">
                <h1><?= htmlspecialchars($libro['titulo']) ?></h1>

                <div class="book-meta">
                    <div class="meta-item">
                        <i class="fas fa-user meta-icon"></i>
                        <span class="meta-label">Autor:</span>
                        <span class="meta-value"><?= htmlspecialchars($libro['autor']) ?></span>
                    </div>

                    <div class="meta-item">
                        <i class="fas fa-barcode meta-icon"></i>
                        <span class="meta-label">ISBN:</span>
                        <span class="meta-value"><?= htmlspecialchars($libro['isbn']) ?></span>
                    </div>

                    <div class="meta-item">
                        <i class="fas fa-tags meta-icon"></i>
                        <span class="meta-label">Categoría:</span>
                        <span class="meta-value"><?= htmlspecialchars($libro['categoria_nombre'] ?: 'Sin categoría') ?></span>
                    </div>

                    <div class="meta-item">
                        <i class="fas fa-calendar-alt meta-icon"></i>
                        <span class="meta-label">Año de publicación:</span>
                        <span class="meta-value"><?= htmlspecialchars($libro['anio_publicacion'] ?: 'No especificado') ?></span>
                    </div>

                    <div class="meta-item">
                        <i class="fas fa-boxes meta-icon"></i>
                        <span class="meta-label">Cantidad total:</span>
                        <span class="meta-value"><?= htmlspecialchars($libro['cantidad']) ?> ejemplares</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disponibilidad -->
        <div class="availability-section">
            <div class="availability-number"><?= $libro['cantidad_disponible'] ?></div>
            <div class="availability-text">
                <?= $libro['cantidad_disponible'] > 0 ? 'Ejemplares disponibles' : 'No disponible' ?>
            </div>
        </div>

        <!-- Descripción -->
        <?php if (!empty($libro['descripcion'])): ?>
            <div class="description-section">
                <h3><i class="fas fa-align-left"></i> Descripción</h3>
                <div class="description-text">
                    <?= nl2br(htmlspecialchars($libro['descripcion'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Botones de acción -->
        <div class="action-buttons">
            <a href="/ProyectoFinalDS7/prueba.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Volver a la biblioteca
            </a>

            <?php if (!$yaReservado && $libro['cantidad_disponible'] > 0): ?>
                <form action="reservar_libro.php" method="POST" style="flex: 1;">
                    <input type="hidden" name="libro_id" value="<?= $libro['id'] ?>">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-bookmark"></i> Reservar este libro
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary" disabled style="flex: 1;">
                    <i class="fas fa-times"></i> 
                    <?= $yaReservado ? 'Ya reservado' : 'No disponible' ?>
                </button>
            <?php endif; ?>

            <?php if (!empty($userData) && $userData['role'] === 'admin'): ?>
                <button class="btn btn-primary" onclick="document.getElementById('edit-form-section').style.display='block';window.scrollTo(0,document.body.scrollHeight);return false;">
                    <i class="fas fa-edit"></i> Editar libro
                </button>
                <form action="" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este libro? Esta acción no se puede deshacer.');">
                    <input type="hidden" name="delete_book" value="1">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-trash"></i> Eliminar libro
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($userData) && $userData['role'] === 'admin'): ?>
        <div id="edit-form-section" style="display:<?= (isset($_POST['edit_book']) ? 'block' : 'none') ?>;margin-top:2rem;">
            <h2>Editar libro</h2>
            <?php if ($editSuccess === true): ?>
                <div class="alert alert-success">¡Libro actualizado correctamente!</div>
            <?php elseif ($editSuccess === false): ?>
                <div class="alert alert-danger">Error al actualizar el libro.</div>
            <?php endif; ?>
            <form action="" method="POST" style="background:#f8f9fa;padding:2rem;border-radius:12px;max-width:600px;">
                <input type="hidden" name="edit_book" value="1">
                <div style="margin-bottom:1rem;">
                    <label>Título:</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" required style="width:100%;padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Autor:</label>
                    <input type="text" name="autor" value="<?= htmlspecialchars($libro['autor']) ?>" required style="width:100%;padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>ISBN:</label>
                    <input type="text" name="isbn" value="<?= htmlspecialchars($libro['isbn']) ?>" required style="width:100%;padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Descripción:</label>
                    <textarea name="descripcion" style="width:100%;padding:0.5rem;" rows="3"><?= htmlspecialchars($libro['descripcion']) ?></textarea>
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Categoría (ID):</label>
                    <input type="number" name="categoria_id" value="<?= htmlspecialchars($libro['categoria_id']) ?>" required style="width:100%;padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Cantidad total:</label>
                    <input type="number" name="cantidad" value="<?= htmlspecialchars($libro['cantidad']) ?>" required style="width:100%;padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Cantidad disponible:</label>
                    <input type="number" name="cantidad_disponible" value="<?= htmlspecialchars($libro['cantidad_disponible']) ?>" required style="width:100%;padding:0.5rem;">
                </div>
                <div style="margin-bottom:1rem;">
                    <label>Año de publicación:</label>
                    <input type="number" name="anio_publicacion" value="<?= htmlspecialchars($libro['anio_publicacion']) ?>" style="width:100%;padding:0.5rem;">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
