<?php
// config.php - Configuración de la base de datos
$host = '185.232.14.52';
$usuario = 'u760464709_23005089_usr';
$password = ':Sa[MX~2l';
$base_datos = 'u760464709_23005089_bd';

$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
