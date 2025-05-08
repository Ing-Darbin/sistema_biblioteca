<?php
/**
 * Registro de Préstamos de Libros.
 *
 * Este script permite a los bibliotecarios registrar un nuevo préstamo de libro.
 * Muestra un formulario para seleccionar el estudiante, el libro (solo disponibles),
 * y las fechas de préstamo y devolución.
 * Al enviar el formulario, actualiza el estado del libro a 'prestado' e inserta
 * un nuevo registro en la tabla de préstamos.
 */

// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';
// Iniciar la sesión para verificar los permisos del usuario.
session_start();

// Control de acceso:
// Solo los bibliotecarios pueden acceder a esta página.
if (
    !isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !==
    "bibliotecario"
) {
    header("Location: index.php");
    exit();
}

$mensaje = ''; // Variable para almacenar mensajes de feedback.

// --- PROCESAMIENTO DEL FORMULARIO DE REGISTRO DE PRÉSTAMO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST["id_usuario"];
    $id_libro = $_POST["id_libro"];
    $fecha_prestamo = $_POST["fecha_prestamo"];     // Fecha de préstamo del formulario.
    $fecha_devolucion = $_POST["fecha_devolucion"]; // Fecha de devolución estimada del formulario.

    // Iniciar una transacción para asegurar la atomicidad de las operaciones.
    $conexion->begin_transaction();

    // Paso 1: Cambiar estado del libro a "prestado".
    $stmt_update_libro = $conexion->prepare("UPDATE libros SET estado = 'prestado' WHERE id_libro = ?");
    $stmt_update_libro->bind_param("i", $id_libro);

    if ($stmt_update_libro->execute()) {
        // Paso 2: Insertar el registro del préstamo.
        $stmt_insert_prestamo = $conexion->prepare("INSERT INTO prestamos (id_usuario, id_libro, fecha_prestamo, fecha_devolucion) VALUES (?, ?, ?, ?)");
        $stmt_insert_prestamo->bind_param("iiss", $id_usuario, $id_libro, $fecha_prestamo, $fecha_devolucion);
        if ($stmt_insert_prestamo->execute()) {
            $conexion->commit(); // Confirmar la transacción si ambas operaciones son exitosas.
            $mensaje = "Préstamo registrado correctamente.";
        } else {
            $mensaje = "Error al registrar el préstamo: " . $stmt_insert_prestamo->error;
            // Consider reverting book status or logging error
        }
        $stmt_insert_prestamo->close();
    } else {
        $conexion->rollback(); // Revertir la transacción si falla la actualización del libro.
        $mensaje = "Error al actualizar el estado del libro: " . $stmt_update_libro->error;
    }
    $stmt_update_libro->close();
}

// --- DEFINICIÓN DE FECHAS POR DEFECTO PARA EL FORMULARIO ---
// Fecha de préstamo por defecto: día actual.
$fecha_prestamo_default = date("Y-m-d");
// Fecha de devolución por defecto: 7 días a partir de hoy.
$fecha_devolucion_default = date("Y-m-d", strtotime("+7 days"));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Préstamo</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min
.css" rel="stylesheet">
    <!-- Estilos CSS para la página -->
    <style>
        body {
            /* Estilo de fondo similar a otras páginas del sistema */
            background-image: url('imagenes/fondo_biblioteca.jpg'); /* Ruta a la imagen de fondo */
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: #f8f9fa; /* Color de respaldo */
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9); /* Fondo semi-transparente para legibilidad */
            padding: 20px;
            border-radius: 8px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Préstamo de Libro</h2>
        
        <!-- Mostrar mensajes de feedback (éxito/error) -->
        <?php if (!empty($mensaje)) { // Se cambió isset($mensaje) por !empty($mensaje) para más robustez
            echo "<div class='alert " . (strpos($mensaje, 'Error') !== false ? 'alert-danger' : 'alert-success') . "'>" . htmlspecialchars($mensaje) . "</div>";
        } ?>

        <!-- Formulario para registrar un nuevo préstamo -->
        <form method="POST" class="card p-4 shadow-sm">
            <!-- Selección de Estudiante -->
            <div class="mb-3">
                <label>Estudiante:</label>
                <select name="id_usuario" class="form-control" required>
                    <option value="">-- Seleccione Estudiante --</option>
                    <?php
                    // Obtener y listar todos los usuarios de tipo 'estudiante'.
                    $usuarios = $conexion->query("SELECT * FROM usuarios
WHERE tipo_usuario = 'estudiante'");
                    while ($u = $usuarios->fetch_assoc()) {
                        echo "<option
value='{$u['id_usuario']}'>" . htmlspecialchars($u['nombre']) . " - " . htmlspecialchars($u['email']) . "</option>";
                    }
                    if ($usuarios) $usuarios->free(); // Liberar resultados
                    ?>
                </select>
            </div>
            <!-- Selección de Libro (solo disponibles) -->
            <div class="mb-3">
                <label>Libro:</label>
                <select name="id_libro" class="form-control" required>
                    <option value="">-- Seleccione Libro --</option>
                    <?php
                    // Obtener y listar todos los libros con estado 'disponible'.
                    $libros = $conexion->query("SELECT * FROM libros WHERE
estado = 'disponible'");
                    while ($libro = $libros->fetch_assoc()) {
                        echo "<option
value='{$libro['id_libro']}'>" . htmlspecialchars($libro['titulo']) . " (" . htmlspecialchars($libro['isbn']) . ")</option>";
                    }
                    if ($libros) $libros->free(); // Liberar resultados
                    ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_prestamo">Fecha de Préstamo:</label>
                    <input type="date" id="fecha_prestamo" name="fecha_prestamo" class="form-control" value="<?php echo $fecha_prestamo_default; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_devolucion">Fecha de Devolución (Estimada):</label>
                    <input type="date" id="fecha_devolucion" name="fecha_devolucion" class="form-control" value="<?php echo $fecha_devolucion_default; ?>">
                </div>
            </div>
            <p class="form-text">El periodo de préstamo por defecto es de 7 días.</p>
            <!-- Botón de envío del formulario -->
            <button type="submit" class="btn btn-primary">Prestar
                Libro</button>
        </form>
        <!-- Enlace para volver al panel de control -->
        <a href="dashboard.php" class="btn btn-secondary mt-3">Volver al
            panel</a>
    </div>
    <?php
    // Cerrar la conexión a la base de datos al final del script.
    if ($conexion) $conexion->close();
    ?>
</body>
</html>