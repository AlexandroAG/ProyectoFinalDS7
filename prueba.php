<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca - Libros</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos básicos para los filtros, puedes ajustarlos */
        .filters-container {
            display: flex;
            justify-content: center;
            gap: 20px; /* Espacio entre los filtros */
            padding: 20px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap; /* Permite que los elementos se envuelvan en pantallas pequeñas */
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filter-group label {
            font-weight: bold;
            color: #555;
        }
        .filter-group input[type="search"],
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            width: 200px; /* Ancho ajustable */
        }
        .filter-group button {
            padding: 8px 15px;
            background-color: #007bff; /* Color azul, ajusta a tu tema */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        .filter-group button:hover {
            background-color: #0056b3;
        }

        /* Estilos para el menú de navegación (basados en tu imagen) */
        nav.main-nav {
            background-color: #333; /* Color de fondo de tu barra de navegación */
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav.main-nav .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        nav.main-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 25px; /* Espacio entre los elementos del menú */
        }
        nav.main-nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 5px 0;
            transition: color 0.3s ease;
        }
        nav.main-nav ul li a:hover {
            color: #007bff; /* Color al pasar el ratón */
        }
    </style>
</head>
<body>

    <nav class="main-nav">
        <div class="logo">Sistema de Biblioteca</div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="libros.php">Libros</a></li>
            <li><a href="reservados.php">Reservados</a></li>
            <li><a href="roles.php">Roles</a></li>
            <li><a href="perfil.php">Perfil</a></li>
            </ul>
    </nav>

    <div class="filters-container">
        <form action="libros.php" method="GET" class="filter-group">
            <label for="search">Buscar Libro:</label>
            <input type="search" id="search" name="search" placeholder="Título, autor, ISBN..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit"><i class="fas fa-search"></i> Buscar</button>
        </form>

        <form action="libros.php" method="GET" class="filter-group">
            <label for="category">Categoría:</label>
            <select id="category" name="category" onchange="this.form.submit()">
                <option value="">Todas las Categorías</option>
                <?php
                // Simulación de datos de categorías de la base de datos
                // En un entorno real, esto vendría de una consulta a la BD
                $categories = [
                    ['id' => 1, 'name' => 'Ficción'],
                    ['id' => 2, 'name' => 'Ciencia'],
                    ['id' => 3, 'name' => 'Historia'],
                    ['id' => 4, 'name' => 'Programación']
                ];

                $selectedCategory = $_GET['category'] ?? '';

                foreach ($categories as $cat) {
                    $selected = ($selectedCategory == $cat['id']) ? 'selected' : '';
                    echo "<option value='{$cat['id']}' {$selected}>" . htmlspecialchars($cat['name']) . "</option>";
                }
                ?>
            </select>
            </form>
    </div>

    <main style="padding: 20px;">
        <h2>Listado de Libros</h2>
        <?php
        // Ejemplo de cómo manejar los parámetros de búsqueda y categoría
        $searchTerm = $_GET['search'] ?? '';
        $categoryId = $_GET['category'] ?? '';

        echo "<p>Buscando: " . htmlspecialchars($searchTerm) . "</p>";
        echo "<p>Categoría seleccionada: " . htmlspecialchars($categoryId) . "</p>";

        // Aquí iría tu lógica para consultar la base de datos
        // usando $searchTerm y $categoryId para filtrar los resultados.
        // Por ejemplo:
        /*
        $sql = "SELECT * FROM libros WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($searchTerm)) {
            $sql .= " AND (titulo LIKE ? OR autor LIKE ?)";
            $params[] = "%" . $searchTerm . "%";
            $params[] = "%" . $searchTerm . "%";
            $types .= "ss";
        }
        if (!empty($categoryId)) {
            $sql .= " AND categoria_id = ?";
            $params[] = $categoryId;
            $types .= "i";
        }

        // Luego prepararías y ejecutarías tu consulta con mysqli_stmt_bind_param
        // ...
        */
        ?>
        <p> (Aquí irían los resultados de tus libros filtrados) </p>
    </main>

    <script src="path/to/your/main.js"></script>
</body>
</html>