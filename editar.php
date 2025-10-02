<?php
// editar.php - Formulario para editar productos
session_start();
require_once 'config.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $cantidad = $_POST['cantidad'];
    $minimo = $_POST['minimo'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $proveedor = $_POST['proveedor'];
    $ubicacion = $_POST['ubicacion'];

    $sql = "UPDATE productos SET codigo=?, nombre=?, categoria=?, cantidad=?, minimo=?, 
            precio_compra=?, precio_venta=?, proveedor=?, ubicacion=? WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiiddssi", $codigo, $nombre, $categoria, $cantidad, $minimo, 
                      $precio_compra, $precio_venta, $proveedor, $ubicacion, $id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Producto actualizado exitosamente";
        header("Location: index.php");
        exit();
    } else {
        $error = "Error al actualizar el producto: " . $conn->error;
    }
}

$producto = $conn->query("SELECT * FROM productos WHERE id = $id")->fetch_assoc();
$categorias = $conn->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit"></i> Editar Producto</h2>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Código *</label>
                        <input type="text" name="codigo" class="form-control" value="<?php echo $producto['codigo']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo $producto['nombre']; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Categoría *</label>
                        <input type="text" name="categoria" class="form-control" value="<?php echo $producto['categoria']; ?>" list="categorias" required>
                        <datalist id="categorias">
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?php echo $cat['categoria']; ?>">
                            <?php endwhile; ?>
                        </datalist>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" value="<?php echo $producto['cantidad']; ?>" min="0" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock Mínimo *</label>
                        <input type="number" name="minimo" class="form-control" value="<?php echo $producto['minimo']; ?>" min="0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Precio de Compra *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio_compra" class="form-control" value="<?php echo $producto['precio_compra']; ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Precio de Venta *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="precio_venta" class="form-control" value="<?php echo $producto['precio_venta']; ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Proveedor</label>
                        <input type="text" name="proveedor" class="form-control" value="<?php echo $producto['proveedor']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ubicación</label>
                        <input type="text" name="ubicacion" class="form-control" value="<?php echo $producto['ubicacion']; ?>">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save"></i> Actualizar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
