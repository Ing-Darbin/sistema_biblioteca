<?php
/**
 * Procesamiento del Inicio de Sesión.
 *
 * Este script maneja la autenticación de los usuarios.
 * Recibe el email y la contraseña enviados desde el formulario de login (index.php),
 * verifica las credenciales contra la base de datos y, si son válidas,
 * establece las variables de sesión necesarias y redirige al usuario al dashboard.
 * En caso de error, guarda un mensaje en la sesión y redirige de vuelta al formulario de login.
 */

// Iniciar la sesión para poder almacenar datos del usuario si el login es exitoso.
session_start();
// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';

// Verificar si el formulario ha sido enviado (método POST).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el email y la contraseña del formulario.
    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];

    // Preparar la consulta SQL para buscar un usuario con el email proporcionado.
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Verificar si se encontró un usuario con ese email.
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        // Verificar si la contraseña proporcionada coincide con la contraseña hasheada en la base de datos.
        if (password_verify($contrasena, $usuario["contraseña"])) {
            // Contraseña correcta: Iniciar sesión.
            // Almacenar datos importantes del usuario en la sesión.
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["tipo_usuario"] = $usuario["tipo_usuario"];
            $_SESSION["nombre_usuario"] = $usuario["nombre"]; // Guardar el nombre del usuario
            // Redirigir al panel de control (dashboard).
            header("Location: dashboard.php");
            exit(); // Importante: terminar la ejecución del script después de la redirección.
        } else {
            // Contraseña incorrecta: Guardar mensaje de error en sesión y redirigir al login.
            $_SESSION['login_error'] = "Contraseña incorrecta.";
            header("Location: index.php");
            exit();
        }
    } else {
        // Usuario no encontrado: Guardar mensaje de error en sesión y redirigir al login.
        $_SESSION['login_error'] = "Usuario no encontrado.";
        header("Location: index.php");
        exit();
    }
    // Cerrar el statement y la conexión a la base de datos.
    $stmt->close();
    $conexion->close();
}
