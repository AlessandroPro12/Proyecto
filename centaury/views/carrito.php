<?php
session_start();
require_once '../config/db.php';

// Solo usuarios logueados pueden acceder
if (!isset($_SESSION['usuario'])) {
    header("Location: views/login.php");
    exit;
}

// 🔐 CSRF: genera (si no existe) y usa un token por sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Carrito
$datoscarrito = $_SESSION['carrito'] ?? [];
$cantidadtotal = 0;
foreach ($datoscarrito as $items) {
    $precio   = (float)($items['precio']   ?? 0);
    $cantidad = (int)  ($items['cantidad'] ?? 0);
    $cantidadtotal += $precio * $cantidad;
}

/*
 * ⭐ Favoritos — Seguridad añadida
 * - Si no existe o alguien pisó la variable con un tipo no-array, la reiniciamos.
 * - $favoritos obtiene SIEMPRE un array.
 */
if (!isset($_SESSION['favoritos']) || !is_array($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}
$favoritos = $_SESSION['favoritos'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title> 🛒 - Mi Carrito De Compras - 🧺 </title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">🛒 Mi Carrito De Compras</h2>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success">
         El Pedido Ha Sido Realizado Con Exito ID: <?= htmlspecialchars($_GET['pedido_id']) ?>
      </div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
      <div class="alert alert-warning">
         Tu carrito esta vacio, añade un producto de la tienda para seguir.
      </div>
    <?php endif; ?> <!-- Cierre del bloque de mensajes -->

    <?php if (empty($datoscarrito)): ?>
        <div class="alert alert-info">Tu carrito está vacío.</div>
        <a href="../index.php" class="btn btn-primary">Volver a la tienda</a>
    <?php else: ?>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datoscarrito as $IDproductos => $items): ?>
                    <?php
                        // defensas suaves por si algún item no es array
                        if (!is_array($items)) { continue; }
                        $nombre   = htmlspecialchars((string)($items['nombre'] ?? 'Producto'));
                        $precio   = (float)($items['precio']   ?? 0);
                        $cantidad = (int)  ($items['cantidad'] ?? 0);
                        $subtotal = $precio * $cantidad;
                    ?>
                    <tr>
                        <td><?= $nombre ?></td>
                        <td>$<?= number_format($precio, 0, ',', '.') ?></td>
                        <td><?= $cantidad ?></td>
                        <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
                        <td>
                            <a
                              href="quitar_carrito.php?id=<?= urlencode((string)$IDproductos) ?>&csrf=<?= urlencode($csrfToken) ?>"
                              class="btn btn-sm btn-danger"
                              title="Quitar del carrito"
                            >❌</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <h4>Total: <span class="text-success">$<?= number_format($cantidadtotal, 0, ',', '.') ?></span></h4>
            <div>
                <a href="../index.php" class="btn btn-secondary">🛍 Seguir con la Compras</a>
                <a href="finalizar_pedido.php?csrf=<?= urlencode($csrfToken) ?>" class="btn btn-success">✅ Realizar El Pedido</a>
            </div>
        </div>
    <?php endif; ?>


    <!-- Sección de FAVORITOS ⭐ -->
    <div class="mt-5">
        <h3 class="mb-3"> ⭐ - Productos favoritos</h3>

        <?php if (empty($favoritos)): ?>
            <div class="alert alert-info"> 🤷‍♂️ - Aún no tienes productos en favoritos.</div>
            <a href="../index.php" class="btn btn-outline-primary"> 🔎 - Explorar productos</a>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($favoritos as $favId => $favItem): ?>
                                    <?php
                                        if (!is_array($favItem)) { continue; }
                                        $favNombre = htmlspecialchars((string)($favItem['nombre'] ?? 'Producto'));
                                        $favPrecio = isset($favItem['precio']) ? (float)$favItem['precio'] : null;
                                    ?>
                                    <tr>
                                        <td><?= $favNombre ?></td>
                                        <td>
                                            <?php if ($favPrecio !== null): ?>
                                                $<?= number_format($favPrecio, 0, ',', '.') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Quitar de favoritos -->
                                            <a
                                              href="quitar_favorito.php?id=<?= urlencode((string)$favId) ?>&csrf=<?= urlencode($csrfToken) ?>"
                                              class="btn btn-sm btn-outline-danger"
                                              title="Quitar de favoritos"
                                            >❌</a>
                                            <!-- (Opcional) Ver/Comprar -->
                                            <a
                                              href="../producto.php?id=<?= urlencode((string)$favId) ?>"
                                              class="btn btn-sm btn-outline-secondary"
                                              title="Ver producto"
                                            >🔍</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div> <!--/.table-responsive-->
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- /FIN Favoritos -->
</div>

</body>
</html>
