<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Sistema Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Opcional: Si quieres usar íconos de Bootstrap -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"> -->
    <style>
        body {
            /* --- ESTILOS PARA EL FONDO DE BIBLIOTECA --- */
            background-image: url('imagenes/fondo_biblioteca.jpg'); /* Ruta a tu imagen */
            background-size: cover; /* Escala la imagen para cubrir todo el fondo */
            background-position: center center; /* Centra la imagen */
            background-repeat: no-repeat; /* Evita que la imagen se repita */
            background-attachment: fixed; /* Fija la imagen para que no se desplace con el scroll */
            background-color: #f8f9fa; /* Color de respaldo si la imagen no carga */
        }
        .dashboard-card {
            margin-top: 50px; /* Espacio desde la parte superior */
            border-radius: 0.5rem; /* Bordes un poco más redondeados */
        }
        .card-header {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card dashboard-card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h1 class="mb-0 h4 ">Panel de Control del Sistema</h1>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-success" role="alert">
                    <?php
                    // Preparar el saludo personalizado
                    $nombre_usuario_session = isset($_SESSION["nombre_usuario"]) ? trim(htmlspecialchars($_SESSION["nombre_usuario"])) : "";
                    
                    $mensaje_bienvenida = "¡Bienvenido a la Biblioteca";

                    if (!empty($nombre_usuario_session)) {
                        $mensaje_bienvenida .= ", " . $nombre_usuario_session;
                    }
                    $mensaje_bienvenida .= "!";

                    // Si quisieras un mensaje alternativo si el nombre no está, podrías añadir un else aquí.
                    // Por ejemplo: else { $mensaje_bienvenida = "¡Bienvenido a la Biblioteca!"; }
                    ?>
                    <h4 class="alert-heading"><?php echo $mensaje_bienvenida; ?></h4>
                    <p class="mb-0">Utiliza el menú a continuación para navegar por las funcionalidades del sistema.</p>
                </div>
                <h3 class="mt-4 mb-3 text-center">Menú Principal</h3>
                <div class="d-flex flex-column align-items-start gap-2">
                    <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === 'bibliotecario'): ?>
                        <a href="libros.php" class="btn btn-primary btn-lg">
                            <!-- <i class="bi bi-book-fill me-2"></i> -->Gestión de Libros
                        </a>
                        <a href="prestamos.php" class="btn btn-success btn-lg">
                            <!-- <i class="bi bi-arrow-right-square-fill me-2"></i> -->Registrar Préstamo
                        </a>
                        <a href="gestionar_prestamos.php" class="btn btn-info btn-lg">
                            <!-- <i class="bi bi-arrow-repeat me-2"></i> -->Gestionar Devoluciones
                        </a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] === 'estudiante'): ?>
                        <a href="catalogo_libros.php" class="btn btn-secondary btn-lg">
                            <!-- <i class="bi bi-journals me-2"></i> -->Ver Catálogo y Reservar
                        </a>
                        <a href="mis_prestamos.php" class="btn btn-info btn-lg">
                            <!-- <i class="bi bi-collection-fill me-2"></i> -->Mis Préstamos
                        </a>
                        <a href="mis_reservas.php" class="btn btn-warning btn-lg">
                            <!-- <i class="bi bi-bookmark-check-fill me-2"></i> -->Mis Reservas
                        </a>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <a href="logout.php" class="btn btn-danger">
                        <!-- <i class="bi bi-box-arrow-right me-2"></i> -->Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
