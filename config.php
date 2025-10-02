<?php
// config.php - Configuración de la base de datos para Render

// Si no existen las variables de entorno, usa tus valores por defecto
$host       = getenv("DB_HOST") ?: "185.232.14.52";
$usuario    = getenv("DB_USER") ?: "u760464709_23005089_usr";
$password   = getenv("DB_PASS") ?: ":Sa[MX~2l";
$base_datos = getenv("DB_NAME") ?: "u760464709_23005089_bd";

// Crear la conexión
$conn = @new mysqli($host, $usuario, $password, $base_datos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer codificación de caracteres
$conn->set_charset("utf8");
?>
