<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Validar datos
if (!isset($_POST['id'], $_POST['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id     = (string)$_POST['id'];
$nombre = trim((string)$_POST['nombre']);
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : null;

if ($id === '' || $nombre === '') {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Asegurar estructura de favoritos
if (!isset($_SESSION['favoritos']) || !is_array($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}

// Evitar duplicados: simplemente se sobreescribe
$_SESSION['favoritos'][$id] = [
    'nombre' => $nombre,
];

if ($precio !== null) {
    $_SESSION['favoritos'][$id]['precio'] = $precio;
}

echo json_encode(['success' => true]);
