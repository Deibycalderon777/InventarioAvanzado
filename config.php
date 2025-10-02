<?php
// config.php - Configuración de la base de datos usando variables de entorno

$host     = getenv("DB_HOST") ?: "localhost";
$usuario  = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASS") ?: "";
$base_datos = getenv("DB_NAME") ?: "inventario";

// Intentar conectar
$conn = @new mysqli($host, $usuario, $password, $base_datos);

// Verificar errores SOLO en tiempo de ejecución
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");
?>
