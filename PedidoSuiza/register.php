<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

$nombreUsuario = trim($datos['nombre_usuario'] ?? '');
$contrasena = trim($datos['contrasena'] ?? '');
$confirmarContrasena = trim($datos['confirmar_contrasena'] ?? '');
$turnos = array_unique(array_map('intval', $datos['turnos'] ?? []));

if ($nombreUsuario === '' || $contrasena === '' || $confirmarContrasena === '') {
    echo json_encode(["error" => "Completá todos los campos"]);
    exit;
}

if ($contrasena !== $confirmarContrasena) {
    echo json_encode(["error" => "Las contraseñas no coinciden"]);
    exit;
}

if (count($turnos) === 0) {
    echo json_encode(["error" => "Elegí al menos un turno"]);
    exit;
}

$consultaExiste = mysqli_prepare($conexion, "SELECT id_usuario FROM usuario WHERE nombre_usuario = ?");
mysqli_stmt_bind_param($consultaExiste, "s", $nombreUsuario);
mysqli_stmt_execute($consultaExiste);
mysqli_stmt_store_result($consultaExiste);

if (mysqli_stmt_num_rows($consultaExiste) > 0) {
    echo json_encode(["error" => "Ese usuario ya existe"]);
    mysqli_stmt_close($consultaExiste);
    exit;
}
mysqli_stmt_close($consultaExiste);

$insertar = mysqli_prepare($conexion, "INSERT INTO usuario (nombre_usuario, contrasenia, es_profesor) VALUES (?, ?, 0)");
mysqli_stmt_bind_param($insertar, "ss", $nombreUsuario, $contrasena);

if (!mysqli_stmt_execute($insertar)) {
    echo json_encode(["error" => "No se pudo registrar el usuario"]);
    mysqli_stmt_close($insertar);
    exit;
}

$idUsuario = mysqli_insert_id($conexion);
mysqli_stmt_close($insertar);

$insertarTurno = mysqli_prepare($conexion, "INSERT INTO usuario_turnos (id_usuario, id_turno) VALUES (?, ?)");
foreach ($turnos as $idTurno) {
    mysqli_stmt_bind_param($insertarTurno, "ii", $idUsuario, $idTurno);
    mysqli_stmt_execute($insertarTurno);
}
mysqli_stmt_close($insertarTurno);

echo json_encode(["exito" => "Usuario registrado correctamente"]);
mysqli_close($conexion);
