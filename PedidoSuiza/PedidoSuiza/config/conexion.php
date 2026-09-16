<?php
$server = "localhost";
$user = "root";
$pass = "";
$bd = "pedidosuiza";
$conexion = mysqli_connect($server, $user, $pass, $bd);
if (!$conexion) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
    exit;
}
?>
