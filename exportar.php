<?php
// exportar.php - Exportar a Excel/CSV
require_once 'config.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventario_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, ['Código', 'Nombre', 'Categoría', 'Cantidad', 'Mínimo', 'Precio Compra', 'Precio Venta', 'Valor Total', 'Proveedor', 'Ubicación']);

// Datos
$result = $conn->query("SELECT * FROM productos ORDER BY nombre");
while ($row = $result->fetch_assoc()) {
    $valor_total = $row['cantidad'] * $row['precio_compra'];
    fputcsv($output, [
        $row['codigo'],
        $row['nombre'],
        $row['categoria'],
        $row['cantidad'],
        $row['minimo'],
        $row['precio_compra'],
        $row['precio_venta'],
        $valor_total,
        $row['proveedor'],
        $row['ubicacion']
    ]);
}

fclose($output);
$conn->close();
exit();