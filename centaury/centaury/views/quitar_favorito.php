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
    if (isset($_SESSION['favoritos']) && is_array($_SESSION['favoritos'])) {
        if (isset($_SESSION['favoritos'][$id])) {
            unset($_SESSION['favoritos'][$id]);
        }
        // opcional: si se quedó vacío, lo limpiamos
        if (empty($_SESSION['favoritos'])) {
            unset($_SESSION['favoritos']);
        }
    }
}

// Volver al carrito (donde está la sección de favoritos)
header('Location: carrito.php');
exit;
