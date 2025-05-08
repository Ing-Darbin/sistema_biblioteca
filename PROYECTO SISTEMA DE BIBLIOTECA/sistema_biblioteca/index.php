<?php

/**
 * Página de Inicio de Sesión (Login).
 *
 * Este script presenta el formulario de inicio de sesión para que los usuarios
 * puedan acceder al sistema de biblioteca. También proporciona un enlace
 * a la página de registro para nuevos usuarios.
 *
 * El formulario envía las credenciales (email y contraseña) al script `login.php`
 * para su procesamiento y autenticación.
 *
 * Si el script `login.php` detecta un error (ej. credenciales incorrectas),
 * redirige de vuelta a esta página y muestra un mensaje de error que se
 * almacena en la variable de sesión `$_SESSION['login_error']`.
 */
// Iniciar la sesión para poder acceder a mensajes de error de login.
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - Biblioteca</title>
    <!-- Enlace a la hoja de estilos de Bootstrap para el diseño -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min
.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">Sistema de Biblioteca</h2>
        <div class="row justify-content-center mt-4">
            <div class="col-md-4">
                <form action="login.php" method="POST" class="card p-4
shadow-sm">
                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" name="email" class="formcontrol" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña:</label>
                        <input type="password" name="contrasena"
                            class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w100">Iniciar sesión</button>
                </form>
                <p class="mt-3 text-center">¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
            </div>
            <h2 class="text-center">Sistema de Biblioteca</h2>
            <div class="row justify-content-center mt-4">
                <div class="col-md-4">
                    <?php
                    // Mostrar mensajes de error de login si existen en la sesión.
                    // Estos mensajes son establecidos por login.php en caso de fallo de autenticación.
                    if (isset($_SESSION['login_error'])) :
                    ?>
                        <div class="alert alert-danger" role="alert">
                            <?php
                            echo htmlspecialchars($_SESSION['login_error']); // Usar htmlspecialchars para prevenir XSS al mostrar el error.
                            unset($_SESSION['login_error']); // Limpiar el error de la sesión después de mostrarlo para que no persista.
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de inicio de sesión que envía datos a login.php mediante POST -->
                    <form action="login.php" method="POST" class="card p-4 shadow-sm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña:</label>
                            <input type="password" name="contrasena" id="contrasena" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                    </form>
                    <!-- Enlace a la página de registro para usuarios nuevos -->
                    <p class="mt-3 text-center">¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
        <!-- Opcional: Script de Bootstrap JS para funcionalidades interactivas (si se necesitaran) -->
        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
</body>

</html>