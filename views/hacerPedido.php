<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$imagenesProducto = [
    'Churros Caseros' => '../imagenes/images.jpg',
    'Donas' => '../imagenes/donas.jpg',
    'Facturas' => '../imagenes/facturas.jpg',
    'Empanadas Fritas' => '../imagenes/empanadasFritas.jpg',
    'Milanesa con arroz' => '../imagenes/almuerzo.jpg',
];

$productosNuevos = [
    ['Agua', 20, 500, 'bebida', 'agua.jpg'],
    ['Alfajor', 20, 1000, 'dulce', 'alfajor.jpg'],
    ['Chocotorta', 20, 2500, 'dulce', 'chocotorta.jpg'],
    ['Coca-Cola', 20, 1500, 'bebida', 'cocacola.jpg'],
    ['Fideos', 20, 4500, 'almuerzo', 'fideos.jpg'],
    ['Hamburguesa', 20, 5000, 'salado', 'hamburguesa.jpg'],
    ['Milanesa napolitana', 20, 7000, 'almuerzo', 'milanesaNapo.jpg'],
    ['Pepsi', 20, 1500, 'bebida', 'pepsi.jpg'],
    ['Pizza', 20, 5000, 'salado', 'pizza.jpg'],
    ['Tacos', 20, 4500, 'salado', 'tacos.jpg'],
];

$nombresProductos = [];
$resultadoNombres = mysqli_query($conexion, "SELECT nombre_producto FROM productos");
while ($fila = mysqli_fetch_assoc($resultadoNombres)) {
    $nombresProductos[$fila['nombre_producto']] = true;
}

$insertarProducto = mysqli_prepare(
    $conexion,
    "INSERT INTO productos (cantidad_producto, precio, nombre_producto, tipo_producto) VALUES (?, ?, ?, ?)"
);
foreach ($productosNuevos as [$nombre, $stock, $precio, $tipo, $archivoImagen]) {
    $imagenesProducto[$nombre] = '../imagenes/' . $archivoImagen;
    if (!isset($nombresProductos[$nombre])) {
        mysqli_stmt_bind_param($insertarProducto, "idss", $stock, $precio, $nombre, $tipo);
        mysqli_stmt_execute($insertarProducto);
        $nombresProductos[$nombre] = true;
    }
}
mysqli_stmt_close($insertarProducto);

$productos = [];
$resultadoProductos = mysqli_query($conexion, "SELECT id_producto, nombre_producto, cantidad_producto, precio FROM productos ORDER BY id_producto");
while ($fila = mysqli_fetch_assoc($resultadoProductos)) {
    $productos[] = $fila;
}

$facturas = [];
if (isset($_SESSION['id_usuario'])) {
    $consultaFacturas = mysqli_prepare($conexion, "SELECT r.id_reserva_buffet, r.horario_reserva, r.total, p.nombre_producto, p.cantidad, p.precio_total FROM reserva_buffet r JOIN productos_reservados p ON p.id_reserva_buffet = r.id_reserva_buffet WHERE r.id_usuario = ? ORDER BY r.id_reserva_buffet DESC");
    mysqli_stmt_bind_param($consultaFacturas, "i", $_SESSION['id_usuario']);
    mysqli_stmt_execute($consultaFacturas);
    $resultadoFacturas = mysqli_stmt_get_result($consultaFacturas);
    while ($fila = mysqli_fetch_assoc($resultadoFacturas)) {
        $idFactura = $fila['id_reserva_buffet'];
        $facturas[$idFactura]['id'] = $idFactura;
        $facturas[$idFactura]['horario'] = $fila['horario_reserva'];
        $facturas[$idFactura]['total'] = $fila['total'];
        $facturas[$idFactura]['items'][] = $fila;
    }
    mysqli_stmt_close($consultaFacturas);
}

function formatearPrecio($valor)
{
    return '$' . number_format((float) $valor, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hacer pedido - PedidoSuiza</title>
    <link rel="stylesheet" href="../CSS/estiloPedidosSuiza.css">
</head>

<body>

    <header>
        <div class="header">
            <h1><a class="volver-inicio" href="pedidos.php">PedidoSuiza</a></h1>
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <div class="usuario-sesion">
                    <span><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></span>
                    <form action="../logout.php" method="post">
                        <button type="submit" class="btn-cerrar-sesion">Cerrar sesión</button>
                    </form>
                </div>
            <?php else: ?>
                <a class="login" href="login.php">iniciar sesion</a>
            <?php endif; ?>
        </div>
    </header>
    <main class="<?= isset($_SESSION['id_usuario']) ? 'pagina-pedido' : '' ?>">
        <div class="contenido">
            <!-- El título ahora vive adentro del contenedor para alinearse perfecto -->
            <h1 class="titulo-seccion">Buffet</h1>

            <?php if (!isset($_SESSION['id_usuario'])): ?>
                <p class="mensaje-error">Iniciá sesión para poder hacer un pedido.</p>
            <?php endif; ?>

            <form id="formPedido">
                <?php foreach ($productos as $producto): ?>
                    <?php
                        $nombre = $producto['nombre_producto'];
                        if (!isset($imagenesProducto[$nombre])) {
                            continue;
                        }
                        $stock = (int) $producto['cantidad_producto'];
                        $imagen = $imagenesProducto[$nombre];
                    ?>
                    <div class="tarjeta-producto">
                        <img src="<?= htmlspecialchars($imagen) ?>" alt="<?= htmlspecialchars($nombre) ?>" class="comida">
                        <div class="info-producto">
                            <h3><?= htmlspecialchars($nombre) ?></h3>
                            <p class="precio">$<?= number_format((float) $producto['precio'], 0, ',', '.') ?></p>
                            <p class="stock"><?= $stock > 0 ? "Quedan $stock" : "Sin stock" ?></p>
                            <?php if (isset($_SESSION['id_usuario'])): ?>
                                <input
                                    type="number"
                                    class="cantidad-producto"
                                    data-id-producto="<?= (int) $producto['id_producto'] ?>"
                                    data-nombre="<?= htmlspecialchars($nombre) ?>"
                                    data-precio="<?= (float) $producto['precio'] ?>"
                                    min="0"
                                    max="<?= $stock ?>"
                                    value="0"
                                    <?= $stock === 0 ? 'disabled' : '' ?>>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <p id="mensajePedido" class="mensaje-error"></p>
                    <button type="submit" class="btn-pedido">Hacer pedido</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if (isset($_SESSION['id_usuario'])): ?>
            <aside class="panel-reservas">
                <div id="pedidoActual" class="factura-bloque factura-en-curso" hidden>
                    <h2>Tu pedido</h2>
                    <p class="factura-dato">Todavía no está confirmado</p>
                    <div id="itemsPedidoActual"></div>
                    <div class="factura-total">
                        <span>Total</span>
                        <span id="totalPedidoActual"></span>
                    </div>
                </div>

                <h2>Mis facturas</h2>
                <?php if (count($facturas) === 0): ?>
                    <p class="sin-reservas">Todavía no hiciste ningún pedido. Elegí productos para ver el detalle acá.</p>
                <?php endif; ?>

                <?php foreach ($facturas as $factura): ?>
                    <div class="factura-bloque">
                        <p class="factura-numero">Pedido N° <?= (int) $factura['id'] ?></p>
                        <p class="factura-dato"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($factura['horario']))) ?></p>
                        <?php foreach ($factura['items'] as $item): ?>
                            <div class="factura-item">
                                <div>
                                    <h3><?= htmlspecialchars($item['nombre_producto']) ?></h3>
                                    <p><?= (int) $item['cantidad'] ?> × <?= formatearPrecio($item['precio_total'] / $item['cantidad']) ?></p>
                                </div>
                                <span class="factura-subtotal"><?= formatearPrecio($item['precio_total']) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="factura-total">
                            <span>Total</span>
                            <span><?= formatearPrecio($factura['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </aside>
        <?php endif; ?>
    </main>

    <script src="../JS/pedido.js"></script>
</body>

</html>
