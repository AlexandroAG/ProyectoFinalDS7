<?php
require_once __DIR__ . '/auth_middleware.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../config/Database.php';

// Verificar que el usuario sea administrador
$authController = new AuthController();
$userData = $authController->getProfileData();

if (empty($userData) || $userData['role'] !== 'admin') {
    header('Location: ../prueba.php?error=acceso_denegado');
    exit;
}

// Configurar headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="reporte_biblioteca_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Conectar a la base de datos
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die('Error de conexión a la base de datos');
}

// Iniciar el contenido HTML para Excel
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
      <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <style>
        .header { background-color: #4472C4; color: white; font-weight: bold; }
        .data { border: 1px solid #000; }
        .center { text-align: center; }
        .number { text-align: right; }
      </style>
      </head>
      <body>';

echo '<h1>REPORTE DE BIBLIOTECA - ' . date('d/m/Y H:i:s') . '</h1>';

// ================== SECCIÓN 1: RESUMEN GENERAL ==================
echo '<h2>RESUMEN GENERAL</h2>';
echo '<table border="1" style="border-collapse: collapse; width: 100%;">';

// Obtener estadísticas generales
$stats = [];

// Total de libros
$stmt = $conn->query("SELECT COUNT(*) as total FROM libros");
$stats['total_libros'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total de categorías
$stmt = $conn->query("SELECT COUNT(*) as total FROM categorias_libros");
$stats['total_categorias'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total de usuarios
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE role = 'estudiante'");
$stats['total_estudiantes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total de préstamos activos
$stmt = $conn->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'activo'");
$stats['prestamos_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total de préstamos completados
$stmt = $conn->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'completado'");
$stats['prestamos_completados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Libros más prestados
$stmt = $conn->query("SELECT COUNT(*) as total FROM prestamos");
$stats['total_prestamos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo '<tr class="header">
        <td>CONCEPTO</td>
        <td>CANTIDAD</td>
      </tr>';
echo '<tr><td>Total de Libros</td><td class="number">' . $stats['total_libros'] . '</td></tr>';
echo '<tr><td>Total de Categorías</td><td class="number">' . $stats['total_categorias'] . '</td></tr>';
echo '<tr><td>Total de Estudiantes</td><td class="number">' . $stats['total_estudiantes'] . '</td></tr>';
echo '<tr><td>Préstamos Activos</td><td class="number">' . $stats['prestamos_activos'] . '</td></tr>';
echo '<tr><td>Préstamos Completados</td><td class="number">' . $stats['prestamos_completados'] . '</td></tr>';
echo '<tr><td>Total de Préstamos</td><td class="number">' . $stats['total_prestamos'] . '</td></tr>';
echo '</table><br><br>';

// ================== SECCIÓN 2: INVENTARIO DE LIBROS ==================
echo '<h2>INVENTARIO DE LIBROS</h2>';
echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
echo '<tr class="header">
        <td>ID</td>
        <td>TÍTULO</td>
        <td>AUTOR</td>
        <td>ISBN</td>
        <td>CATEGORÍA</td>
        <td>AÑO</td>
        <td>CANTIDAD TOTAL</td>
        <td>DISPONIBLES</td>
        <td>EN PRÉSTAMO</td>
        <td>ESTADO</td>
      </tr>';

$sql = "SELECT l.*, c.nombre as categoria_nombre,
               (l.cantidad - l.cantidad_disponible) as en_prestamo
        FROM libros l 
        LEFT JOIN categorias_libros c ON l.categoria_id = c.id 
        ORDER BY l.titulo";

$stmt = $conn->prepare($sql);
$stmt->execute();
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($libros as $libro) {
    $estado = $libro['cantidad_disponible'] > 0 ? 'DISPONIBLE' : 'AGOTADO';
    echo '<tr class="data">
            <td class="center">' . htmlspecialchars($libro['id']) . '</td>
            <td>' . htmlspecialchars($libro['titulo']) . '</td>
            <td>' . htmlspecialchars($libro['autor']) . '</td>
            <td class="center">' . htmlspecialchars($libro['isbn']) . '</td>
            <td>' . htmlspecialchars($libro['categoria_nombre'] ?: 'Sin categoría') . '</td>
            <td class="center">' . htmlspecialchars($libro['anio_publicacion'] ?: 'N/A') . '</td>
            <td class="number">' . htmlspecialchars($libro['cantidad']) . '</td>
            <td class="number">' . htmlspecialchars($libro['cantidad_disponible']) . '</td>
            <td class="number">' . htmlspecialchars($libro['en_prestamo']) . '</td>
            <td class="center">' . $estado . '</td>
          </tr>';
}
echo '</table><br><br>';

// ================== SECCIÓN 3: PRÉSTAMOS ACTIVOS ==================
echo '<h2>PRÉSTAMOS ACTIVOS</h2>';
echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
echo '<tr class="header">
        <td>ID PRÉSTAMO</td>
        <td>USUARIO</td>
        <td>EMAIL</td>
        <td>LIBRO</td>
        <td>ISBN</td>
        <td>FECHA PRÉSTAMO</td>
        <td>FECHA DEVOLUCIÓN</td>
        <td>DÍAS RESTANTES</td>
        <td>ESTADO</td>
      </tr>';

$sql = "SELECT p.*, 
               CONCAT(u.primer_nombre, ' ', u.primer_apellido) as usuario_nombre,
               u.email,
               l.titulo, l.isbn,
               DATEDIFF(p.fecha_devolucion_esperada, CURDATE()) as dias_restantes
        FROM prestamos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN libros l ON p.libro_id = l.id
        WHERE p.estado = 'activo'
        ORDER BY p.fecha_devolucion_esperada ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$prestamos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($prestamos_activos as $prestamo) {
    $estado_prestamo = $prestamo['dias_restantes'] < 0 ? 'ATRASADO' : 'ACTIVO';
    $color = $prestamo['dias_restantes'] < 0 ? 'style="background-color: #ffebee;"' : '';
    
    echo '<tr class="data" ' . $color . '>
            <td class="center">' . htmlspecialchars($prestamo['id']) . '</td>
            <td>' . htmlspecialchars($prestamo['usuario_nombre']) . '</td>
            <td>' . htmlspecialchars($prestamo['email']) . '</td>
            <td>' . htmlspecialchars($prestamo['titulo']) . '</td>
            <td class="center">' . htmlspecialchars($prestamo['isbn']) . '</td>
            <td class="center">' . date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) . '</td>
            <td class="center">' . date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) . '</td>
            <td class="number">' . $prestamo['dias_restantes'] . '</td>
            <td class="center">' . $estado_prestamo . '</td>
          </tr>';
}
echo '</table><br><br>';

// ================== SECCIÓN 4: HISTORIAL DE PRÉSTAMOS ==================
echo '<h2>HISTORIAL DE PRÉSTAMOS (ÚLTIMOS 50)</h2>';
echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
echo '<tr class="header">
        <td>ID</td>
        <td>USUARIO</td>
        <td>LIBRO</td>
        <td>ISBN</td>
        <td>FECHA PRÉSTAMO</td>
        <td>FECHA DEVOLUCIÓN ESPERADA</td>
        <td>FECHA DEVOLUCIÓN REAL</td>
        <td>ESTADO</td>
      </tr>';

$sql = "SELECT p.*, 
               CONCAT(u.primer_nombre, ' ', u.primer_apellido) as usuario_nombre,
               l.titulo, l.isbn
        FROM prestamos p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        INNER JOIN libros l ON p.libro_id = l.id
        ORDER BY p.fecha_prestamo DESC
        LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->execute();
$historial_prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($historial_prestamos as $prestamo) {
    echo '<tr class="data">
            <td class="center">' . htmlspecialchars($prestamo['id']) . '</td>
            <td>' . htmlspecialchars($prestamo['usuario_nombre']) . '</td>
            <td>' . htmlspecialchars($prestamo['titulo']) . '</td>
            <td class="center">' . htmlspecialchars($prestamo['isbn']) . '</td>
            <td class="center">' . date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) . '</td>
            <td class="center">' . date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) . '</td>
            <td class="center">' . ($prestamo['fecha_devolucion_real'] ? date('d/m/Y', strtotime($prestamo['fecha_devolucion_real'])) : 'Pendiente') . '</td>
            <td class="center">' . strtoupper($prestamo['estado']) . '</td>
          </tr>';
}
echo '</table><br><br>';

// ================== SECCIÓN 5: LIBROS MÁS PRESTADOS ==================
echo '<h2>LIBROS MÁS PRESTADOS</h2>';
echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
echo '<tr class="header">
        <td>RANKING</td>
        <td>LIBRO</td>
        <td>AUTOR</td>
        <td>ISBN</td>
        <td>CATEGORÍA</td>
        <td>TOTAL PRÉSTAMOS</td>
        <td>PRESTAMOS ACTIVOS</td>
      </tr>';

$sql = "SELECT l.titulo, l.autor, l.isbn, c.nombre as categoria_nombre,
               COUNT(p.id) as total_prestamos,
               SUM(CASE WHEN p.estado = 'activo' THEN 1 ELSE 0 END) as prestamos_activos
        FROM libros l
        LEFT JOIN prestamos p ON l.id = p.libro_id
        LEFT JOIN categorias_libros c ON l.categoria_id = c.id
        GROUP BY l.id
        ORDER BY total_prestamos DESC, l.titulo ASC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->execute();
$libros_populares = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ranking = 1;
foreach ($libros_populares as $libro) {
    echo '<tr class="data">
            <td class="center">' . $ranking . '</td>
            <td>' . htmlspecialchars($libro['titulo']) . '</td>
            <td>' . htmlspecialchars($libro['autor']) . '</td>
            <td class="center">' . htmlspecialchars($libro['isbn']) . '</td>
            <td>' . htmlspecialchars($libro['categoria_nombre'] ?: 'Sin categoría') . '</td>
            <td class="number">' . $libro['total_prestamos'] . '</td>
            <td class="number">' . $libro['prestamos_activos'] . '</td>
          </tr>';
    $ranking++;
}
echo '</table><br><br>';

echo '<p><em>Reporte generado el ' . date('d/m/Y H:i:s') . ' por el administrador: ' . htmlspecialchars($userData['full_name'] ?? 'Admin') . '</em></p>';

echo '</body></html>';
?>
