<?php
/**
 * Cierre de Sesión de Usuario.
 *
 * Este script se encarga de finalizar la sesión activa del usuario.
 * Realiza las siguientes acciones:
 * 1. Inicia o reanuda la sesión actual. Esto es necesario para poder acceder
 *    a las funciones de sesión y destruirla correctamente.
 * 2. Destruye todas las variables de sesión registradas (ej. id_usuario, tipo_usuario).
 *    Esto efectivamente "cierra la sesión" del usuario.
 * 3. Redirige al usuario a la página de inicio de sesión (index.php).
 * 4. Termina la ejecución del script para asegurar que no se procese más código después de la redirección.
 */
session_start();
session_destroy();
header("Location: index.php");
exit(); // Es una buena práctica llamar a exit() después de una redirección con header().
?>
