<?php
session_start();

// Opcional: solo usuarios logueados
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Validar CSRF
if (
    !isset($_SESSION['csrf_token'], $_GET['csrf']) ||
    !hash_equals($_SESSION['csrf_token'], $_GET['csrf'])
) {
    header('Location: carrito.php?error=csrf');
    exit;
}

// ID del producto
$id = isset($_GET['id']) ? (string)$_GET['id'] : '';

if ($id !== '') {
    if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
        if (isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
        }
        // opcional: si se quedó vacío, lo limpiamos
        if (empty($_SESSION['carrito'])) {
            unset($_SESSION['carrito']);
        }
    }
}

// Volver al carrito
header('Location: carrito.php');
exit;
