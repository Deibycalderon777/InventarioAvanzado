
<?php
// api.php - API REST para el sistema de inventario con AJAX
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';

switch ($accion) {
    case 'obtener_todo':
        obtenerTodo();
        break;
    
    case 'crear':
        crearProducto();
        break;
    
    case 'actualizar':
        actualizarProducto();
        break;
    
    case 'eliminar':
        eliminarProducto();
        break;
    
    case 'exportar':
        exportarInventario();
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

// Obtener todos los datos necesarios
function obtenerTodo() {
    global $conn;
    
    // Estadísticas
    $sql = "SELECT 
        COUNT(*) as total_productos,
        COALESCE(SUM(cantidad * precio_compra), 0) as valor_inventario,
        COALESCE(SUM(cantidad * precio_venta), 0) as ventas_potenciales,
        COALESCE(SUM(cantidad * (precio_venta - precio_compra)), 0) as ganancias_potenciales,
        SUM(CASE WHEN cantidad <= minimo THEN 1 ELSE 0 END) as productos_bajo_stock
        FROM productos";
    $result = $conn->query($sql);
    $estadisticas = $result->fetch_assoc();
    
    // Productos
    $sql = "SELECT * FROM productos ORDER BY nombre";
    $result = $conn->query($sql);
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    // Productos con stock bajo
    $sql = "SELECT * FROM productos WHERE cantidad <= minimo ORDER BY cantidad ASC LIMIT 10";
    $result = $conn->query($sql);
    $productos_bajo_stock = [];
    while ($row = $result->fetch_assoc()) {
        $productos_bajo_stock[] = $row;
    }
    
    // Categorías únicas
    $sql = "SELECT DISTINCT categoria FROM productos ORDER BY categoria";
    $result = $conn->query($sql);
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
    
    echo json_encode([
        'success' => true,
        'estadisticas' => $estadisticas,
        'productos' => $productos,
        'productos_bajo_stock' => $productos_bajo_stock,
        'categorias' => $categorias
    ], JSON_UNESCAPED_UNICODE);
}

// Crear nuevo producto
function crearProducto() {
    global $conn;
    
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $cantidad = intval($_POST['cantidad']);
    $minimo = intval($_POST['minimo']);
    $precio_compra = floatval($_POST['precio_compra']);
    $precio_venta = floatval($_POST['precio_venta']);
    $proveedor = $conn->real_escape_string($_POST['proveedor']);
    $ubicacion = $conn->real_escape_string($_POST['ubicacion']);
    
    // Verificar que el código no exista
    $check = $conn->query("SELECT id FROM productos WHERE codigo = '$codigo'");
    if ($check->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El código ya existe. Por favor usa otro.'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $sql = "INSERT INTO productos (codigo, nombre, categoria, cantidad, minimo, precio_compra, precio_venta, proveedor, ubicacion) 
            VALUES ('$codigo', '$nombre', '$categoria', $cantidad, $minimo, $precio_compra, $precio_venta, '$proveedor', '$ubicacion')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => '✅ Producto agregado exitosamente',
            'id' => $conn->insert_id
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al agregar el producto: ' . $conn->error
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Actualizar producto
function actualizarProducto() {
    global $conn;
    
    $id = intval($_POST['id']);
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $cantidad = intval($_POST['cantidad']);
    $minimo = intval($_POST['minimo']);
    $precio_compra = floatval($_POST['precio_compra']);
    $precio_venta = floatval($_POST['precio_venta']);
    $proveedor = $conn->real_escape_string($_POST['proveedor']);
    $ubicacion = $conn->real_escape_string($_POST['ubicacion']);
    
    // Verificar que el código no exista en otro producto
    $check = $conn->query("SELECT id FROM productos WHERE codigo = '$codigo' AND id != $id");
    if ($check->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El código ya existe en otro producto.'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $sql = "UPDATE productos SET 
            codigo='$codigo', 
            nombre='$nombre', 
            categoria='$categoria', 
            cantidad=$cantidad, 
            minimo=$minimo, 
            precio_compra=$precio_compra, 
            precio_venta=$precio_venta, 
            proveedor='$proveedor', 
            ubicacion='$ubicacion' 
            WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => '✅ Producto actualizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el producto: ' . $conn->error
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Eliminar producto
function eliminarProducto() {
    global $conn;
    
    $id = intval($_POST['id']);
    
    $sql = "DELETE FROM productos WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'success' => true,
            'message' => '✅ Producto eliminado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el producto: ' . $conn->error
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Exportar inventario a CSV
function exportarInventario() {
    global $conn;
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inventario_' . date('Y-m-d_H-i-s') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8 (ayuda con caracteres especiales en Excel)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, [
        'Código', 
        'Nombre', 
        'Categoría', 
        'Cantidad', 
        'Mínimo', 
        'Precio Compra', 
        'Precio Venta', 
        'Valor Total', 
        'Ganancia Unitaria',
        'Proveedor', 
        'Ubicación',
        'Fecha Registro'
    ]);
    
    // Datos
    $result = $conn->query("SELECT * FROM productos ORDER BY nombre");
    while ($row = $result->fetch_assoc()) {
        $valor_total = $row['cantidad'] * $row['precio_compra'];
        $ganancia = $row['precio_venta'] - $row['precio_compra'];
        
        fputcsv($output, [
            $row['codigo'],
            $row['nombre'],
            $row['categoria'],
            $row['cantidad'],
            $row['minimo'],
            number_format($row['precio_compra'], 2),
            number_format($row['precio_venta'], 2),
            number_format($valor_total, 2),
            number_format($ganancia, 2),
            $row['proveedor'],
            $row['ubicacion'],
            $row['fecha_registro']
        ]);
    }
    
    fclose($output);
    exit();
}

$conn->close();
?>
