<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - PedidoSuiza</title>
    <link rel="stylesheet" href="../CSS/estiloRegistroPedidoSuiza.css">
</head>

<body>
    <div class="fondo-mosaico">
        <img src="../imagenes/almuerzo.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/images.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/facturas.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/donas.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/empanadasFritas.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/images.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/donas.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/facturas.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/empanadasFritas.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/images.jpg" alt="Comida" class="foto-bento">
        <img src="../imagenes/facturas.jpg" alt="Comida" class="foto-bento">
    </div>

    <div class="capa-filtro"></div>

    <main class="contenedor-registro">
        <div class="registroPrincipal">
            <h1>CREAR CUENTA</h1>
            <form id="formRegistro">
                <div class="grupo-input">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" placeholder="Elegí un usuario">
                </div>
                <div class="grupo-input">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" placeholder="Ingresá tu contraseña">
                </div>
                <div class="grupo-input">
                    <label for="confirmarPassword">Confirmar contraseña</label>
                    <input type="password" id="confirmarPassword" placeholder="Repetí tu contraseña">
                </div>
                <div class="grupo-turnos">
                    <span>Turno (podés elegir más de uno)</span>
                    <div class="opciones-turno">
                        <label class="opcion-turno"><input type="checkbox" name="turno" value="1"> Mañana</label>
                        <label class="opcion-turno"><input type="checkbox" name="turno" value="2"> Tarde</label>
                        <label class="opcion-turno"><input type="checkbox" name="turno" value="3"> Noche</label>
                    </div>
                </div>
                <p id="mensajeRegistro" class="mensaje-error"></p>
                <button type="submit" class="btn-registrar">Registrarme</button>
            </form>
            <span class="enlace-secundario">¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></span>
        </div>
    </main>

    <script src="../JS/register.js"></script>
</body>

</html>
