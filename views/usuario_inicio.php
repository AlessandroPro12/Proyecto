<?php
session_start();
require_once __DIR__ . '/../models/models_admin.php';

// Verificar sesión y rol
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'usuario') {
    header("Location: login.php");
    exit;
}

$nombre_usuario = htmlspecialchars($_SESSION['usuario']);
$cliente_id     = $_SESSION['cliente_id'] ?? null;

if ($cliente_id === null) {
    // Si por alguna razón no hay cliente_id, forzamos logout
    header("Location: logout.php");
    exit;
}

$admin = new AdminModel();
$admin->conexion();

// Obtener pedidos del usuario (por cliente_id)
$pedidos = $admin->listarPedidosUsuario($cliente_id);
$total_pedidos = $pedidos ? count($pedidos) : 0;

$total_gastado = 0;
if ($pedidos) {
    foreach ($pedidos as $p) {
        $total_gastado += $p['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel del Usuario - Sistema de Ventas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    font-family: "Segoe UI", Roboto, sans-serif;
    background-color: #f8fafc;
    color: #1e293b;
}
.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #6366f1;
    color: #fff;
    padding-top: 1rem;
    box-shadow: 2px 0 6px rgba(0,0,0,0.1);
}
.sidebar a {
    color: #e0e7ff;
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 8px;
    margin: 4px 10px;
    transition: 0.2s;
}
.sidebar a:hover, .sidebar a.active {
    background: #4f46e5;
    color: #fff;
}
.sidebar h5 {
    font-weight: 700;
}
.main-content {
    margin-left: 250px;
    padding: 2rem;
}
.card {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}
.card-header {
    background: #f1f5f9;
    font-weight: 600;
    border-radius: 12px 12px 0 0 !important;
}
footer {
    text-align: center;
    padding: 20px;
    color: #64748b;
    margin-top: 3rem;
}
.badge {
    font-size: 0.85rem;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="text-center mb-4">
        <h5>👤 <?= $nombre_usuario ?></h5>
        <p style="font-size: 0.9rem;">Usuario</p>
    </div>
    <a href="usuario_inicio.php" class="active">🏠 Inicio</a>
    <a href="usuario_pedidos.php">🛒 Mis pedidos</a>
    <a href="usuario_perfil.php">👤 Mi perfil</a>
    <a href="usuario_soporte.php">💬 Soporte</a>
    <hr style="border-color: rgba(255,255,255,0.3);">
    <a href="../index.php">🏬 Volver a la tienda</a>
    <a href="../logout.php">🚪 Cerrar sesión</a>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="mb-4">
        <h3 class="fw-bold">Bienvenido, <?= $nombre_usuario ?> 👋</h3>
        <p class="text-muted">Aquí puedes revisar tus pedidos y tu historial de compras.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-3 text-center border-0 shadow-sm">
                <h5>Pedidos Realizados</h5>
                <h3 class="text-primary"><?= $total_pedidos ?></h3>
                <p class="text-muted">Total de compras registradas</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center border-0 shadow-sm">
                <h5>Total Gastado</h5>
                <h3 class="text-success">$<?= number_format($total_gastado, 2) ?></h3>
                <p class="text-muted">Suma de todos tus pedidos</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center border-0 shadow-sm">
                <h5>Estado de Cuenta</h5>
                <h3 class="text-warning">Activo</h3>
                <p class="text-muted">Cuenta en funcionamiento</p>
            </div>
        </div>
    </div>

    <div class="card mt-5 p-4">
        <h5 class="mb-3">🧾 Últimos pedidos</h5>
        <?php if ($pedidos && count($pedidos) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($pedidos, 0, 5) as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                        <td>$<?= number_format($p['total'], 2) ?></td>
                        <td>
                            <?php
                                $estado = strtolower($p['estado']);

                                // Versión compatible con PHP 7
                                switch ($estado) {
                                    case 'pendiente':
                                        $color = 'warning';
                                        break;
                                    case 'pagado':
                                        $color = 'success';
                                        break;
                                    case 'cancelado':
                                        $color = 'danger';
                                        break;
                                    default:
                                        $color = 'secondary';
                                        break;
                                }
                            ?>
                            <span class="badge bg-<?= $color ?>"><?= ucfirst($estado) ?></span>
                        </td>
                        <td>
                            <a href="detalle_pedido.php?id=<?= $p['id'] ?>"
                            class="btn btn-sm btn-outline-primary">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted">Aún no tienes pedidos registrados.</p>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Sistema de Ventas - Panel de Usuario
    </footer>
</div>

</body>
</html>
