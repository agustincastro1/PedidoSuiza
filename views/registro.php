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
                <div id="pasoDatos">
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
                    <div class="grupo-input">
                        <label for="codigoDocente">Código docente (solo profesores)</label>
                        <input type="password" id="codigoDocente" placeholder="Si sos alumno, dejalo vacío" autocomplete="off">
                    </div>
                    <div class="grupo-turnos">
                        <span>Turno (podés elegir más de uno)</span>
                        <div class="opciones-turno">
                            <label class="opcion-turno"><input type="checkbox" name="turno" value="1"> Mañana</label>
                            <label class="opcion-turno"><input type="checkbox" name="turno" value="2"> Tarde</label>
                            <label class="opcion-turno"><input type="checkbox" name="turno" value="3"> Noche</label>
                        </div>
                    </div>
                </div>
                <div id="pasoAlumno" hidden>
                    <div class="grupo-input">
                        <label for="anio">Año</label>
                        <select id="anio">
                            <option value="">Elegí tu año</option>
                            <option value="1">1°</option>
                            <option value="2">2°</option>
                            <option value="3">3°</option>
                            <option value="4">4°</option>
                            <option value="5">5°</option>
                            <option value="6">6°</option>
                        </select>
                    </div>
                    <div class="grupo-input">
                        <label for="division">División</label>
                        <input type="text" id="division" placeholder="Ingresá tu división" maxlength="10" autocomplete="off">
                    </div>
                </div>
                <p id="mensajeRegistro" class="mensaje-error"></p>
                <button type="submit" id="btnRegistrar" class="btn-registrar">Continuar</button>
                <button type="button" id="btnVolver" class="btn-volver" hidden>Volver</button>
            </form>
            <span class="enlace-secundario">¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a></span>
        </div>
    </main>

    <script src="../JS/register.js"></script>
</body>

</html>
