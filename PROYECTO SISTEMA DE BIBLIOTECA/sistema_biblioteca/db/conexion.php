<?php
/**
 * Configuración y Establecimiento de la Conexión a la Base de Datos.
 *
 * Este script se encarga de definir los parámetros de conexión
 * a la base de datos MySQL y de establecer dicha conexión utilizando
 * la extensión MySQLi.
 *
 * Variables de Conexión:
 * @var string $host          El servidor donde se encuentra la base de datos (ej. "localhost").
 * @var string $usuario       El nombre de usuario para acceder a la base de datos (ej. "root").
 * @var string $contrasena    La contraseña para el usuario de la base de datos.
 *                            (Dejar en blanco si no hay contraseña, como es común en XAMPP por defecto).
 * @var string $base_de_datos El nombre de la base de datos a la que se conectará.
 *
 * Objeto de Conexión:
 * @var mysqli $conexion      Una instancia del objeto mysqli que representa la conexión
 *                            a la base de datos. Este objeto se utilizará en otros
 *                            scripts para realizar consultas.
 *
 * Manejo de Errores:
 * Si la conexión falla, el script terminará la ejecución (usando `die()`)
 * y mostrará un mensaje de error. En un entorno de producción,
 * se recomienda manejar los errores de forma más controlada (ej. registrándolos
 * en un archivo de log y mostrando un mensaje genérico al usuario).
 */

$host = "localhost";
$usuario = "root";
$contrasena = ""; // cambia esto si tienes contraseña en tu MySQL
$base_de_datos = "sistema_biblioteca";

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conexion->connect_error) {
 die("Conexión fallida: " . $conexion->connect_error);
}
// Configurar el conjunto de caracteres a UTF-8 para soportar caracteres especiales.
$conexion->set_charset("utf8");
?>
