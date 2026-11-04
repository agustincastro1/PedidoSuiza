<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

if (empty($_SESSION['es_profesor'])) {
    echo json_encode(["error" => "Solo los profesores pueden reservar computadoras"]);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$datos = json_decode(file_get_contents('php://input'), true);
$cantidad = intval($datos['cantidad'] ?? 0);
$aula = trim($datos['aula'] ?? '');
$horario = trim($datos['horario'] ?? '');
$fechaHora = DateTime::createFromFormat('Y-m-d\TH:i', $horario);

if ($cantidad <= 0) {
    echo json_encode(["error" => "Elegí cuántas computadoras querés reservar"]);
    exit;
}

if ($aula === '' || mb_strlen($aula) > 50) {
    echo json_encode(["error" => "Escribí el aula (máximo 50 caracteres)"]);
    exit;
}

if (!$fechaHora || $fechaHora->format('Y-m-d\TH:i') !== $horario) {
    echo json_encode(["error" => "Elegí una fecha y hora válidas"]);
    exit;
}
$horarioReserva = $fechaHora->format('Y-m-d H:i:00');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    mysqli_begin_transaction($conexion);

    $consultaProfesor = mysqli_prepare($conexion, "SELECT id_profesor FROM profesor WHERE id_usuario = ?");
    mysqli_stmt_bind_param($consultaProfesor, "i", $idUsuario);
    mysqli_stmt_execute($consultaProfesor);
    $profesor = mysqli_stmt_get_result($consultaProfesor)->fetch_assoc();
    mysqli_stmt_close($consultaProfesor);

    if (!$profesor) {
        mysqli_rollback($conexion);
        echo json_encode(["error" => "Solo los profesores pueden reservar computadoras"]);
        exit;
    }
    $idProfesor = $profesor['id_profesor'];

    // Se compara en MySQL porque PHP puede tener otra zona horaria configurada.
    $consultaHorario = mysqli_prepare($conexion, "SELECT CAST(? AS DATETIME) < NOW() - INTERVAL 1 MINUTE");
    mysqli_stmt_bind_param($consultaHorario, "s", $horarioReserva);
    mysqli_stmt_execute($consultaHorario);
    $horarioPasado = (bool) mysqli_stmt_get_result($consultaHorario)->fetch_row()[0];
    mysqli_stmt_close($consultaHorario);

    if ($horarioPasado) {
        mysqli_rollback($conexion);
        echo json_encode(["error" => "La fecha y hora no pueden ser anteriores a ahora"]);
        exit;
    }

    $resultadoStock = mysqli_query($conexion, "SELECT id_computadora, computadoras_disp FROM computadoras ORDER BY id_computadora LIMIT 1 FOR UPDATE");
    $computadoras = mysqli_fetch_assoc($resultadoStock);

    if (!$computadoras || $computadoras['computadoras_disp'] < $cantidad) {
        mysqli_rollback($conexion);
        echo json_encode(["error" => "No hay suficientes computadoras disponibles"]);
        exit;
    }
    $idComputadora = $computadoras['id_computadora'];

    $insertarReserva = mysqli_prepare($conexion, "INSERT INTO reserva_computadoras (id_profesor, cant_reservada, aula, horario_reserva, estado_reserva_computadoras) VALUES (?, ?, ?, ?, 'activa')");
    mysqli_stmt_bind_param($insertarReserva, "iiss", $idProfesor, $cantidad, $aula, $horarioReserva);
    mysqli_stmt_execute($insertarReserva);
    $idReserva = mysqli_insert_id($conexion);
    mysqli_stmt_close($insertarReserva);

    $insertarDetalle = mysqli_prepare($conexion, "INSERT INTO detalle_reserva_comp (id_reserva_comp, id_computadora) VALUES (?, ?)");
    mysqli_stmt_bind_param($insertarDetalle, "ii", $idReserva, $idComputadora);
    mysqli_stmt_execute($insertarDetalle);
    mysqli_stmt_close($insertarDetalle);

    $descontarStock = mysqli_prepare($conexion, "UPDATE computadoras SET computadoras_disp = computadoras_disp - ? WHERE id_computadora = ?");
    mysqli_stmt_bind_param($descontarStock, "ii", $cantidad, $idComputadora);
    mysqli_stmt_execute($descontarStock);
    mysqli_stmt_close($descontarStock);

    mysqli_commit($conexion);
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    error_log($e->getMessage());
    echo json_encode(["error" => "No se pudo realizar la reserva, probá de nuevo"]);
    exit;
}

echo json_encode(["exito" => "Reserva realizada correctamente"]);
mysqli_close($conexion);
