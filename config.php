<?php
// config.php - Configuración de la base de datos para Render

$host       = getenv("DB_HOST") ?: "185.232.14.52";
$usuario    = getenv("DB_USER") ?: "u760464709_23005089_usr";
$password   = getenv("DB_PASS") ?: ":Sa[MX~2l";
$base_datos = getenv("DB_NAME") ?: "u760464709_23005089_bd";

$conn = null;

// Solo intentar conexión si Render ya está sirviendo la app
if (php_sapi_name() !== 'cli') {
    $conn = @new mysqli($host, $usuario, $password, $base_datos);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
}
?>
