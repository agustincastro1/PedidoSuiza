<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

$nombreUsuario = trim($datos['nombre_usuario'] ?? '');
$contrasena = trim($datos['contrasena'] ?? '');
$confirmarContrasena = trim($datos['confirmar_contrasena'] ?? '');
$turnos = array_unique(array_map('intval', $datos['turnos'] ?? []));
$codigoDocente = trim($datos['codigo_docente'] ?? '');
$esProfesor = $codigoDocente !== '' ? 1 : 0;
$anio = intval($datos['anio'] ?? 0);
$division = trim($datos['division'] ?? '');

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

if (!$esProfesor && ($anio < 1 || $anio > 6 || !preg_match('/^[A-Za-z0-9]{1,10}$/', $division))) {
    echo json_encode(["error" => "Ingresá un año y una división válidos"]);
    exit;
}

$consultaCodigo = mysqli_prepare($conexion, "SELECT id_codigo FROM codigo_docente WHERE codigo = ?");
mysqli_stmt_bind_param($consultaCodigo, "s", $codigoDocente);
mysqli_stmt_execute($consultaCodigo);
mysqli_stmt_store_result($consultaCodigo);
$codigoValido = mysqli_stmt_num_rows($consultaCodigo) > 0;
mysqli_stmt_close($consultaCodigo);

if ($esProfesor && !$codigoValido) {
    echo json_encode(["error" => "El código docente no es válido"]);
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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    mysqli_begin_transaction($conexion);

    $insertar = mysqli_prepare($conexion, "INSERT INTO usuario (nombre_usuario, contrasenia, es_profesor) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($insertar, "ssi", $nombreUsuario, $contrasena, $esProfesor);
    mysqli_stmt_execute($insertar);
    $idUsuario = mysqli_insert_id($conexion);
    mysqli_stmt_close($insertar);

    if ($esProfesor) {
        $insertarRol = mysqli_prepare($conexion, "INSERT INTO profesor (id_usuario) VALUES (?)");
        mysqli_stmt_bind_param($insertarRol, "i", $idUsuario);
    } else {
        $division = strtoupper($division);
        $insertarRol = mysqli_prepare($conexion, "INSERT INTO alumno (id_usuario, anio, division) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insertarRol, "iis", $idUsuario, $anio, $division);
    }
    mysqli_stmt_execute($insertarRol);
    mysqli_stmt_close($insertarRol);

    $insertarTurno = mysqli_prepare($conexion, "INSERT INTO usuario_turnos (id_usuario, id_turno) VALUES (?, ?)");
    foreach ($turnos as $idTurno) {
        mysqli_stmt_bind_param($insertarTurno, "ii", $idUsuario, $idTurno);
        mysqli_stmt_execute($insertarTurno);
    }
    mysqli_stmt_close($insertarTurno);

    mysqli_commit($conexion);
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexion);
    error_log($e->getMessage());
    echo json_encode(["error" => "No se pudo registrar el usuario"]);
    exit;
}

$_SESSION['id_usuario'] = $idUsuario;
$_SESSION['nombre_usuario'] = $nombreUsuario;
$_SESSION['es_profesor'] = (bool)$esProfesor;

echo json_encode(["exito" => "Usuario registrado correctamente"]);
mysqli_close($conexion);
