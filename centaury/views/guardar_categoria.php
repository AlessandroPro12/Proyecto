<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php'; // Ajusta la ruta si tu archivo está en otra carpeta

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['nombre_categoria']) || trim($_POST['nombre_categoria']) === '') {
        echo json_encode(['success' => false, 'mensaje' => 'Es obligatorio nombrar la categoria']);
        exit;
    }

    $nombre = trim($_POST['nombre_categoria']);
    $imagen = null;

    // Si se sube imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $dir = "../img/categorias/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $nombarchivo = time() . '_' . basename($_FILES['imagen']['name']);
        $targetPath = $dir . $nombarchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
            $imagen = $nombarchivo;
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Ha ocurrido un Error al subir la imagen.']);
            exit;
        }
    }

    // Insertar en la base de datos
    $stmt = $pdo->prepare("INSERT INTO categorias (nombre, imagen) VALUES (?, ?)");
    $stmt->execute([$nombre, $imagen]);

    $IDNueva = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'ID' => $IDNueva,
        'nombre' => $nombre
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error en Base de Datos: ' . $e->getmensaje()]);
}
