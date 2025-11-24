<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Validar datos recibidos
if (!isset($_POST['id'], $_POST['nombre'], $_POST['precio'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id      = (string)$_POST['id'];
$nombre  = trim((string)$_POST['nombre']);
$precio  = (float)$_POST['precio'];

if ($id === '' || $nombre === '') {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Asegurar estructura del carrito
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Si ya existe el producto en el carrito, solo aumentamos la cantidad
if (isset($_SESSION['carrito'][$id])) {
    $cantidadActual = (int)($_SESSION['carrito'][$id]['cantidad'] ?? 1);
    $_SESSION['carrito'][$id]['cantidad'] = $cantidadActual + 1;
    $_SESSION['carrito'][$id]['nombre'] = $nombre;
    $_SESSION['carrito'][$id]['precio'] = $precio;
} else {
    $_SESSION['carrito'][$id] = [
        'nombre'   => $nombre,
        'precio'   => $precio,
        'cantidad' => 1,
    ];
}

echo json_encode(['success' => true]);
