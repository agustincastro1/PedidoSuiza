<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

$usuario = trim($datos['usuario'] ?? '');
$password = trim($datos['password'] ?? '');

if ($usuario === '' || $password === '') {
    echo json_encode(["ok" => false, "mensaje" => "Faltan datos."]);
    exit;
}

$consulta = mysqli_prepare($conexion, "SELECT id_usuario, nombre_usuario, contrasenia, es_profesor FROM usuario WHERE nombre_usuario = ? LIMIT 1");
mysqli_stmt_bind_param($consulta, "s", $usuario);
mysqli_stmt_execute($consulta);
$resultado = mysqli_stmt_get_result($consulta);

if (mysqli_num_rows($resultado) === 0) {
    echo json_encode(["ok" => false, "mensaje" => "Usuario o contraseña incorrectos."]);
    exit;
}

$fila = mysqli_fetch_assoc($resultado);

// NOTA DE SEGURIDAD: acá comparamos texto plano.
// Lo ideal es guardar las contraseñas con password_hash() y comparar con password_verify().
if ($fila['contrasenia'] !== $password) {
    echo json_encode(["ok" => false, "mensaje" => "Usuario o contraseña incorrectos."]);
    exit;
}

echo json_encode([
    "ok" => true,
    "usuario" => $fila['nombre_usuario'],
    "esProfesor" => (bool)$fila['es_profesor']
]);

mysqli_close($conexion);