<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Tenés que iniciar sesión para hacer un pedido"]);
    exit;
}

$idUsuario = $_SESSION['id_usuario'];
$datos = json_decode(file_get_contents('php://input'), true);
$items = $datos['productos'] ?? [];

$pedido = [];
foreach ($items as $item) {
    $idProducto = intval($item['id_producto'] ?? 0);
    $cantidad = intval($item['cantidad'] ?? 0);
    if ($idProducto > 0 && $cantidad > 0) {
        $pedido[$idProducto] = $cantidad;
    }
}

if (count($pedido) === 0) {
    echo json_encode(["error" => "Elegí al menos un producto"]);
    exit;
}

mysqli_begin_transaction($conexion);

$consultaStock = mysqli_prepare($conexion, "SELECT nombre_producto, cantidad_producto, precio FROM productos WHERE id_producto = ? FOR UPDATE");
$detalles = [];
$total = 0;

foreach ($pedido as $idProducto => $cantidad) {
    mysqli_stmt_bind_param($consultaStock, "i", $idProducto);
    mysqli_stmt_execute($consultaStock);
    $producto = mysqli_stmt_get_result($consultaStock)->fetch_assoc();

    if (!$producto || $producto['cantidad_producto'] < $cantidad) {
        mysqli_rollback($conexion);
        $nombre = $producto['nombre_producto'] ?? 'un producto';
        echo json_encode(["error" => "No hay stock suficiente de $nombre"]);
        exit;
    }

    $precioTotal = $producto['precio'] * $cantidad;
    $detalles[$idProducto] = [
        'nombre' => $producto['nombre_producto'],
        'precio_total' => $precioTotal,
        'cantidad' => $cantidad,
    ];
    $total += $precioTotal;
}
mysqli_stmt_close($consultaStock);

$insertarReserva = mysqli_prepare($conexion, "INSERT INTO reserva_buffet (id_usuario, horario_reserva, estado_reserva_buffet, total) VALUES (?, NOW(), 'pendiente', ?)");
mysqli_stmt_bind_param($insertarReserva, "id", $idUsuario, $total);
mysqli_stmt_execute($insertarReserva);
$idReserva = mysqli_insert_id($conexion);
mysqli_stmt_close($insertarReserva);

$insertarDetalle = mysqli_prepare($conexion, "INSERT INTO productos_reservados (id_reserva_buffet, id_producto, nombre_producto, precio_total, cantidad) VALUES (?, ?, ?, ?, ?)");
$descontarStock = mysqli_prepare($conexion, "UPDATE productos SET cantidad_producto = cantidad_producto - ? WHERE id_producto = ?");

foreach ($detalles as $idProducto => $detalle) {
    mysqli_stmt_bind_param($insertarDetalle, "iisdi", $idReserva, $idProducto, $detalle['nombre'], $detalle['precio_total'], $detalle['cantidad']);
    mysqli_stmt_execute($insertarDetalle);

    mysqli_stmt_bind_param($descontarStock, "ii", $detalle['cantidad'], $idProducto);
    mysqli_stmt_execute($descontarStock);
}
mysqli_stmt_close($insertarDetalle);
mysqli_stmt_close($descontarStock);

mysqli_commit($conexion);

echo json_encode(["exito" => "Pedido realizado correctamente"]);
mysqli_close($conexion);
