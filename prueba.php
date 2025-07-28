<?php
require_once __DIR__ . '/controllers/auth_middleware.php';
require_once './config/Database.php';

// Buscar filtros
$searchTerm = $_GET['search'] ?? '';
$categoryId = $_GET['category'] ?? '';

// Conexión a la base de datos
$database = new Database();
$conn = $database->connect();

// Consulta
$sql = "SELECT libros.*, categorias_libros.nombre AS categoria_nombre 
        FROM libros 
        LEFT JOIN categorias_libros ON libros.categoria_id = categorias_libros.id 
        WHERE 1=1";
$params = [];

if (!empty($searchTerm)) {
    $sql .= " AND (titulo LIKE :search OR autor LIKE :search OR isbn LIKE :search)";
    $params[':search'] = '%' . $searchTerm . '%';
}

if (!empty($categoryId)) {
    $sql .= " AND categoria_id = :cat";
    $params[':cat'] = $categoryId;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorías simuladas
$categories = [
    ['id' => 1, 'name' => 'Ficción'],
    ['id' => 2, 'name' => 'Ciencia'],
    ['id' => 3, 'name' => 'Historia'],
    ['id' => 4, 'name' => 'Programación']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Biblioteca - Libros</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fuentes y estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <link rel="stylesheet" href="./assets/css/main_styles.css">
    
    <style>
 

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .book-card {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
header {
    background-color: #000000; /* Cambia esto a negro */
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

        .book-card:hover {
            transform: scale(1.03);
            cursor: pointer;
        }

        .book-card a {
            text-decoration: none;
            color: inherit;
        }

        .book-card a:hover {
            text-decoration: none;
        }

        .book-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .book-info {
            padding: 15px;
        }

        .book-info h3 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }

        .book-info p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }

        .book-card form {
            padding: 0 15px 15px;
        }

        .book-card button {
            width: 100%;
            padding: 10px;
            background-color: #27ae60;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .book-card button:hover {
            background-color: #219a52;
        }

        .book-card::before {
            content: "👁️ Ver detalles";
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .book-card {
            position: relative;
        }

        .book-card:hover::before {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .filters-container {
                flex-direction: column;
                gap: 10px;
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
            <a href="/ProyectoFinalDS7/views/auth/rol.php">Roles</a>
            <a href="/ProyectoFinalDS7/views/profile.php">Perfil</a>
        </nav>
    </header>

<div class="filters-container">
    <form action="prueba.php" method="GET" class="filter-group" style="flex-wrap: wrap; gap: 15px;">
        <label for="search">Buscar Libro:</label>
        <input type="search" id="search" name="search" placeholder="Título, autor, ISBN..." value="<?= htmlspecialchars($searchTerm) ?>">

        <label for="category">Categoría:</label>
        <select id="category" name="category">
            <option value="">Todas las Categorías</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit"><i class="fas fa-search"></i> Filtrar</button>
    </form>
</div>

<main>
    <p>Buscando: <?= htmlspecialchars($searchTerm) ?></p>
    <p>Categoría seleccionada: <?= htmlspecialchars($categoryId) ?></p>

    <?php if (count($libros) > 0): ?>
        <div class="book-grid">
            <?php foreach ($libros as $libro): ?>
                <div class="book-card">
                    <a href="book_details.php?id=<?= $libro['id'] ?>" style="text-decoration: none; color: inherit;">
                        <img src="<?= htmlspecialchars($libro['imagen_path'] ?: 'default.jpg') ?>" alt="Portada del libro">
                        <div class="book-info">
                            <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
                            <p><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></p>
                            <p><strong>Categoría:</strong> <?= htmlspecialchars($libro['categoria_nombre']) ?></p>
                            <p><strong>Año:</strong> <?= htmlspecialchars($libro['anio_publicacion']) ?></p>
                        </div>
                    </a>
                    <form action="reservar_libro.php" method="POST">
                        <input type="hidden" name="libro_id" value="<?= $libro['id'] ?>">
                        <button type="submit">Reservar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No se encontraron libros con los filtros aplicados.</p>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'reservado'): ?>
    <div style="color: green; font-weight: bold;">¡Reserva realizada con éxito!</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div style="color: red; font-weight: bold;">
        <?php
        switch ($_GET['error']) {
            case 'not_logged_in': echo "Debes iniciar sesión para reservar."; break;
            case 'no_libro': echo "Libro no especificado para reservar."; break;
            case 'sql_error': echo "Error al realizar la reserva."; break;
            case 'no_disponible': echo "Este libro no está disponible actualmente."; break;
            case 'ya_reservado': echo "Ya tienes este libro reservado."; break;
            case 'libro_no_encontrado': echo "El libro solicitado no fue encontrado."; break;
            default: echo "Error desconocido.";
        }
        ?>
    </div>
<?php endif; ?>

</main>

</body>
</html>
