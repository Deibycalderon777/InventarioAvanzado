<?php
// eliminar.php - Eliminar productos
session_start();
require_once 'config.php';

$id = $_GET['id'];

$sql = "DELETE FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Producto eliminado exitosamente";
} else {
    $_SESSION['error'] = "Error al eliminar el producto";
}

header("Location: index.php");
exit();
?>