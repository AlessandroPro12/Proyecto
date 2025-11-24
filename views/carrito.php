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
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{  font-family: 'Montserrat', sans-serif;  box-sizing: border-box;  }

        
    body {
        background: linear-gradient(135deg, #02031bff, #323461ff);
        min-height: 100vh;
        padding: 20px 0;
    }

    .main-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
    }

    .header-carrito {
        background: linear-gradient(135deg, #02031bff 0%, #323461ff 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }

    .header-carrito h2 {
        margin: 0;
        font-weight: 700;
        font-size: 2.5rem;
    }

    .table-carrito {
        border: none;
    }

    .table-carrito thead {
        background: linear-gradient(135deg, #f5f7ff 0%, #e8e8ff 100%);
        font-weight: 600;
        color: #333;
    }

    .table-carrito tbody tr {
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .table-carrito tbody tr:hover {
        background-color: #f8f9ff;
        transform: translateX(5px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
    }

    .resumen-total {
        background: linear-gradient(135deg, #f5f7ff 0%, #e8e8ff 100%);
        border-radius: 15px;
        padding: 25px;
        margin-top: 30px;
        border-left: 5px solid #667eea;
    }

    .resumen-total h4 {
        color: #333;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .total-amount {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .btn-secondary, .btn-success, .btn-outline-primary {
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-secondary {
        background-color: #6c757d;
    }

    .btn-secondary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
    }

    .btn-success {
        background: linear-gradient(135deg, #02031bff, #323461ff);
    }

    .btn-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }

    .favoritos-section {
        margin-top: 50px;
        padding-top: 40px;
        border-top: 2px solid #e9ecef;
    }

    .btn-primary {
        background: linear-gradient(135deg, #02031bff, #323461ff);
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }
    .favoritos-header {
        background: linear-gradient(135deg, #02031bff 0%, #323461ff 100%);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 25px;
        font-weight: 600;
        font-size: 1.3rem;
    }

    .card-favoritos {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-favoritos tbody tr {
        transition: all 0.3s ease;
    }

    .table-favoritos tbody tr:hover {
        background-color: #fff8f0;
    }

    .alert {
        border-radius: 15px;
        border: none;
        font-weight: 500;
    }

    .alert-info {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #333;
    }

    .alert-warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333;
    }

    .alert-success {
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e59c 100%);
        color: #333;
    }
</style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="main-container">
        <div class="header-carrito">
            <h2>🛒 Mi Carrito De Compras</h2>
        </div>

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
            <div class="table-responsive">
                <table class="table table-carrito">
                    <thead>
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
                                    href="quitar_producto.php?id=<?= urlencode((string)$IDproductos) ?>&csrf=<?= urlencode($csrfToken) ?>"
                                    class="btn btn-sm btn-danger"
                                    title="Quitar del carrito"
                                    >❌</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="resumen-total">
                <h4>💰 Resumen del Pedido</h4>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size: 1.2rem; color: #666;">Total a Pagar:</span>
                    <span class="total-amount">$<?= number_format($cantidadtotal, 0, ',', '.') ?></span>
                </div>
                <div class="mt-4 d-flex gap-3 justify-content-end">
                    <a href="../index.php" class="btn btn-secondary">🛍 Seguir Comprando</a>
                    <a href="finalizar_pedido.php?csrf=<?= urlencode($csrfToken) ?>" class="btn btn-success">✅ Finalizar Pedido</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sección de FAVORITOS ⭐ -->
        <div class="favoritos-section">
            <div class="favoritos-header">⭐ - Productos Favoritos</div>

            <?php if (empty($favoritos)): ?>
                <div class="alert alert-info"> 🤷‍♂️ - Aún no tienes productos en favoritos.</div>
                <a href="../index.php" class="btn btn-outline-primary"> 🔎 - Explorar productos</a>
            <?php else: ?>
                <div class="card card-favoritos">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-favoritos table-hover mb-0">
                                <thead style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
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
                                                href="quitar_producto.php?id=<?= urlencode((string)$favId) ?>&csrf=<?= urlencode($csrfToken) ?>"
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
</div>

</body>
</html>