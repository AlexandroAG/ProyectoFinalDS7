<?php
require_once __DIR__ . '/../controllers/ReservationController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/auth_middleware.php';

$reservationController = new ReservationController();
$authController = new AuthController();

// Función para generar ISBN simulado único para cada reserva
function generateReservationISBN($reservaId, $libroId, $usuarioId) {
    // Crear un hash único basado en los IDs
    $hash = md5($reservaId . '-' . $libroId . '-' . $usuarioId . '-reservation');
    
    // Extraer números del hash
    $numbers = preg_replace('/[^0-9]/', '', $hash);
    
    // Tomar los primeros 12 dígitos
    $isbn12 = substr($numbers, 0, 12);
    
    // Si no hay suficientes números, rellenar con ceros
    $isbn12 = str_pad($isbn12, 12, '0', STR_PAD_RIGHT);
    
    // Calcular dígito de control para ISBN-13
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $weight = ($i % 2 == 0) ? 1 : 3;
        $sum += intval($isbn12[$i]) * $weight;
    }
    $checkDigit = (10 - ($sum % 10)) % 10;
    
    // Formar ISBN-13 con prefijo 978 (estándar para libros)
    $isbn13 = '978' . substr($isbn12, 3) . $checkDigit;
    
    // Formatear como ISBN estándar: 978-X-XXXX-XXXX-X
    return '978-' . substr($isbn13, 3, 1) . '-' . substr($isbn13, 4, 4) . '-' . substr($isbn13, 8, 4) . '-' . substr($isbn13, 12, 1);
}

// Obtener datos del usuario
$userData = $authController->getProfileData();

// Manejar devolución de libros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_book'])) {
    $prestamoId = $_POST['prestamo_id'];
    if ($reservationController->returnBook($prestamoId)) {
        $successMessage = "Libro devuelto exitosamente.";
    } else {
        $errorMessage = "Error al devolver el libro.";
    }
}

// Obtener reservas del usuario
$reservas = $reservationController->getUserReservations();
$reservasActivas = $reservationController->getActiveUserReservations();
$stats = $reservationController->getUserStats();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - Biblioteca Virtual</title>
    <link rel="stylesheet" href="../assets/css/main_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .reservations-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4a6fa5;
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        .reservations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .reservation-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .reservation-card:hover {
            transform: translateY(-2px);
        }

        .reservation-header {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
        }

        .book-thumbnail {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .book-info h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
        }

        .book-info p {
            margin: 0.25rem 0;
            color: #666;
            font-size: 0.9rem;
        }

        .isbn-reserva {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .isbn-original {
            color: #666;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
        }

        .reservation-details {
            padding: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-activo {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-completado {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-atrasado {
            background: #ffebee;
            color: #d32f2f;
        }

        .return-button {
            background: #f44336;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
        }

        .return-button:hover {
            background: #d32f2f;
        }

        .no-reservations {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
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

        body {
            margin: 0;
            padding-top: 80px;
            font-family: 'Roboto', sans-serif;
            background: #f5f5f5;
        }

        nav a {
            color: white;
            margin-right: 1rem;
            text-decoration: none;
        }

        .logo {
            font-weight: bold;
            font-size: 1.2rem;
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
            <?php 
            $userData = $authController->getProfileData();
            if (!empty($userData) && $userData['role'] === 'admin'): ?>
                <a href="/ProyectoFinalDS7/views/auth/rol.php">Roles</a>
            <?php endif; ?>
            <a href="/ProyectoFinalDS7/views/profile.php">Perfil</a>
        </nav>
    </header>

    <main class="reservations-container">
        <h1>Mis Reservas</h1>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_reservas']; ?></div>
                <div class="stat-label">Total Reservas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['activas']; ?></div>
                <div class="stat-label">Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completadas']; ?></div>
                <div class="stat-label">Completadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['atrasadas']; ?></div>
                <div class="stat-label">Atrasadas</div>
            </div>
        </div>

        <!-- Lista de reservas -->
        <?php if (empty($reservas)): ?>
            <div class="no-reservations">
                <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No tienes reservas</h3>
                <p>Empieza a explorar nuestra colección de libros.</p>
                <a href="/ProyectoFinalDS7/prueba.php" style="color: #4a6fa5; text-decoration: none;">
                    Ver libros disponibles
                </a>
            </div>
        <?php else: ?>
            <div class="reservations-grid">
                <?php foreach ($reservas as $reserva): ?>
                    <div class="reservation-card">
                        <div class="reservation-header">
                            <?php if (!empty($reserva['imagen_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($reserva['imagen_path']); ?>" 
                                     alt="Portada" class="book-thumbnail">
                            <?php else: ?>
                                <div class="book-thumbnail" style="background: #ddd; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-book" style="color: #999;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="book-info">
                                <h3><?php echo htmlspecialchars($reserva['titulo']); ?></h3>
                                <p><strong>Autor:</strong> <?php echo htmlspecialchars($reserva['autor']); ?></p>
                                <p><strong><i class="fas fa-barcode"></i> ISBN Original:</strong> 
                                    <span class="isbn-original"><?php echo htmlspecialchars($reserva['isbn']); ?></span>
                                </p>
                                <p><strong><i class="fas fa-bookmark" style="color: #667eea;"></i> ISBN Reserva:</strong> 
                                    <span class="isbn-reserva">
                                        <?php echo generateReservationISBN($reserva['id'], $reserva['libro_id'], $userData['id'] ?? 0); ?>
                                    </span>
                                </p>
                                <?php if (!empty($reserva['categoria_nombre'])): ?>
                                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($reserva['categoria_nombre']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="reservation-details">
                            <div class="detail-row">
                                <span class="detail-label">Estado:</span>
                                <span class="status-badge status-<?php echo $reserva['estado']; ?>">
                                    <?php echo ucfirst($reserva['estado']); ?>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Fecha de préstamo:</span>
                                <span><?php echo date('d/m/Y', strtotime($reserva['fecha_prestamo'])); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Fecha de devolución:</span>
                                <span><?php echo date('d/m/Y', strtotime($reserva['fecha_devolucion_esperada'])); ?></span>
                            </div>
                            
                            <?php if ($reserva['fecha_devolucion_real']): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Devuelto el:</span>
                                    <span><?php echo date('d/m/Y', strtotime($reserva['fecha_devolucion_real'])); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($reserva['estado'] === 'activo'): ?>
                                <form method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="prestamo_id" value="<?php echo $reserva['id']; ?>">
                                    <button type="submit" name="return_book" class="return-button" 
                                            onclick="return confirm('¿Estás seguro de que quieres devolver este libro?')">
                                        <i class="fas fa-undo"></i> Devolver Libro
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include './partials/footer.php'; ?>
</body>
</html>
