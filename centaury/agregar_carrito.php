<?php
session_start();

// Siempre responder JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar método
if ($_server['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar datos recibidos
if (!isset($_POST['id'], $_POST['nombre'], $_POST['precio'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id = intval($_POST['id']);
$nombre = trim($_POST['nombre']);
$precio = floatval($_POST['precio']);
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

// Validación mínima de valores
if ($id <= 0 || $precio <= 0 || $cantidad <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Si ya existe el producto, sumar cantidad
if (isset($_SESSION['carrito'][$id])) {
    $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
} else {
    $_SESSION['carrito'][$id] = [
        'nombre' => $nombre,
        'precio' => $precio,
        'cantidad' => $cantidad
    ];
}

// Calcular totales
$total_items = 0;            // suma de cantidades
$total_general = 0.0;
foreach ($_SESSION['carrito'] as $item) {
    $total_items += (int)$item['cantidad'];
    $total_general += ((float)$item['precio']) * (int)$item['cantidad'];
}
$items_distintos = count($_SESSION['carrito']); // número de IDs únicos

// Devolver confirmación en JSON
echo json_encode([
    'success' => true,
    'message' => 'Producto agregado al carrito',
    'items_distintos' => $items_distintos,
    'total_items' => $total_items,
    'total_general' => number_format($total_general, 2, '.', '')
]);
