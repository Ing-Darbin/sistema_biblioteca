<?php
// --- BLOQUE PHP INICIAL: Conexión, Sesión y Obtener Datos del Libro a Editar ---

// Incluir el archivo de conexión a la base de datos
require 'db/conexion.php';

// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado y si es bibliotecario
// Si no está logueado o no es bibliotecario, redirigir al login
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "bibliotecario") {
    header("Location: index.php");
    exit(); // Asegurarse de que el script se detenga después de redirigir
}

$mensaje = ''; // Variable para mensajes de actualización
$libro_a_editar = null; // Variable para almacenar los datos del libro que se va a editar

// --- BLOQUE PHP PARA OBTENER DATOS DEL LIBRO CUANDO SE ACCEDE A LA PÁGINA (Normalmente por GET) ---

// Verificar si se recibió un ID de libro válido por GET (cuando se hace clic en "Editar" desde libros.php)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id_libro = $_GET['id'];

    // Validar que el ID sea un número entero positivo (prevención básica)
    if (filter_var($id_libro, FILTER_VALIDATE_INT) && $id_libro > 0) {
        // Preparar la consulta para obtener todos los datos del libro específico
        $stmt_select = $conexion->prepare("SELECT * FROM libros WHERE id_libro = ?");
        // Vincular el parámetro: 'i' indica que id_libro es un entero
        $stmt_select->bind_param("i", $id_libro);
        // Ejecutar la consulta
        $stmt_select->execute();
        // Obtener el resultado
        $resultado_select = $stmt_select->get_result();

        // Verificar si se encontró exactamente un libro con ese ID
        if ($resultado_select->num_rows === 1) {
            // Obtener los datos del libro como un array asociativo
            $libro_a_editar = $resultado_select->fetch_assoc();
        } else {
            // Si no se encontró el libro (ID incorrecto o libro no existe), mostrar un mensaje de error
            $mensaje = "Error: Libro no encontrado o ID no válido.";
            // Podrías redirigir de vuelta a libros.php si el libro no existe
            // header("Location: libros.php?mensaje=no_encontrado"); exit();
        }

        // Cerrar el statement de selección
        $stmt_select->close();
    } else {
        // Si el ID recibido no es un número válido
        $mensaje = "Error: ID de libro no válido.";
        // Podrías redirigir de vuelta a libros.php
        // header("Location: libros.php?mensaje=id_invalido"); exit();
    }
}

// --- FIN BLOQUE PHP PARA OBTENER DATOS DEL LIBRO ---

// --- BLOQUE PHP PARA PROCESAR EL FORMULARIO CUANDO SE ENVÍA POR POST (Clic en "Guardar Cambios") ---

// Verificar si el formulario fue enviado usando el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos enviados desde el formulario POST
    // El id_libro viene de un campo oculto en el formulario
    $id_libro = $_POST["id_libro"];
    $titulo = $_POST["titulo"];
    $autor = $_POST["autor"];
    $isbn = $_POST["isbn"];
    $id_categoria = $_POST["id_categoria"];
    $estado = $_POST["estado"]; // Obtenemos el estado seleccionado


    // Validar que el ID de libro sea válido antes de intentar actualizar
    if (filter_var($id_libro, FILTER_VALIDATE_INT) && $id_libro > 0) {

        // Preparar la consulta SQL para actualizar el libro en la base de datos
        // Se actualizan los campos para el libro con el id_libro específico
        $stmt_update = $conexion->prepare("UPDATE libros SET titulo = ?, autor = ?, isbn = ?, id_categoria = ?, estado = ? WHERE id_libro = ?");

        // Vincular los parámetros a la consulta preparada
        // 'ssssii': string (titulo), string (autor), string (isbn), integer (id_categoria), string (estado), integer (id_libro)
        // Asegúrate de que los tipos coincidan con las columnas de tu tabla y las variables
        $stmt_update->bind_param("sssssi", $titulo, $autor, $isbn, $id_categoria, $estado, $id_libro);

        // Ejecutar la consulta de actualización
        if ($stmt_update->execute()) {
            $mensaje = "Libro actualizado correctamente.";
            // Después de actualizar, redirigir a la lista de libros
            // You can pass a message via GET parameter or use session flash messages
            header("Location: libros.php?mensaje=actualizado_exitoso");
            exit(); // Asegurarse de que el script se detenga si rediriges

        } else {
            // Manejar errores durante la actualización
            $mensaje = "Error al actualizar el libro: " . $conexion->error;
        }

        // Cerrar el statement de actualización
        $stmt_update->close();
    } else {
        // Si el ID recibido del formulario oculto no es válido
        $mensaje = "Error al actualizar: ID de libro no válido.";
    }

    // Nota: La conexión ($conexion) se mantiene abierta.
}

// --- FIN BLOQUE PHP PARA PROCESAR FORMULARIO POST ---
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Libro - Sistema de Biblioteca</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <h1 class="mb-4 text-center">Editar Libro</h1>

        <?php if ($mensaje): ?>
            <div class='alert <?php echo (strpos($mensaje, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?>' role='alert'>
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <?php
        // Solo mostrar el formulario si se encontró un libro para editar ($libro_a_editar no es null)
        if ($libro_a_editar):
        ?>
            <div class="card p-4 shadow-sm mb-4">
                <h3 class="card-title text-center">Editar Datos del Libro</h3>
                <form method="POST" action="editar_libro.php">
                    <input type="hidden" name="id_libro" value="<?php echo htmlspecialchars($libro_a_editar['id_libro']); ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título:</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($libro_a_editar['titulo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="autor" class="form-label">Autor:</label>
                        <input type="text" class="form-control" id="autor" name="autor" value="<?php echo htmlspecialchars($libro_a_editar['autor']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="isbn" class="form-label">ISBN:</label>
                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($libro_a_editar['isbn']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoría:</label>
                        <select class="form-select" id="id_categoria" name="id_categoria" required>
                            <option value="">-- Seleccione Categoría --</option>
                            <?php
                            // --- CÓDIGO PHP PARA OBTENER CATEGORÍAS Y SELECCIONAR LA ACTUAL ---
                            // Necesita $conexion. Consulta la tabla categorias.
                            $categorias_result = $conexion->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre");
                            if ($categorias_result && $categorias_result->num_rows > 0) {
                                while ($cat = $categorias_result->fetch_assoc()) {
                                    // Comparar el ID de la categoría con el id_categoria del libro actual
                                    $selected = ($cat['id_categoria'] == $libro_a_editar['id_categoria']) ? 'selected' : '';
                                    echo "<option value='{$cat['id_categoria']}' {$selected}>" . htmlspecialchars($cat['nombre']) . "</option>";
                                }
                                $categorias_result->free(); // Liberar resultados
                            } else {
                                echo "<option value=''>No hay categorías disponibles</option>";
                            }
                            // --- FIN CÓDIGO PHP ---
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado:</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="disponible" <?php echo ($libro_a_editar['estado'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                            <option value="prestado" <?php echo ($libro_a_editar['estado'] == 'prestado') ? 'selected' : ''; ?>>Prestado</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                </form>
            </div> <?php
                // Si $libro_a_editar es null (no se encontró el libro), se muestra el mensaje de error que se estableció antes
                else:
                    ?>
            <div class="alert alert-warning text-center" role="alert">
                <?php echo $mensaje ? htmlspecialchars($mensaje) : "No se pudo cargar la información del libro. Verifique si el ID es correcto."; ?>
            </div>
        <?php endif; ?>


        <div class="text-center mt-3 mb-4">
            <a href="libros.php" class="btn btn-secondary">Volver a la Lista de Libros</a>
        </div>


    </div> <?php
            // --- CERRAR LA CONEXIÓN A LA BASE DE DATOS ---
            // Es una buena práctica cerrar la conexión al finalizar el uso de la base de datos en el script.
            if ($conexion) {
                $conexion->close();
            }
            ?>

</body>

</html>