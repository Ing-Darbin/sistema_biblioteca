<?php
/**
 * Gestión de Préstamos y Devoluciones.
 *
 * Este script permite a los bibliotecarios:
 * - Ver un listado de todos los préstamos activos (libros no devueltos).
 * - Marcar un libro como devuelto, lo que actualiza el estado del préstamo
 *   y el estado del libro (a 'disponible').
 * Utiliza transacciones para asegurar la consistencia de los datos al procesar devoluciones.
 */

// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';
// Iniciar la sesión para verificar los permisos del usuario.
session_start();

// Control de acceso:
// Solo los bibliotecarios pueden acceder a esta página.
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "bibliotecario") {
    header("Location: index.php");
    exit();
}

$mensaje = ''; // Variable para almacenar mensajes de feedback.

// --- PROCESAMIENTO DE LA DEVOLUCIÓN DE UN LIBRO ---
// Se activa cuando se envía el formulario con accion='devolver'.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'devolver') {
    // Verificar que se recibieron los IDs necesarios.
    if (isset($_POST['id_prestamo']) && isset($_POST['id_libro'])) {
        $id_prestamo_devolver = $_POST['id_prestamo'];
        $id_libro_devuelto = $_POST['id_libro'];

        // Inicializar variables para los statements SQL para el bloque finally.
        $stmt_update_prestamo = null; 
        $stmt_update_libro = null;    

        // Iniciar una transacción para asegurar la atomicidad de las operaciones.
        $conexion->begin_transaction();

        try {
            // Paso 1: Actualizar el estado del préstamo a devuelto (devuelto = 1).
            $stmt_update_prestamo = $conexion->prepare("UPDATE prestamos SET devuelto = 1 WHERE id_prestamo = ?");
            $stmt_update_prestamo->bind_param("i", $id_prestamo_devolver);
            $stmt_update_prestamo->execute();

            // Paso 2: Actualizar el estado del libro a 'disponible'.
            $stmt_update_libro = $conexion->prepare("UPDATE libros SET estado = 'disponible' WHERE id_libro = ?");
            $stmt_update_libro->bind_param("i", $id_libro_devuelto);
            $stmt_update_libro->execute();

            // Si ambas operaciones son exitosas, confirmar la transacción.
            $conexion->commit();
            $mensaje = "Libro devuelto y préstamo actualizado correctamente.";

        } catch (mysqli_sql_exception $exception) {
            $conexion->rollback(); // Revertir todos los cambios si alguna operación falla.
            $mensaje = "Error al procesar la devolución: " . $exception->getMessage();
        } finally {
            // Asegurarse de cerrar los statements si fueron preparados
            if ($stmt_update_prestamo instanceof mysqli_stmt) {
                $stmt_update_prestamo->close();
            }
            if ($stmt_update_libro instanceof mysqli_stmt) {
                $stmt_update_libro->close();
            }
        }
    } else {
        $mensaje = "Error: Faltan datos para procesar la devolución.";
    }
}

// --- OBTENCIÓN DE PRÉSTAMOS ACTIVOS PARA MOSTRAR EN LA TABLA ---
// Consulta para obtener todos los préstamos que no han sido devueltos (devuelto = 0).
$prestamos_activos_result = $conexion->query("
    SELECT p.id_prestamo, p.fecha_prestamo, p.fecha_devolucion, 
           u.nombre AS nombre_usuario, u.email AS email_usuario,
           l.id_libro, l.titulo AS titulo_libro
    FROM prestamos p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    JOIN libros l ON p.id_libro = l.id_libro
    WHERE p.devuelto = 0
    ORDER BY p.fecha_prestamo ASC
");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Préstamos y Devoluciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos CSS para la página -->
    <style>
        body {
            background-image: url('imagenes/fondo_biblioteca.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: #f8f9fa;
        }
        .container-gestion {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 8px;
            margin-top: 40px;
            margin-bottom: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container container-gestion">
        <h1 class="text-center mb-4">Gestionar Préstamos Activos</h1>

        <!-- Mostrar mensajes de feedback (éxito/error) -->
        <?php if ($mensaje): ?>
            <div class="alert <?php echo (strpos($mensaje, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?>" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla para mostrar los préstamos activos -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Libro</th>
                        <th>Estudiante</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Devolución Estimada</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php // Iterar sobre los resultados de la consulta de préstamos activos. ?>
                    <?php if ($prestamos_activos_result && $prestamos_activos_result->num_rows > 0): ?>
                        <?php while ($prestamo = $prestamos_activos_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prestamo['titulo_libro']); ?></td>
                                <td><?php echo htmlspecialchars($prestamo['nombre_usuario']) . " (" . htmlspecialchars($prestamo['email_usuario']) . ")"; ?></td>
                                <td><?php echo htmlspecialchars($prestamo['fecha_prestamo']); ?></td>
                                <td><?php echo htmlspecialchars($prestamo['fecha_devolucion']); ?></td>
                                <td>
                                    <!-- Formulario para marcar un libro como devuelto -->
                                    <form method="POST" action="gestionar_prestamos.php" style="display:inline;">
                                        <input type="hidden" name="id_prestamo" value="<?php echo $prestamo['id_prestamo']; ?>">
                                        <input type="hidden" name="id_libro" value="<?php echo $prestamo['id_libro']; ?>">
                                        <input type="hidden" name="accion" value="devolver">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Confirmar la devolución de este libro?');">Marcar como Devuelto</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?php // Mensaje si no hay préstamos activos. ?>
                        <tr><td colspan="5" class="text-center">No hay préstamos activos en este momento.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Enlace para volver al panel de control -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Volver al Panel</a>
        </div>
    </div>
    <?php
    // Liberar resultados de la consulta y cerrar la conexión a la base de datos.
    if ($prestamos_activos_result) $prestamos_activos_result->free();
    if ($conexion) $conexion->close();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
