<?php
/**
 * Registro de Nuevos Usuarios.
 *
 * Este script permite a los visitantes registrarse en el sistema de biblioteca.
 * Recibe los datos del formulario (nombre, email, contraseña, tipo de usuario),
 * hashea la contraseña por seguridad, y luego intenta insertar el nuevo usuario
 * en la base de datos.
 * Muestra mensajes de éxito o error al usuario.
 */

// --- INICIO: Habilitar reporte de errores para depuración ---
// ¡Importante! Comenta o elimina estas líneas en un entorno de producción.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN: Habilitar reporte de errores ---

// Incluir el archivo de conexión a la base de datos
require 'db/conexion.php';

$mensaje = ''; // Variable para almacenar mensajes de feedback para el usuario.
 
// Verificar si se envió el formulario (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Obtener los datos del formulario
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];
    $tipo_usuario = $_POST['tipo_usuario']; // Obtener el tipo de usuario del formulario.

    // Validaciones básicas (se pueden expandir):
    // - Verificar que los campos no estén vacíos (ya cubierto por 'required' en HTML, pero bueno tenerlo en backend).
    // - Validar formato de email.
    // - Requerimientos de fortaleza de contraseña.

    // 2. Hashear la contraseña (¡CRUCIAL para la seguridad!)
    // Se utiliza password_hash con el algoritmo por defecto (actualmente bcrypt).
    $contrasena_hasheada = password_hash($contrasena, PASSWORD_DEFAULT);

    // 3. Preparar la consulta SQL para insertar el nuevo usuario.
    // Se usan sentencias preparadas para prevenir inyección SQL.
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, contraseña, tipo_usuario) VALUES (?, ?, ?, ?)");

    // 4. Vincular los parámetros a la consulta preparada.
    // 'ssss' indica que los cuatro parámetros son de tipo string.
    $stmt->bind_param("ssss", $nombre, $email, $contrasena_hasheada, $tipo_usuario);

    try {
        // 5. Ejecutar la consulta.
        if ($stmt->execute()) {
            $mensaje = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            // Opcional: Redirigir a la página de login después de un registro exitoso.
            // header("Location: index.php?registro=exitoso"); exit();
        } else {
            // Este bloque podría no alcanzarse si execute() siempre lanza una excepción en caso de error.
            $mensaje = "Error al registrar el usuario: " . $stmt->error;
        }
    } catch (mysqli_sql_exception $e) {
        // Manejar errores específicos, como email duplicado (código de error MySQL 1062).
        if ($e->getCode() == 1062) {
            $mensaje = "Error: El correo electrónico '" . htmlspecialchars($email) . "' ya está registrado.";
        } else {
            // Para otros errores de SQL, puedes mostrar un mensaje genérico o el error específico
            $mensaje = "Error al registrar el usuario: " . $e->getMessage();
        }
    }
    // Cerrar el statement.
    $stmt->close(); 
} // Fin de if ($_SERVER["REQUEST_METHOD"] == "POST")
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            /* Asumiendo que quieres el mismo fondo que en otras páginas */
            background-image: url('imagenes/fondo_biblioteca.jpg'); /* Ruta a tu imagen */
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: #f8f9fa; /* Color de respaldo */
            display: flex;
            align-items: center; /* Ayuda a centrar el contenido verticalmente */
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Tarjeta contenedora del formulario -->
            <div class="card p-4 shadow-sm">
                <h2 class="text-center mb-4">Registro de Nuevo Usuario</h2>

                <!-- Mostrar mensajes de feedback (éxito/error) -->
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo (strpos($mensaje, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?>" role="alert">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <form action="registro.php" method="POST">
                    <!-- Campo Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <!-- Campo Contraseña -->
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña:</label>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                    </div>
                    <!-- Campo Tipo de Usuario (Selector) -->
                    <div class="mb-3">
                        <label for="tipo_usuario" class="form-label">Tipo de Usuario:</label>
                        <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                            <option value="estudiante">Estudiante</option>
                            <option value="bibliotecario">Bibliotecario</option>
                        </select>
                    </div>
                    <!-- Botón de Envío -->
                    <button type="submit" class="btn btn-primary w-100">Registrar</button>
                </form>
                <!-- Enlace para ir a la página de inicio de sesión -->
                <p class="mt-3 text-center">¿Ya tienes una cuenta? <a href="index.php">Inicia Sesión aquí</a></p>
            </div>
        </div>
    </div>
</div>
<?php
// Cerrar la conexión a la base de datos al final del script,
// después de que todo el HTML se haya generado y si la conexión existe.
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>
</body>
</html>