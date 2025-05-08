<?php
/**
 * Procesamiento de Solicitudes de Reserva de Libros.
 *
 * Este script maneja la lógica para que un estudiante pueda reservar un libro.
 * Realiza las siguientes acciones:
 * 1. Inicia la sesión y verifica que el usuario esté logueado y sea de tipo 'estudiante'.
 * 2. Valida que se haya recibido un ID de libro válido a través del método GET.
 * 3. Comprueba que el libro solicitado exista en la base de datos.
 * 4. Verifica que el estudiante no tenga ya una reserva activa (pendiente o lista para recoger) para el mismo libro.
 * 5. Si todas las validaciones son correctas, inserta un nuevo registro de reserva en la tabla 'reservas'
 *    con un estado inicial de 'pendiente'.
 * 6. Almacena un mensaje de éxito o error en la sesión para ser mostrado en la página del catálogo.
 * 7. Redirige al usuario de vuelta a la página del catálogo de libros.
 */

// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';
// Iniciar la sesión para acceder a las variables de sesión del usuario y para almacenar mensajes.
session_start();

// Control de acceso: Verificar si el usuario está logueado y es de tipo 'estudiante'.
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "estudiante") {
    $_SESSION['mensaje_catalogo'] = "Error: Debes iniciar sesión como estudiante para reservar.";
    header("Location: index.php"); // Redirigir al login si no cumple los requisitos.
    exit();
}

// Validar el ID del libro: Verificar si se recibió el parámetro 'id_libro' y si es un entero válido.
if (!isset($_GET['id_libro']) || !filter_var($_GET['id_libro'], FILTER_VALIDATE_INT)) {
    $_SESSION['mensaje_catalogo'] = "Error: ID de libro no válido.";
    header("Location: catalogo_libros.php"); // Redirigir al catálogo si el ID no es válido.
    exit();
}
// Obtener y castear el ID del libro a entero y obtener el ID del usuario logueado.
$id_libro_a_reservar = (int)$_GET['id_libro'];
$id_usuario_logueado = $_SESSION["id_usuario"];

$stmt_check_libro = $conexion->prepare("SELECT id_libro FROM libros WHERE id_libro = ?");
$stmt_check_libro->bind_param("i", $id_libro_a_reservar);
$stmt_check_libro->execute();
$result_check_libro = $stmt_check_libro->get_result();
if ($result_check_libro->num_rows === 0) {
    $_SESSION['mensaje_catalogo'] = "Error: El libro que intentas reservar no existe.";
    header("Location: catalogo_libros.php");
    $stmt_check_libro->close();
    exit();
}
$stmt_check_libro->close();

$stmt_check_reserva_activa = $conexion->prepare("SELECT id_reserva FROM reservas WHERE id_libro = ? AND id_usuario = ? AND estado_reserva IN ('pendiente', 'lista_para_recoger')");
$stmt_check_reserva_activa->bind_param("ii", $id_libro_a_reservar, $id_usuario_logueado);
$stmt_check_reserva_activa->execute();
$result_check_reserva_activa = $stmt_check_reserva_activa->get_result();

if ($result_check_reserva_activa->num_rows > 0) {
    $_SESSION['mensaje_catalogo'] = "Ya tienes una reserva activa o lista para recoger para este libro.";
    header("Location: catalogo_libros.php");
    $stmt_check_reserva_activa->close();
    exit();
}
$stmt_check_reserva_activa->close();

$fecha_reserva = date("Y-m-d H:i:s");
$estado_inicial_reserva = 'pendiente'; // El estado inicial de una reserva es 'pendiente'

$stmt_insert_reserva = $conexion->prepare("INSERT INTO reservas (id_libro, id_usuario, fecha_reserva, estado_reserva) VALUES (?, ?, ?, ?)");
$stmt_insert_reserva->bind_param("iiss", $id_libro_a_reservar, $id_usuario_logueado, $fecha_reserva, $estado_inicial_reserva);

try {
    if ($stmt_insert_reserva->execute()) {
        $_SESSION['mensaje_catalogo'] = "¡Libro reservado con éxito! Puedes ver el estado de tu reserva en 'Mis Reservas'.";
    } else {
        // Este else podría no ser alcanzado si execute() siempre lanza una excepción en caso de error.
        $_SESSION['mensaje_catalogo'] = "Error al intentar reservar el libro: " . $stmt_insert_reserva->error;
    }
} catch (mysqli_sql_exception $e) {
    // Capturar cualquier excepción de SQL durante la ejecución
    $_SESSION['mensaje_catalogo'] = "Error al procesar la reserva: " . $e->getMessage();
    // Podrías añadir lógica para códigos de error específicos si es necesario, ej: if ($e->getCode() == ...)
}

$stmt_insert_reserva->close();
$conexion->close();
header("Location: catalogo_libros.php"); // Redirigir de vuelta al catálogo
exit();