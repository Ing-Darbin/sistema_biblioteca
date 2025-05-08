<?php
// --- BLOQUE PHP INICIAL: Conexión, Sesión y Verificación de Usuario ---

// Incluir el archivo de conexión a la base de datos
require 'db/conexion.php';

// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado y si es estudiante
// Si no está logueado o no es estudiante, redirigir al login
// (Podrías permitir que los bibliotecarios también vean esta página para un estudiante específico si modificas la lógica)
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "estudiante") {
    header("Location: index.php");
    exit(); // Asegurarse de que el script se detenga
}

// Obtener el ID del usuario logueado desde la sesión
$id_usuario_logueado = $_SESSION["id_usuario"];

// --- BLOQUE PHP PARA CONSULTAR LOS PRÉSTAMOS DEL ESTUDIANTE (Basado en la estructura de la tabla prestamos) ---

$prestamos_del_estudiante = null; // Variable para almacenar los préstamos

// Preparar la consulta SQL para obtener los préstamos de este usuario
// Hacemos JOIN con la tabla 'libros' para obtener el título del libro prestado
$stmt = $conexion->prepare("
    SELECT
        p.id_prestamo,
        p.fecha_prestamo,
        p.fecha_devolucion,
        p.devuelto,
        l.titulo AS titulo_libro,
        l.autor AS autor_libro
    FROM prestamos p
    JOIN libros l ON p.id_libro = l.id_libro
    WHERE p.id_usuario = ?
    ORDER BY p.fecha_prestamo DESC
");

// Vincular el ID del usuario logueado a la consulta preparada
$stmt->bind_param("i", $id_usuario_logueado); // 'i' especifica que es un integer

// Ejecutar la consulta
$stmt->execute();

// Obtener los resultados
$prestamos_del_estudiante = $stmt->get_result();

// Cerrar el statement
$stmt->close();

// Nota: La conexión ($conexion) se cerrará al final del script.

// --- FIN BLOQUE PHP PARA CONSULTAR PRÉSTAMOS ---
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Préstamos - Sistema de Biblioteca</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="mb-4 text-center">Mis Préstamos</h1>

    <div class="card p-4 shadow-sm">
        <h3 class="card-title text-center">Historial de Préstamos</h3>

        <table class="table table-striped table-hover mt-4">
            <thead>
                <tr>
                    <th>Título del Libro</th>
                    <th>Autor del Libro</th>
                    <th>Fecha de Préstamo</th>
                    <th>Fecha Límite Devolución</th>
                    <th>Estado</th>
                    </tr>
            </thead>
            <tbody>
                <?php
                // --- CÓDIGO PHP PARA MOSTRAR LOS PRÉSTAMOS ---
                // Asegúrate de que $prestamos_del_estudiante contenga los resultados

                if ($prestamos_del_estudiante && $prestamos_del_estudiante->num_rows > 0) {
                    // Bucle para recorrer los resultados y mostrar cada préstamo en una fila de la tabla
                    while ($prestamo = $prestamos_del_estudiante->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($prestamo['titulo_libro']) . "</td>";
                        echo "<td>" . htmlspecialchars($prestamo['autor_libro']) . "</td>";
                        echo "<td>" . htmlspecialchars($prestamo['fecha_prestamo']) . "</td>";
                        echo "<td>" . htmlspecialchars($prestamo['fecha_devolucion']) . "</td>";
                        // Mostrar el estado del préstamo
                        echo "<td>" . ($prestamo['devuelto'] ? 'Devuelto' : 'Pendiente') . "</td>";
                        // Aquí irían celdas para multas si las tuvieras
                        echo "</tr>";
                    }
                    $prestamos_del_estudiante->free(); // Liberar resultados
                } else {
                    // Mostrar un mensaje si el estudiante no tiene préstamos registrados
                    echo "<tr><td colspan='5' class='text-center'>No tienes préstamos registrados aún.</td></tr>"; // colspan debe coincidir con el número de columnas
                }

                // --- FIN CÓDIGO PHP ---
                ?>
            </tbody>
        </table>
        </div> <div class="text-center mt-3 mb-4">
        <a href="dashboard.php" class="btn btn-secondary">Volver al Panel</a>
    </div>

</div> <?php
// --- CERRAR LA CONEXIÓN A LA BASE DE DATOS ---
if ($conexion) {
    $conexion->close();
}
?>

</body>
</html>