<?php
/**
 * Proveedor de Sugerencias de Libros para Autocompletado.
 *
 * Este script es llamado vía AJAX desde libros.php.
 * Recibe un término de búsqueda (parámetro 'q' por GET) y devuelve
 * un array JSON con los títulos de libros que coinciden con dicho término.
 * Está diseñado para ser utilizado por la funcionalidad de búsqueda con autocompletado.
 */

// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';
// Iniciar la sesión para verificar los permisos del usuario.
session_start();

// Control de acceso:
// Solo los bibliotecarios pueden acceder a esta funcionalidad.
// Si el usuario no está logueado o no es bibliotecario, se devuelve un error 403 (Forbidden).
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "bibliotecario") {
    http_response_code(403); // Establecer código de estado HTTP a 403 Prohibido.
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit();
}

$sugerencias = []; // Array para almacenar los títulos de libros sugeridos.
$termino = '';     // Variable para almacenar el término de búsqueda sanitizado.

// Verificar si se recibió el parámetro 'q' (término de búsqueda) por GET.
if (isset($_GET['q'])) {
    $termino = trim($_GET['q']); // Eliminar espacios en blanco al inicio y final del término.

    // Solo proceder si el término de búsqueda no está vacío.
    if (!empty($termino)) {
        // Preparar la consulta SQL para buscar títulos de libros.
        // Se utiliza LIKE con comodines (%) para buscar coincidencias parciales (el término puede estar en cualquier parte del título).
        // Se ordenan los resultados por título y se limita a 10 sugerencias para optimizar el rendimiento.
        $stmt = $conexion->prepare("SELECT titulo FROM libros WHERE titulo LIKE ? ORDER BY titulo LIMIT 10");
        $param_termino = "%" . $termino . "%"; // Añadir comodines al término de búsqueda.
        
        // Vincular el parámetro a la consulta preparada ('s' indica que es un string).
        $stmt->bind_param("s", $param_termino);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $sugerencias[] = $fila['titulo'];
        }
        $stmt->close(); // Cerrar el statement.
    }
}

// Cerrar la conexión a la base de datos.
$conexion->close(); // Es importante cerrar la conexión una vez que ya no se necesita.

// Devolver las sugerencias en formato JSON.
// Establecer la cabecera Content-Type para indicar que la respuesta es JSON.
header('Content-Type: application/json');
echo json_encode($sugerencias);
exit(); // Terminar la ejecución del script.

?>
