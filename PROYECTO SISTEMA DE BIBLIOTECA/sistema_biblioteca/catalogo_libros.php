<?php
/**
 * Catálogo de Libros para Estudiantes.
 *
 * Este script muestra a los estudiantes el listado completo de libros del sistema,
 * indicando su estado actual (disponible, prestado).
 * Permite a los estudiantes iniciar el proceso de reserva de un libro.
 * También muestra mensajes de feedback relacionados con las acciones de reserva.
 */

// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';
// Iniciar la sesión para verificar los permisos del usuario y gestionar mensajes.
session_start();

// Control de acceso:
// Solo los estudiantes pueden acceder a esta página.
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "estudiante") {
    header("Location: index.php");
    exit();
}

$id_usuario_logueado = $_SESSION["id_usuario"]; // ID del estudiante logueado.
$mensaje_catalogo = ''; // Variable para almacenar mensajes de feedback.

// Recuperar y limpiar mensajes de feedback de la sesión (usados por procesar_reserva.php).
if (isset($_SESSION['mensaje_catalogo'])) {
    $mensaje_catalogo = $_SESSION['mensaje_catalogo'];
    unset($_SESSION['mensaje_catalogo']); // Limpiar el mensaje después de mostrarlo
}

// --- OBTENCIÓN DE TODOS LOS LIBROS PARA MOSTRAR EN EL CATÁLOGO ---
$libros_result = $conexion->query("
    SELECT l.id_libro, l.titulo, l.autor, l.isbn, c.nombre AS categoria, l.estado
    FROM libros l
    JOIN categorias c ON l.id_categoria = c.id_categoria
    ORDER BY l.titulo
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Libros - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos CSS para la página del catálogo -->
    <style>
        body {
            background-image: url('imagenes/fondo_biblioteca.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: #f8f9fa;
        }
        .container-catalogo {
            background-color: rgba(255, 255, 255, 0.95); /* Un poco más opaco para mejor legibilidad */
            padding: 30px;
            border-radius: 8px;
            margin-top: 40px;
            margin-bottom: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .estado-disponible {
            color: green;
            font-weight: bold;
        }
        .estado-prestado {
            color: orange;
            font-weight: bold;
        }
         .estado-reservado { /* Si tuvieras este estado en la tabla libros */
            color: purple;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container container-catalogo">
        <h1 class="text-center mb-4">Catálogo de Libros</h1>

        <!-- Mostrar mensajes de feedback (éxito/error/advertencia) de las acciones de reserva -->
        <?php if ($mensaje_catalogo): ?>
            <div class="alert <?php echo (strpos($mensaje_catalogo, 'Error') !== false || strpos($mensaje_catalogo, 'Ya tienes') !== false) ? 'alert-warning' : 'alert-success'; ?>" role="alert">
                <?php echo htmlspecialchars($mensaje_catalogo); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla para mostrar el listado de libros del catálogo -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>ISBN</th>
                        <th>Categoría</th>
                        <th>Estado Actual</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // Iterar sobre los resultados de la consulta de libros. ?>
                    <?php if ($libros_result && $libros_result->num_rows > 0): ?>
                        <?php while ($libro = $libros_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($libro['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($libro['categoria']); ?></td>
                                <?php // Aplicar clase CSS y texto según el estado del libro. ?>
                                <td class="estado-<?php echo htmlspecialchars(strtolower($libro['estado'])); ?>"><?php echo htmlspecialchars(ucfirst($libro['estado'])); ?></td>
                                <td>
                                    <?php // El botón de reservar siempre se muestra, la lógica de si se puede o no reservar se maneja en procesar_reserva.php ?>
                                    <a href="procesar_reserva.php?id_libro=<?php echo $libro['id_libro']; ?>" class="btn btn-sm btn-info">Reservar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?php // Mensaje si no hay libros en el catálogo. ?>
                        <tr><td colspan="6" class="text-center">No hay libros disponibles en el catálogo en este momento.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Enlace para volver al panel de control del estudiante -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Volver al Panel</a>
        </div>
    </div>
    <?php
    // Liberar resultados de la consulta y cerrar la conexión a la base de datos.
    if ($libros_result) $libros_result->free();
    if ($conexion) $conexion->close();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>