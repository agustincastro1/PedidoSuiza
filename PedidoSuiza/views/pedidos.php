<?php
session_start();

$imagenesFondo = [
    '../imagenes/almuerzo.jpg',
    '../imagenes/donas.jpg',
    '../imagenes/empanadasFritas.jpg',
    '../imagenes/facturas.jpg',
    '../imagenes/images.jpg',
];

$mosaico = array_merge($imagenesFondo, $imagenesFondo, $imagenesFondo);
shuffle($mosaico);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PedidoSuiza</title>
    <link rel="stylesheet" href="../CSS/estiloInicioPedidoSuiza.css">
</head>

<body>

    <div class="fondo-mosaico-inicio">
        <?php foreach ($mosaico as $imagen): ?>
            <img src="<?= htmlspecialchars($imagen) ?>" alt="" class="foto-mosaico">
        <?php endforeach; ?>
    </div>
    <div class="capa-filtro-inicio"></div>

    <header class="header-inicio">
        <h1>PedidoSuiza</h1>
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
    </header>

    <main class="hero-inicio">
        <div class="hero-contenido">
            <h2>Pedí tu comida sin hacer fila</h2>
            <p>Elegí lo que querés comer y pasá a retirarlo cuando esté listo.</p>
            <a class="btn-hero" href="hacerPedido.php">Hacer pedido</a>
        </div>
    </main>
</body>

</html>
