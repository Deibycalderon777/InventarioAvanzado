<?php
// index.php - Página principal del sistema
session_start();
require_once 'config.php';

// Obtener estadísticas
$sql = "SELECT 
    COUNT(*) as total_productos,
    SUM(cantidad * precio_compra) as valor_inventario,
    SUM(cantidad * precio_venta) as ventas_potenciales,
    SUM(cantidad * (precio_venta - precio_compra)) as ganancias_potenciales,
    SUM(CASE WHEN cantidad <= minimo THEN 1 ELSE 0 END) as productos_bajo_stock
    FROM productos";
$result = $conn->query($sql);
$stats = $result->fetch_assoc();

// Obtener productos con filtros
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

$sql = "SELECT * FROM productos WHERE 1=1";
if ($busqueda) {
    $sql .= " AND (nombre LIKE '%$busqueda%' OR codigo LIKE '%$busqueda%')";
}
if ($categoria) {
    $sql .= " AND categoria = '$categoria'";
}
$sql .= " ORDER BY nombre";

$productos = $conn->query($sql);

// Obtener categorías únicas
$categorias = $conn->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria");

// Productos con stock bajo
$productos_bajo = $conn->query("SELECT * FROM productos WHERE cantidad <= minimo ORDER BY cantidad ASC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - Abarrotes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .stat-card.green { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.blue { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.red { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .badge-stock-bajo { background-color: #dc3545; }
        .badge-stock-ok { background-color: #28a745; }
        .alert-stock {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-boxes"></i> Sistema de Inventario - Abarrotes</h1>
                    <p class="text-muted">Gestión completa de productos</p>
                </div>
                <div>
                    <a href="agregar.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                    <a href="exportar.php" class="btn btn-success btn-lg">
                        <i class="fas fa-download"></i> Exportar
                    </a>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card green">
                        <h5><i class="fas fa-box"></i> Total Productos</h5>
                        <h2><?php echo number_format($stats['total_productos']); ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card blue">
                        <h5><i class="fas fa-dollar-sign"></i> Valor Inventario</h5>
                        <h2>$<?php echo number_format($stats['valor_inventario'], 2); ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card orange">
                        <h5><i class="fas fa-chart-line"></i> Ventas Potenciales</h5>
                        <h2>$<?php echo number_format($stats['ventas_potenciales'], 2); ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card red">
                        <h5><i class="fas fa-exclamation-triangle"></i> Stock Bajo</h5>
                        <h2><?php echo $stats['productos_bajo_stock']; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Alertas de Stock Bajo -->
            <?php if ($productos_bajo->num_rows > 0): ?>
            <div class="alert-stock">
                <h5><i class="fas fa-exclamation-triangle"></i> ⚠️ Productos con Stock Bajo</h5>
                <ul class="mb-0">
                    <?php while ($pb = $productos_bajo->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo $pb['nombre']; ?></strong> - 
                            Stock: <?php echo $pb['cantidad']; ?> (Mínimo: <?php echo $pb['minimo']; ?>)
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Filtros -->
            <form method="GET" class="row mb-4">
                <div class="col-md-6">
                    <input type="text" name="buscar" class="form-control" 
                           placeholder="Buscar por nombre o código..." 
                           value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="col-md-4">
                    <select name="categoria" class="form-select">
                        <option value="">Todas las Categorías</option>
                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $cat['categoria']; ?>" 
                                    <?php echo ($categoria == $cat['categoria']) ? 'selected' : ''; ?>>
                                <?php echo $cat['categoria']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <!-- Tabla de Productos -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Mínimo</th>
                            <th>P. Compra</th>
                            <th>P. Venta</th>
                            <th>Valor Total</th>
                            <th>Proveedor</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = $productos->fetch_assoc()): ?>
                            <tr>
                                <td><code><?php echo $producto['codigo']; ?></code></td>
                                <td><strong><?php echo $producto['nombre']; ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $producto['categoria']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($producto['cantidad'] <= $producto['minimo']) ? 'badge-stock-bajo' : 'badge-stock-ok'; ?>">
                                        <?php echo $producto['cantidad']; ?>
                                    </span>
                                </td>
                                <td><?php echo $producto['minimo']; ?></td>
                                <td>$<?php echo number_format($producto['precio_compra'], 2); ?></td>
                                <td><strong>$<?php echo number_format($producto['precio_venta'], 2); ?></strong></td>
                                <td>$<?php echo number_format($producto['cantidad'] * $producto['precio_compra'], 2); ?></td>
                                <td><small><?php echo $producto['proveedor']; ?></small></td>
                                <td><code><?php echo $producto['ubicacion']; ?></code></td>
                                <td>
                                    <a href="editar.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Eliminar este producto?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>