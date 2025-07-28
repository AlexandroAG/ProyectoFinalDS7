
<?php
require_once './config/Database.php';

$mensaje = '';
$error = '';

// Obtener categorías existentes de la base de datos
$database = new Database();
$conn = $database->connect();
$categorias_existentes = [];

if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT id, nombre FROM categorias_libros ORDER BY nombre");
        $stmt->execute();
        $categorias_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al cargar categorías: " . $e->getMessage();
    }
}

if (isset($_POST['agregar_libro'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria_existente = trim($_POST['categoria_existente'] ?? '');
    $categoria_nueva = trim($_POST['categoria_nueva'] ?? '');
    $stock = (int) ($_POST['stock'] ?? 1);
    $anio_publicacion = (int) ($_POST['anio_publicacion'] ?? 0);

    // Determinar qué categoría usar
    $categoria_final = '';
    if ($categoria_existente === 'nueva' && !empty($categoria_nueva)) {
        $categoria_final = $categoria_nueva;
    } elseif (!empty($categoria_existente) && $categoria_existente !== 'nueva') {
        $categoria_final = $categoria_existente;
    }

    if (!$titulo || !$autor || !$isbn || !$descripcion || !$categoria_final || $stock < 1 || $anio_publicacion < 1500 || $anio_publicacion > (int)date('Y')) {
        $error = "Por favor complete todos los campos correctamente, incluyendo una categoría válida y el año válido.";
    } else {
        // Procesar imagen subida
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['imagen']['tmp_name'];
            $fileName = $_FILES['imagen']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = './uploads/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imagen_path = $dest_path;
                    $thumbnail_path = $dest_path; // Puedes hacer un thumbnail real si quieres
                } else {
                    $error = 'Error al mover el archivo de imagen.';
                }
            } else {
                $error = 'Tipo de archivo no permitido. Solo JPG, JPEG, PNG, GIF.';
            }
        } else {
            $imagen_path = null;
            $thumbnail_path = null;
        }

        if (!$error) {
            $database = new Database();
            $conn = $database->connect();

                if ($conn) {
                try {
                    // Categoría - buscar por nombre o crear nueva
                    $stmtCat = $conn->prepare("SELECT id FROM categorias_libros WHERE nombre = :nombre");
                    $stmtCat->execute([':nombre' => $categoria_final]);
                    $cat = $stmtCat->fetch(PDO::FETCH_ASSOC);
                    if ($cat) {
                        $categoria_id = $cat['id'];
                    } else {
                        $stmtInsertCat = $conn->prepare("INSERT INTO categorias_libros (nombre) VALUES (:nombre)");
                        $stmtInsertCat->execute([':nombre' => $categoria_final]);
                        $categoria_id = $conn->lastInsertId();
                    }                    // Insertar libro
                    $sql = "INSERT INTO libros 
                        (titulo, autor, isbn, descripcion, categoria_id, cantidad, cantidad_disponible, anio_publicacion, imagen_path, thumbnail_path) 
                        VALUES 
                        (:titulo, :autor, :isbn, :descripcion, :categoria_id, :cantidad, :cantidad_disponible, :anio_publicacion, :imagen_path, :thumbnail_path)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':titulo' => $titulo,
                        ':autor' => $autor,
                        ':isbn' => $isbn,
                        ':descripcion' => $descripcion,
                        ':categoria_id' => $categoria_id,
                        ':cantidad' => $stock,
                        ':cantidad_disponible' => $stock,
                        ':anio_publicacion' => $anio_publicacion,
                        ':imagen_path' => $imagen_path,
                        ':thumbnail_path' => $thumbnail_path
                    ]);
                    $mensaje = "Libro agregado correctamente.";
                } catch (PDOException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        $error = "El ISBN ya existe en la base de datos.";
                    } else {
                        $error = "Error al insertar el libro: " . $e->getMessage();
                    }
                }
            } else {
                $error = "No se pudo conectar a la base de datos.";
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
    <title>Agregar Nuevo Libro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            display: flex; /* Para centrar el formulario si se usa solo */
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Altura mínima para centrar verticalmente */
        }
        /* Estilos generales para mensajes */
        .mensaje {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .exito {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Estilos del modal y formulario (copiados de tu index.php) */
        .modal-overlay {
            /* Si este formulario se usa como un modal independiente,
               este overlay cubriría toda la pantalla. */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 450px; /* Ancho máximo se mantiene */
            padding: 0;
            overflow: hidden;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px; /* MODIFICADO: Padding del header más pequeño */
            border-bottom: 1px solid #eee;
            background-color: #f9f9f9;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
            font-size: 1em; /* MODIFICADO: Tamaño de fuente del título más pequeño */
        }

        .modal-close-button {
            background: none;
            border: none;
            font-size: 1.2em; /* MODIFICADO: Tamaño de fuente del botón cerrar más pequeño */
            color: #888;
            cursor: pointer;
            padding: 2px; /* MODIFICADO: Padding del botón cerrar más pequeño */
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        .modal-close-button:hover {
            background-color: #eee;
        }

        .modal-body {
            padding: 12px; /* MODIFICADO: Padding del cuerpo del modal más pequeño */
        }

        .form-group {
            margin-bottom: 8px; /* MODIFICADO: Espacio entre grupos de formulario más pequeño */
        }

        .form-style-modern label {
            display: block;
            font-weight: normal;
            color: #555;
            margin-bottom: 1px; /* MODIFICADO: Menos espacio entre label y input */
            font-size: 0.8em; /* MODIFICADO: Tamaño de fuente de las etiquetas más pequeño */
        }
        .form-style-modern input[type="text"],
        .form-style-modern input[type="number"],
        .form-style-modern input[type="file"],
        .form-style-modern textarea {
            width: 100%;
            padding: 5px 0; /* MODIFICADO: Padding vertical de los inputs más pequeño */
            border: none;
            border-bottom: 1px solid #ccc;
            border-radius: 0;
            outline: none;
            font-size: 0.9em; /* MODIFICADO: Tamaño de fuente de los inputs más pequeño */
            color: #333;
            background-color: transparent;
            margin-bottom: 10px; /* MODIFICADO: Espacio después de cada campo más pequeño */
        }
        .form-style-modern input[type="text"]:focus,
        .form-style-modern input[type="number"]:focus,
        .form-style-modern input[type="file"]:focus,
        .form-style-modern textarea:focus {
            border-bottom-color: #ffffffff;
        }
        .form-style-modern input::placeholder,
        .form-style-modern textarea::placeholder {
            color: #aaa;
            font-size: 0.85em; /* MODIFICADO: Tamaño de fuente del placeholder más pequeño */
        }
        .form-style-modern .form-actions {
            margin-top: 12px; /* MODIFICADO: Margen superior de las acciones más pequeño */
            padding-top: 8px; /* MODIFICADO: Padding superior de las acciones más pequeño */
            border-top: 1px solid #eee;
            text-align: center;
        }
        .form-style-modern button[type="submit"] {
            background-color: #0015ffff;
            color: white;
            padding: 8px 18px; /* MODIFICADO: Padding del botón más pequeño */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95em; /* MODIFICADO: Tamaño de fuente del botón más pequeño */
            transition: background-color 0.3s ease;
            width: 100%;
            max-width: 280px; /* Ancho máximo del botón se mantiene */
        }
        .form-style-modern button[type="submit"]:hover {
            background-color: #2cb511ff;
        }
    </style>
</head>
<body>
    <?php if (isset($mensaje)): ?>
        <div class="mensaje exito"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="mensaje error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Agregar Nuevo Libro</h2>
                <!-- El botón de cerrar aquí simplemente redirige a la página principal.
                     Para un modal real, usarías JavaScript para ocultarlo. -->
                <button class="modal-close-button" onclick="window.location.href='index.php'">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" enctype="multipart/form-data" class="form-style-modern">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" name="titulo" placeholder="Título del libro" required>
                    </div>
                    <div class="form-group">
                        <label for="autor">Autor:</label>
                        <input type="text" name="autor" placeholder="Autor del libro" required>
                    </div>
                    <div class="form-group">
                        <label for="isbn">ISBN:</label>
                        <input type="text" name="isbn" placeholder="ISBN del libro" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea name="descripcion" rows="4" placeholder="Descripción del libro" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoría:</label>
                        <input type="text" name="categoria" placeholder="Categoría (Ej: Ficción, Ciencia)" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock disponible:</label>
                        <input type="number" name="stock" value="1" min="1" placeholder="Cantidad en stock" required>
                    </div>
                    <div class="form-group">
                        <label for="anio_publicacion">Año de publicación:</label>
                        <input type="number" name="anio_publicacion" min="1500" max="<?php echo date('Y'); ?>" placeholder="Ejemplo: 2020" required>
                    </div>
                    <div class="form-group">
                        <label for="imagen">Imagen del libro:</label>
                        <input type="file" name="imagen" accept="image/*">
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="agregar_libro">Agregar libro</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
