<?php
/**
 * Gestión de Libros del Sistema de Biblioteca.
 *
 * Este archivo permite a los usuarios bibliotecarios:
 * - Registrar nuevos libros.
 * - Ver un listado de libros existentes con opción de búsqueda y sugerencias en tiempo real.
 * - Acceder a la edición de libros.
 * - Eliminar libros (junto con sus préstamos y reservas asociadas).
 */

// --- INICIO: Habilitar reporte de errores para depuración ---
// ¡Importante! Comenta o elimina estas líneas en un entorno de producción.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN: Habilitar reporte de errores ---
 
// Incluir el archivo de conexión a la base de datos.
require 'db/conexion.php';

// Iniciar la sesión para gestionar la información del usuario.
session_start();

// Control de acceso:
// Verificar si el usuario está logueado y si es de tipo "bibliotecario".
// Si no cumple las condiciones, se redirige a la página de inicio (index.php).
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "bibliotecario") {
    header("Location: index.php");
    exit(); // Asegurarse de que el script se detenga después de redirigir
}

$mensaje = ''; // Variable para almacenar mensajes de feedback para el usuario (éxito o error).

// --- PROCESAMIENTO DEL FORMULARIO DE REGISTRO DE NUEVO LIBRO ---
// Procesar el formulario de REGISTRO cuando se envía por POST
// Se verifica que la solicitud sea POST y que no contenga el parámetro 'action' (usado para eliminar).
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_GET['action'])) { // Asegurarse que no es una acción GET
    // Obtener los datos del formulario
    $titulo = $_POST["titulo"];
    $autor = $_POST["autor"];
    $isbn = $_POST["isbn"];
    $id_categoria = $_POST["id_categoria"];
    // El estado se establece por defecto en la base de datos ('disponible')
 
    // Preparar la consulta SQL para insertar el nuevo libro
    // Usamos prepared statements para seguridad (previene inyección SQL)
    $stmt = $conexion->prepare("INSERT INTO libros (titulo, autor, isbn, id_categoria, estado) VALUES (?, ?, ?, ?, 'disponible')");
 
    // Vincular los parámetros a la consulta preparada
    // 'sssi' especifica los tipos de datos: s=string, s=string, s=string, i=integer
    $stmt->bind_param("sssi", $titulo, $autor, $isbn, $id_categoria);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $mensaje = "Libro registrado correctamente.";
    } else {
        // Manejar posibles errores en la inserción
        $mensaje = "Error al registrar el libro: " . $conexion->error;
    }
 
    // Cerrar el statement
    $stmt->close();
 
    // Nota: La conexión ($conexion) se mantiene abierta para el listado de libros.
}

// --- PROCESAMIENTO DE LA SOLICITUD DE ELIMINACIÓN DE UN LIBRO ---
// Se activa cuando la URL contiene ?action=delete&id=<id_libro>
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'delete') {
    // Validar que el ID del libro sea un entero.
    if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id_libro_a_eliminar = $_GET['id'];

        // Inicializar variables para los statements SQL.
        $stmt_delete_reservas = null;
        $stmt_delete_prestamos = null;
        $stmt_delete_libro = null;

        $conexion->begin_transaction(); // Iniciar una transacción para asegurar la atomicidad de las operaciones.

        try {
            // Paso 1: Eliminar todas las reservas asociadas al libro.
            $stmt_delete_reservas = $conexion->prepare("DELETE FROM reservas WHERE id_libro = ?");
            $stmt_delete_reservas->bind_param("i", $id_libro_a_eliminar);
            $stmt_delete_reservas->execute();
 
            // Paso 2: Eliminar todos los préstamos asociados al libro.
            $stmt_delete_prestamos = $conexion->prepare("DELETE FROM prestamos WHERE id_libro = ?");
            $stmt_delete_prestamos->bind_param("i", $id_libro_a_eliminar);
            $stmt_delete_prestamos->execute();

            // 3. Eliminar el libro
            $stmt_delete_libro = $conexion->prepare("DELETE FROM libros WHERE id_libro = ?");
            $stmt_delete_libro->bind_param("i", $id_libro_a_eliminar);
 
            if ($stmt_delete_libro->execute()) {
                $conexion->commit(); // Si todas las eliminaciones son exitosas, confirmar la transacción.
                $mensaje = "Libro y sus registros asociados eliminados correctamente.";
            } else {
                $conexion->rollback(); // Si la eliminación del libro falla, revertir todos los cambios.
                $mensaje = "Error al eliminar el libro: " . $stmt_delete_libro->error;
            }

        } catch (mysqli_sql_exception $e) {
            $conexion->rollback(); // Revertir la transacción en caso de cualquier excepción SQL.
            $mensaje = "Error al procesar la eliminación: " . $e->getMessage();
        } finally {
            // Bloque finally: se ejecuta siempre, haya o no excepción.
            // Asegurarse de cerrar los statements si fueron preparados.
            if ($stmt_delete_reservas instanceof mysqli_stmt) {
                $stmt_delete_reservas->close();
            }
            if ($stmt_delete_prestamos instanceof mysqli_stmt) {
                $stmt_delete_prestamos->close();
            }
            if ($stmt_delete_libro instanceof mysqli_stmt) {
                $stmt_delete_libro->close();
            }
        }
    } else {
        $mensaje = "Error: ID de libro no válido para eliminar.";
    }
    // Nota sobre redirección:
    // Para mostrar el mensaje, no redirigimos inmediatamente.
    // Considera usar mensajes flash con sesión y luego redirigir para una mejor práctica (PRG).
}
// --- FIN DEL BLOQUE PHP DE PROCESAMIENTO INICIAL ---
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Libros - Sistema de Biblioteca</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- Estilos CSS para el contenedor de sugerencias de búsqueda -->
    <style>
        .sugerencias-container {
            position: relative;
        }
        .lista-sugerencias {
            position: absolute;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 99;
            top: 100%; /* Posiciona justo debajo del input-group */
            left: 0;
            right: 0;
            background-color: white;
        }
        .lista-sugerencias div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .lista-sugerencias div:last-child {
            border-bottom: none;
        }
        .lista-sugerencias div:hover {
            background-color: #f0f0f0;
        }
    </style>
    <h1 class="mb-4 text-center">Gestión de Libros</h1>

    <!-- Mostrar mensajes de feedback (éxito/error) -->
    <?php if ($mensaje): ?>
        <div class='alert <?php echo (strpos($mensaje, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?>' role='alert'>
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para Registrar un Nuevo Libro -->
    <div class="card p-4 shadow-sm mb-5">
        <h3 class="card-title text-center">Registrar Nuevo Libro</h3>
        <form method="POST" action="libros.php">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título:</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>
            <div class="mb-3">
                <label for="autor" class="form-label">Autor:</label>
                <input type="text" class="form-control" id="autor" name="autor" required>
            </div>
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN:</label>
                <input type="text" class="form-control" id="isbn" name="isbn" required>
            </div>
            <div class="mb-3">
                <label for="id_categoria" class="form-label">Categoría:</label>
                <select class="form-select" id="id_categoria" name="id_categoria" required>
                    <option value="">-- Seleccione Categoría --</option> <?php
                    // --- OBTENCIÓN Y LISTADO DE CATEGORÍAS PARA EL SELECT ---
                    // Asegúrate de que $conexion esté disponible. Consulta la tabla categorias.
                    $categorias_result = $conexion->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre");
                    if ($categorias_result && $categorias_result->num_rows > 0) {
                         while ($cat = $categorias_result->fetch_assoc()) {
                            echo "<option value='{$cat['id_categoria']}'>{$cat['nombre']}</option>";
                         }
                         $categorias_result->free(); // Liberar resultados
                    } else {
                         echo "<option value=''>No hay categorías disponibles</option>";
                    }
                    // --- FIN OBTENCIÓN DE CATEGORÍAS ---
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Libro</button>
        </form>
    </div>

    <hr class="my-4">

    <!-- Sección para Listar y Buscar Libros Registrados -->
    <div class="card p-4 shadow-sm">
        <h3 class="card-title text-center">Listado de Libros Registrados</h3>

        <!-- Formulario de Búsqueda de Libros con autocompletado -->
        <form method="GET" action="libros.php" class="mb-4 mt-3" id="formBusquedaLibros">
            <div class="input-group sugerencias-container">
                <input type="text" class="form-control" name="q" id="campoBusquedaLibro" placeholder="Buscar por título..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" autocomplete="off">
                <button class="btn btn-outline-primary" type="submit" id="botonBuscarLibro">Buscar</button>
                <?php if (isset($_GET['q']) && !empty(trim($_GET['q']))): ?>
                    <a href="libros.php" class="btn btn-outline-secondary" id="botonLimpiarBusqueda">Limpiar Búsqueda</a>
                <?php endif; ?>
                <!-- Contenedor donde se mostrarán las sugerencias -->
                <div id="listaSugerenciasLibros" class="lista-sugerencias"></div>
            </div>
        </form>

        <!-- Tabla para mostrar el listado de libros -->
        <table class="table table-striped table-hover mt-4"> <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>ISBN</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                    </tr>
            </thead>
            <tbody>
                <?php
                // --- OBTENCIÓN Y LISTADO DE LIBROS (CON FILTRO DE BÚSQUEDA SI APLICA) ---
                // Asegúrate de que $conexion esté disponible para esta consulta.
                $termino_busqueda = ''; // Variable para almacenar el término de búsqueda.
                // Consulta SQL base para obtener libros y su categoría.
                $sql_libros = "SELECT l.id_libro, l.titulo, l.autor, l.isbn, c.nombre AS categoria, l.estado
                               FROM libros l
                               JOIN categorias c ON l.id_categoria = c.id_categoria";
                $params = []; // Array para los parámetros de la consulta preparada.
                $types = '';  // String para los tipos de los parámetros.

                // Si se proporciona un término de búsqueda (parámetro 'q' en GET).
                if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
                    $termino_busqueda = trim($_GET['q']);
                    $sql_libros .= " WHERE l.titulo LIKE ?"; // Añadir condición de búsqueda por título.
                    $params[] = "%" . $termino_busqueda . "%"; // Usar comodines para búsqueda parcial.
                    $types .= "s"; // El tipo del parámetro es string.
                }

                $sql_libros .= " ORDER BY l.titulo"; // Ordenar los resultados por título.

                // Ejecutar la consulta: con prepared statement si hay búsqueda, directa si no.
                if (!empty($params)) {
                    $stmt_libros = $conexion->prepare($sql_libros);
                    $stmt_libros->bind_param($types, ...$params);
                    $stmt_libros->execute();
                    $libros_result = $stmt_libros->get_result();
                } else {
                    $libros_result = $conexion->query($sql_libros);
                }
 
                // Verificar si la consulta fue exitosa y si hay libros
                if ($libros_result && $libros_result->num_rows > 0) {
                    // Bucle para recorrer los resultados y mostrar cada libro en una fila de la tabla
                    while ($libro = $libros_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($libro['titulo']) . "</td>"; // htmlspecialchars para prevenir XSS
                        echo "<td>" . htmlspecialchars($libro['autor']) . "</td>";
                        echo "<td>" . htmlspecialchars($libro['isbn']) . "</td>";
                        echo "<td>" . htmlspecialchars($libro['categoria']) . "</td>";
                        echo "<td>" . htmlspecialchars($libro['estado']) . "</td>";
                        echo "<td>";
                        // Botón para Editar: Redirige a editar_libro.php con el ID del libro.
                        echo "<a href='editar_libro.php?id=" . $libro['id_libro'] . "' class='btn btn-sm btn-warning me-1'>Editar</a>";
                        // Botón para Eliminar: Llama a esta misma página (libros.php) con action=delete y el ID.
                        // Incluye una confirmación JavaScript antes de proceder.
                        echo "<a href='libros.php?action=delete&id=" . $libro['id_libro'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Estás seguro de que deseas eliminar este libro?\");'>Eliminar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    $libros_result->free(); // Liberar resultados
                    if (isset($stmt_libros) && $stmt_libros instanceof mysqli_stmt) {
                        $stmt_libros->close();
                    }
                } else {
                    // Mostrar un mensaje si no hay libros o no hay resultados de búsqueda.
                    $mensaje_no_libros = "No hay libros registrados aún.";
                    if (!empty($termino_busqueda)) {
                        $mensaje_no_libros = "No se encontraron libros que coincidan con su búsqueda: '" . htmlspecialchars($termino_busqueda) . "'.";
                    }
                    echo "<tr><td colspan='6' class='text-center'>" . $mensaje_no_libros . "</td></tr>";
                }
                // --- FIN OBTENCIÓN Y LISTADO DE LIBROS ---
                ?>
            </tbody>
        </table>
    </div> <div class="text-center mt-3 mb-4">
        <a href="dashboard.php" class="btn btn-secondary">Volver al Panel</a>
    </div>

</div>

<!-- Script JavaScript para la funcionalidad de sugerencias de búsqueda (autocompletado) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias a los elementos del DOM.
    const campoBusqueda = document.getElementById('campoBusquedaLibro');
    const listaSugerencias = document.getElementById('listaSugerenciasLibros');
    const formBusqueda = document.getElementById('formBusquedaLibros');
    let timeoutId = null; // Para gestionar el "debouncing" de las peticiones AJAX.

    if (campoBusqueda) {
        // Escuchar el evento 'input' en el campo de búsqueda.
        campoBusqueda.addEventListener('input', function() {
            const query = this.value;

            // Debouncing: Cancelar la petición AJAX anterior si el usuario sigue escribiendo.
            clearTimeout(timeoutId);

            // Si el campo está vacío o tiene menos de 1 caracter, limpiar sugerencias.
            if (query.length < 1) { // Podría aumentarse a 2 o 3 para mejor rendimiento.
                listaSugerencias.innerHTML = '';
                listaSugerencias.style.display = 'none';
                return;
            }

            // Establecer un temporizador: la petición AJAX se hará 300ms después de que el usuario deje de escribir.
            timeoutId = setTimeout(() => {
                // Realizar la petición AJAX al script 'sugerencias_libros.php'.
                fetch('sugerencias_libros.php?q=' + encodeURIComponent(query))
                    .then(response => {
                        // Verificar si la respuesta de la red es correcta.
                        if (!response.ok) {
                            throw new Error('Network response was not ok ' + response.statusText);
                        }
                        return response.json(); // Convertir la respuesta a JSON.
                    })
                    .then(data => {
                        // Procesar los datos JSON recibidos (las sugerencias).
                        listaSugerencias.innerHTML = ''; // Limpiar sugerencias anteriores
                        if (data.length > 0) {
                            // Crear un elemento div por cada sugerencia y añadirlo a la lista.
                            data.forEach(sugerencia => {
                                const div = document.createElement('div');
                                div.textContent = sugerencia;
                                div.addEventListener('click', function() {
                                    campoBusqueda.value = sugerencia; // Poner la sugerencia en el campo
                                    listaSugerencias.innerHTML = '';
                                    listaSugerencias.style.display = 'none';
                                    formBusqueda.submit(); // Enviar el formulario de búsqueda.
                                });
                                listaSugerencias.appendChild(div);
                            });
                            listaSugerencias.style.display = 'block'; // Mostrar la lista de sugerencias.
                        } else {
                            listaSugerencias.style.display = 'none'; // Ocultar si no hay sugerencias.
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener sugerencias:', error);
                        // En caso de error, limpiar y ocultar la lista de sugerencias.
                        listaSugerencias.innerHTML = '';
                        listaSugerencias.style.display = 'none';
                    });
            }, 300);
        });

        // Ocultar sugerencias si se hace clic fuera
        document.addEventListener('click', function(event) { // Listener global.
            if (!campoBusqueda.contains(event.target) && !listaSugerencias.contains(event.target)) {
                listaSugerencias.style.display = 'none';
            }
        });
    }
});
</script>
<?php
// --- CERRAR LA CONEXIÓN A LA BASE DE DATOS ---
// Es una buena práctica cerrar la conexión al finalizar el script, después de que todo el HTML se haya generado.
if ($conexion) {
    $conexion->close();
}
?>

</body>
</html>