<?php
/**
 * Fragmento para Listar Libros en una Tabla HTML.
 *
 * Este script está diseñado para ser incluido (`include` o `require`)
 * en una página PHP principal que ya ha establecido una conexión
 * a la base de datos (a través de la variable `$conexion`).
 *
 * Su propósito es generar el código HTML para una tabla que muestra
 * todos los libros registrados en el sistema, junto con su título,
 * autor, ISBN, categoría y estado.
 *
 * Dependencias:
 * - Una variable de conexión a la base de datos mysqli activa llamada `$conexion`.
 * - Las tablas `libros` y `categorias` deben existir en la base de datos
 *   con la estructura esperada.
 *
 * Consideraciones de seguridad:
 * - Se utiliza `htmlspecialchars()` para escapar los datos antes de mostrarlos,
 *   previniendo ataques de Cross-Site Scripting (XSS).
 */

// Nota: Se asume que $conexion (objeto mysqli) ya está disponible
// y que session_start() ya ha sido llamado si fuera necesario por el script padre.
?>
<table class="table mt-4">
    <thead>
        <tr>
            <th>Título</th>
            <th>Autor</th>
            <th>ISBN</th>
            <th>Categoría</th>
            <th>Estado</th>
            <!-- Podrías añadir más columnas si fuera necesario, como "Acciones" -->
        </tr>
    </thead>
    <tbody>
        <?php
        // Consulta para obtener todos los libros y el nombre de su categoría.
        $libros_result = $conexion->query("SELECT l.id_libro, l.titulo, l.autor, l.isbn, c.nombre AS categoria, l.estado
FROM libros l JOIN categorias c ON l.id_categoria = c.id_categoria");

        // Verificar si la consulta fue exitosa y si hay libros para mostrar.
        if ($libros_result && $libros_result->num_rows > 0) {
            // Iterar sobre cada libro y mostrarlo en una fila de la tabla.
            while ($libro = $libros_result->fetch_assoc()) {
            echo "<tr>
 <td>" . htmlspecialchars($libro['titulo']) . "</td>
 <td>" . htmlspecialchars($libro['autor']) . "</td>
 <td>" . htmlspecialchars($libro['isbn']) . "</td>
 <td>" . htmlspecialchars($libro['categoria']) . "</td>
 <td>" . htmlspecialchars($libro['estado']) . "</td>
 </tr>";
            }
            $libros_result->free(); // Liberar el conjunto de resultados.
        } else {
            // Mensaje a mostrar si no hay libros en la base de datos o la consulta falla.
            echo "<tr><td colspan='5' class='text-center'>No hay libros registrados en el sistema.</td></tr>";
        }
        ?>
    </tbody>
</table>
<?php
// Nota: El cierre de la conexión $conexion->close() debería manejarse
// en el script principal que incluye este archivo, después de que ya no se necesite.
?>