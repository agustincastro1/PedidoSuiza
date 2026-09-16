<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

$nombreUsuario = trim($datos['nombre_usuario'] ?? '');
$contrasena = trim($datos['contrasena'] ?? '');

if ($nombreUsuario === '' || $contrasena === '') {
    echo json_encode(["error" => "Completá todos los campos"]);
    exit;
}

$consulta = mysqli_prepare($conexion, "SELECT id_usuario, nombre_usuario, contrasenia FROM usuario WHERE nombre_usuario = ?");
mysqli_stmt_bind_param($consulta, "s", $nombreUsuario);
mysqli_stmt_execute($consulta);
$resultado = mysqli_stmt_get_result($consulta);
$usuario = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($consulta);

if (!$usuario || $usuario['contrasenia'] !== $contrasena) {
    echo json_encode(["error" => "Usuario o contraseña incorrectos"]);
    exit;
}

$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

echo json_encode(["exito" => "Inicio de sesión correcto"]);
mysqli_close($conexion);
