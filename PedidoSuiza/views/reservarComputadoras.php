<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$esProfesor = !empty($_SESSION['es_profesor']);
$disponibles = 0;
$reservas = [];

if ($esProfesor) {
    $resultadoStock = mysqli_query($conexion, "SELECT computadoras_disp FROM computadoras ORDER BY id_computadora LIMIT 1");
    $disponibles = (int) (mysqli_fetch_assoc($resultadoStock)['computadoras_disp'] ?? 0);

    $consultaReservas = mysqli_prepare($conexion, "SELECT r.id_reserva_comp, r.cant_reservada, r.aula, r.horario_reserva, r.estado_reserva_computadoras FROM reserva_computadoras r JOIN profesor p ON p.id_profesor = r.id_profesor WHERE p.id_usuario = ? ORDER BY r.horario_reserva DESC, r.id_reserva_comp DESC");
    mysqli_stmt_bind_param($consultaReservas, "i", $_SESSION['id_usuario']);
    mysqli_stmt_execute($consultaReservas);
    $resultadoReservas = mysqli_stmt_get_result($consultaReservas);
    while ($fila = mysqli_fetch_assoc($resultadoReservas)) {
        $reservas[] = $fila;
    }
    mysqli_stmt_close($consultaReservas);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar computadoras - PedidoSuiza</title>
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
    <?php if (!$esProfesor): ?>
        <main>
            <div class="contenido">
                <h1 class="titulo-seccion">COMPUTADORAS</h1>
                <p class="mensaje-error">Esta sección es solo para profesores.</p>
            </div>
        </main>
    <?php else: ?>
        <main class="pagina-computadoras">
            <section class="reserva-principal">
                <h1 class="titulo-seccion">COMPUTADORAS</h1>
                <p class="disponibles"><?= $disponibles > 0 ? "Quedan $disponibles disponibles" : "Sin stock" ?></p>

                <form id="formComputadoras">
                    <label for="cantidadComputadoras">Cantidad</label>
                    <input
                        type="number"
                        id="cantidadComputadoras"
                        class="cantidad-producto"
                        min="1"
                        max="<?= $disponibles ?>"
                        value="1"
                        <?= $disponibles === 0 ? 'disabled' : '' ?>>

                    <label for="aulaComputadoras">Aula</label>
                    <input
                        type="text"
                        id="aulaComputadoras"
                        class="cantidad-producto"
                        maxlength="50"
                        placeholder="Ej: 5B o Laboratorio"
                        <?= $disponibles === 0 ? 'disabled' : '' ?>>

                    <label for="horarioComputadoras">Fecha y hora</label>
                    <input
                        type="datetime-local"
                        id="horarioComputadoras"
                        class="cantidad-producto"
                        <?= $disponibles === 0 ? 'disabled' : '' ?>>

                    <p id="mensajeComputadoras" class="mensaje-error"></p>
                    <button type="submit" class="btn-pedido" <?= $disponibles === 0 ? 'disabled' : '' ?>>Reservar</button>
                </form>
            </section>

            <aside class="panel-reservas">
                <h2>Mis reservas</h2>

                <?php if (count($reservas) === 0): ?>
                    <p class="sin-reservas">Todavía no hiciste ninguna reserva.</p>
                <?php endif; ?>

                <?php foreach ($reservas as $reserva): ?>
                    <div class="item-reserva">
                        <h3><?= (int) $reserva['cant_reservada'] ?> <?= (int) $reserva['cant_reservada'] === 1 ? 'computadora' : 'computadoras' ?></h3>
                        <p>Aula: <?= htmlspecialchars($reserva['aula']) ?></p>
                        <p><?= htmlspecialchars(date('d/m/Y H:i', strtotime($reserva['horario_reserva']))) ?></p>
                        <?php if ($reserva['estado_reserva_computadoras'] === 'activa'): ?>
                            <button type="button" class="btn-cerrar-sesion btn-devolver" data-id-reserva="<?= (int) $reserva['id_reserva_comp'] ?>">Devolver</button>
                        <?php else: ?>
                            <p class="reserva-devuelta">Devuelta</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </aside>
        </main>
    <?php endif; ?>

    <script src="../JS/computadoras.js"></script>
</body>

</html>
