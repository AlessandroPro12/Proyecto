<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

try {
    // Validar campos obligatorios
    if (empty($_POST['nombre']) || empty($_POST['precio']) || empty($_POST['stock']) || empty($_POST['categoria_id'])) {
        die("Faltan datos obligatorios");
    }

    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria_id = intval($_POST['categoria_id']);
    $imagen = null;

    // 📸 Subir imagen si existe
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $dir = "../img/productos/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['imagen']['name']);
        $targetPath = $dir . $filename;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
            $imagen = $filename;
        }
    }

    // 🧩 Insertar producto en la base de datos
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen, categoria_id) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen, $categoria_id]);

    header("Location: productos.php?success=1");
    exit;

} catch (PDOException $e) {
    die("Error al guardar el producto: " . $e->getMessage());
}
