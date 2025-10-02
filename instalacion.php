<?php
// instalacion.php - Script para crear la base de datos y tablas
require_once 'config.php';

$sql = "CREATE DATABASE IF NOT EXISTS inventario_abarrotes";
$conn->query($sql);

$conn->select_db('inventario_abarrotes');

$sql = "CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    minimo INT NOT NULL DEFAULT 0,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    proveedor VARCHAR(200),
    ubicacion VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'productos' creada correctamente<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Insertar datos de ejemplo
$productos_ejemplo = [
    ['AB001', 'Arroz 1kg', 'Granos', 45, 20, 18.50, 25.00, 'Distribuidora del Norte', 'A-1'],
    ['AB002', 'Frijol Negro 1kg', 'Granos', 15, 25, 22.00, 30.00, 'Distribuidora del Norte', 'A-2'],
    ['AB003', 'Aceite Vegetal 1L', 'Aceites', 32, 15, 35.00, 48.00, 'Aceites SA', 'B-1'],
    ['AB004', 'Azúcar 1kg', 'Endulzantes', 8, 20, 20.00, 28.00, 'Azucarera MX', 'A-3'],
    ['AB005', 'Sal de Mesa 1kg', 'Condimentos', 50, 10, 8.00, 12.00, 'Sal del Mar', 'C-1'],
    ['AB006', 'Pasta 500g', 'Pastas', 28, 15, 12.00, 18.00, 'Pastas Italianas', 'A-4'],
    ['AB007', 'Atún en Lata 140g', 'Enlatados', 60, 30, 15.00, 22.00, 'Pesquera del Pacífico', 'D-1'],
    ['AB008', 'Leche Entera 1L', 'Lácteos', 12, 20, 22.00, 30.00, 'Lácteos Unidos', 'E-1']
];

foreach ($productos_ejemplo as $p) {
    $sql = "INSERT IGNORE INTO productos (codigo, nombre, categoria, cantidad, minimo, precio_compra, precio_venta, proveedor, ubicacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiiddss", $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8]);
    $stmt->execute();
}

echo "✅ Datos de ejemplo insertados<br>";
echo "<br><a href='index.php'>Ir al sistema de inventario</a>";

$conn->close();
?>

