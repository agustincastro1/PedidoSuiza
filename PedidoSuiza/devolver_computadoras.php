<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

if (empty($_SESSION['es_profesor'])) {
    echo json_encode(["error" => "Solo los profesores pueden devolver computadoras"]);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$datos = json_decode(file_get_contents('php://input'), true);
$idReserva = intval($datos['id_reserva'] ?? 0);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    mysqli_begin_transaction($conexion);

    // Solo encuentra la reserva si pertenece al profesor que inició sesión.
    $consultaReserva = mysqli_prepare($conexion, "SELECT r.cant_reservada, r.estado_reserva_computadoras, d.id_computadora FROM reserva_computadoras r JOIN profesor p ON p.id_profesor = r.id_profesor JOIN detalle_reserva_comp d ON d.id_reserva_comp = r.id_reserva_comp WHERE r.id_reserva_comp = ? AND p.id_usuario = ? FOR UPDATE");
    mysqli_stmt_bind_param($consultaReserva, "ii", $idReserva, $idUsuario);
    mysqli_stmt_execute($consultaReserva);
    $reserva = mysqli_stmt_get_result($consultaReserva)->fetch_assoc();
    mysqli_stmt_close($consultaReserva);

    if (!$reserva || $reserva['estado_reserva_computadoras'] !== 'activa') {
        mysqli_rollback($conexion);
        echo json_encode(["error" => "Esa reserva no se puede devolver"]);
        exit;
    }

    $actualizarReserva = mysqli_prepare($conexion, "UPDATE reserva_computadoras SET estado_reserva_computadoras = 'devuelta' WHERE id_reserva_comp = ?");
    mysqli_stmt_bind_param($actualizarReserva, "i", $idReserva);
    mysqli_stmt_execute($actualizarReserva);
    mysqli_stmt_close($actualizarReserva);

    $sumarStock = mysqli_prepare($conexion, "UPDATE computadoras SET computadoras_disp = computadoras_disp + ? WHERE id_computadora = ?");
    mysqli_stmt_bind_param($sumarStock, "ii", $reserva['cant_reservada'], $reserva['id_computadora']);
    mysqli_stmt_execute($sumarStock);
    mysqli_stmt_close($sumarStock);

    mysqli_commit($conexion);
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    error_log($e->getMessage());
    echo json_encode(["error" => "No se pudo devolver la reserva, probá de nuevo"]);
    exit;
}

echo json_encode(["exito" => "Computadoras devueltas correctamente"]);
mysqli_close($conexion);
