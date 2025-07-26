<?php
// registration_status.php
session_start();

$status_type = 'success'; // Por defecto, asumimos éxito
$message = "Operación completada."; // Mensaje por defecto

if (isset($_GET['mensaje'])) {
    $message = htmlspecialchars($_GET['mensaje']);
    $status_type = 'success'; // Asegurarse de que es éxito
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $status_type = 'error'; // Asegurarse de que es error
} else {
    // Si no hay parámetros, redirigir al login o a una página genérica
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Registro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            overflow: hidden;
        }
        .status-container {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            text-align: center;
            max-width: 450px;
            width: 90%;
        }
        .status-container h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }
        .status-container p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 30px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            background-color: #007bff; /* Azul por defecto para el botón */
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        /* Estilos específicos para mensajes de éxito y error */
        .success-message h2 {
            color: #28a745; /* Verde para éxito */
        }
        .error-message h2 {
            color: #dc3545; /* Rojo para error */
        }
        .success-message {
            border: 1px solid #c3e6cb;
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error-message {
            border: 1px solid #f5c6cb;
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <?php if ($status_type === 'success'): ?>
            <div class="success-message">
                <h2>¡Operación Exitosa!</h2>
                <p><?php echo $message; ?></p>
            </div>
        <?php else: /* $status_type === 'error' */ ?>
            <div class="error-message">
                <h2>¡Error!</h2>
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        
        <a href="login.php" class="btn">Ir a Iniciar Sesión</a>
    </div>
</body>
</html>
