
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
                        <label for="imagen">Imagen del libro:</label>
                        <input type="file" name="imagen">
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
