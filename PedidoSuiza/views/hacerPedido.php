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

$productos = [];
$resultadoProductos = mysqli_query($conexion, "SELECT id_producto, nombre_producto, cantidad_producto, precio FROM productos ORDER BY id_producto");
while ($fila = mysqli_fetch_assoc($resultadoProductos)) {
    $productos[] = $fila;
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
    <main>
        <div class="contenido">
            <!-- El título ahora vive adentro del contenedor para alinearse perfecto -->
            <h1 class="titulo-seccion">COMIDA</h1>

            <?php if (!isset($_SESSION['id_usuario'])): ?>
                <p class="mensaje-error">Iniciá sesión para poder hacer un pedido.</p>
            <?php endif; ?>

            <form id="formPedido">
                <?php foreach ($productos as $producto): ?>
                    <?php
                        $nombre = $producto['nombre_producto'];
                        $stock = (int) $producto['cantidad_producto'];
                        $imagen = $imagenesProducto[$nombre] ?? '../imagenes/images.jpg';
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
    </main>

    <script src="../JS/pedido.js"></script>
</body>

</html>
