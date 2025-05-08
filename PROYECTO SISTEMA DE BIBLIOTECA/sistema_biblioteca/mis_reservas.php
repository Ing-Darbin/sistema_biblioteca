<?php
// mis_reservas.php
require 'db/conexion.php';
session_start();

if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo_usuario"] !== "estudiante") {
    header("Location: index.php");
    exit();
}

$id_usuario_logueado = $_SESSION["id_usuario"];
$mensaje = '';

// (Opcional) Lógica para cancelar una reserva si se envía un ID por GET/POST
// if (isset($_GET['accion']) && $_GET['accion'] == 'cancelar' && isset($_GET['id_reserva'])) {
//     $id_reserva_cancelar = $_GET['id_reserva_cancelar'];
//     // Lógica para actualizar estado_reserva a 'cancelada' para $id_reserva_cancelar
//     // Asegúrate de que la reserva pertenezca al usuario logueado
//     $stmt_cancelar = $conexion->prepare("UPDATE reservas SET estado_reserva = 'cancelada' WHERE id_reserva = ? AND id_usuario = ?");
//     $stmt_cancelar->bind_param("ii", $id_reserva_cancelar, $id_usuario_logueado);
//     if ($stmt_cancelar->execute()){
//         $mensaje = "Reserva cancelada correctamente.";
//     } else {
//         $mensaje = "Error al cancelar la reserva.";
//     }
//     $stmt_cancelar->close();
// }


$stmt = $conexion->prepare("
    SELECT r.id_reserva, r.fecha_reserva, r.fecha_limite_recogida, r.estado_reserva, l.titulo AS titulo_libro
    FROM reservas r
    JOIN libros l ON r.id_libro = l.id_libro
    WHERE r.id_usuario = ? AND r.estado_reserva IN ('pendiente', 'lista_para_recoger')
    ORDER BY r.fecha_reserva DESC
");
$stmt->bind_param("i", $id_usuario_logueado);
$stmt->execute();
$reservas_del_estudiante = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('imagenes/fondo_biblioteca.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: #f8f9fa;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Mis Reservas</h1>
        <?php if ($mensaje): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="card p-3 shadow-sm">
            <?php if ($reservas_del_estudiante && $reservas_del_estudiante->num_rows > 0): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Fecha de Reserva</th>
                            <th>Límite para Recoger</th>
                            <th>Estado</th>
                            <!-- <th>Acciones</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reserva = $reservas_del_estudiante->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reserva['titulo_libro']); ?></td>
                                <td><?php echo htmlspecialchars($reserva['fecha_reserva']); ?></td>
                                <td><?php echo $reserva['fecha_limite_recogida'] ? htmlspecialchars($reserva['fecha_limite_recogida']) : 'N/D (Esperando disponibilidad)'; ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $reserva['estado_reserva']))); ?></td>
                                <!-- <td>
                                    <?php // if ($reserva['estado_reserva'] === 'pendiente' || $reserva['estado_reserva'] === 'lista_para_recoger'): ?>
                                        <a href="mis_reservas.php?accion=cancelar&id_reserva=<?php // echo $reserva['id_reserva']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de cancelar esta reserva?');">Cancelar</a>
                                    <?php // endif; ?>
                                </td> -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No tienes reservas activas.</p>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary">Volver al Panel</a>
        </div>
    </div>
    <?php $conexion->close(); ?>
</body>
</html>
