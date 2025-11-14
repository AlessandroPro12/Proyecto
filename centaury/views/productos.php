<?php
session_start();
require_once '../config/db.php';

/* ========= Helper ruta imagen producto =========
   Devuelve la URL correcta ya sea que estés en /views/
   (admin) o en la página pública (/).
------------------------------------------------ */
function urlImgProducto(?string $archivo): string {
  $archivo = $archivo ?: 'default.png';
  $rutaBase = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../' : './';
  return $rutaBase . 'img/productos/' . rawurlencode($archivo);
}

// 🔒 Solo administradores
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  header("Location: ../views/login.php");
  exit();
}

// 🗂 Cargar categorías
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🧾 Cargar productos
$stmtProd = $pdo->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
$productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

// 🔴 Acción AJAX: eliminar producto en esta misma página
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['accion']) && $_POST['accion'] === 'eliminar'
    && isset($_POST['id'])) {

  header('Content-Type: application/json; charset=utf-8');

  $id = (int) $_POST['id'];

  try {
    $stmtDel = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $ok = $stmtDel->execute([$id]);

    echo json_encode(['success' => $ok]);
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
  }
  exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel de Administración - Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Montserrat', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  background-color: #f8fafc;
  color: #1e293b;
}

/* 🔹 Sidebar */
.sidebar {
  width: 240px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: linear-gradient(#02031bff, #323461ff);
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
.sidebar a:hover, .sidebar a.active { background: #070716ff; color: #fff; }
.sidebar h5 { font-weight: 700; }

/* 🔹 Contenido principal */
.main-content { margin-left: 250px; padding: 2rem; }
.card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; }
.card-header { background: #070716ff; color: white; font-weight: 600; border-radius: 12px 12px 0 0 !important; }

/* 🔹 Tabla */
.table th { background-color: #f1f5f9; }
.table td, .table th { vertical-align: middle; }

/* 🔹 Botones */
.btn-primary { background-color: #070716ff; border: none; }
.btn-primary:hover { background-color: #070716ff; }
.btn-success { background-color: #22c55e; border: none; }
.btn-success:hover { background-color: #16a34a; }
.btn-danger { background-color: #ef4444; border: none; }
.btn-danger:hover { background-color: #dc2626; }
.btn-secondary { background-color: #64748b; border: none; }
.btn-secondary:hover { background-color: #475569; }

/* 🎯 Color del título de página */
.page-title{ color: #02031bff; }

/* 🖼️ Miniatura: siempre recortada y centrada */
.thumb{
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
  display: block;
  background: #f1f5f9;
}
</style>
</head>
<body>

<!-- 🔹 Sidebar -->
<div class="sidebar">
  <div class="text-center mb-4">
    <h5>🧠 <?= htmlspecialchars($_SESSION['usuario']) ?></h5>
    <p style="font-size: 0.9rem;">Administrador</p>
  </div>
  <a href="dashboard.php"> Inicio</a>
  <a href="productos.php" class="active"> Productos</a>
  <a href="categorias.php"> Categorías</a>
  <a href="clientes.php"> Clientes</a>
  <a href="pedidos.php"> Pedidos</a>
  <a href="reportes.php"> Reportes</a>
  <hr style="border-color: rgba(255,255,255,0.3);">
  <a href="../index.php"> Ir a la tienda</a>
  <a href="../logout.php"> Cerrar sesión</a>
</div>

<!-- 🔹 Contenido principal -->
<div class="main-content">
  <h3 class="fw-bold mb-4 page-title">📦 Administración de Productos</h3>
  <p class="text-muted mb-4">Agrega, visualiza o gestiona los productos disponibles en la tienda.</p>

  <!-- 🟢 FORMULARIO NUEVO PRODUCTO -->
  <div class="card border-0 shadow-sm mb-5">
    <div class="card-header">➕ Añadir Nuevo Producto</div>
    <div class="card-body">
      <form id="formProducto" method="POST" enctype="multipart/form-data" action="guardar_producto.php">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="nombre" class="form-label fw-semibold">Nombre del producto</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="precio" class="form-label fw-semibold">Precio</label>
            <input type="number" step="0.01" name="precio" id="precio" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="stock" class="form-label fw-semibold">Stock</label>
            <input type="number" name="stock" id="stock" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="categoria_id" class="form-label fw-semibold">Categoría</label>
            <div class="d-flex gap-2">
              <select name="categoria_id" id="categoria_id" class="form-select" required>
                <option value="">Seleccione una categoría...</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCategoria">+ Nueva</button>
            </div>
          </div>
          <div class="col-12">
            <label for="descripcion" class="form-label fw-semibold">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-md-6">
            <label for="imagen" class="form-label fw-semibold">Imagen del producto</label>
            <input type="file" name="imagen" id="imagen" class="form-control">
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary px-4">Guardar Producto</button>
          <a href="dashboard.php" class="btn btn-secondary ms-2">⬅ Volver al Panel</a>
        </div>
      </form>
    </div>
  </div>

  <!-- 🧾 LISTADO DE PRODUCTOS -->
  <div class="card border-0 shadow-sm">
    <div class="card-header">📋 Lista de Productos Registrados</div>
    <div class="card-body">
      <?php if (count($productos) > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $index => $p): ?>
            <tr>
              <!-- Número consecutivo en pantalla -->
              <td><?= $index + 1 ?></td>

              <td>
                <img
                  src="<?= urlImgProducto($p['imagen'] ?? null) ?>"
                  alt="img" class="thumb">
              </td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></td>
              <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
              <td><?= $p['stock'] ?></td>
              <td>
                <!-- Aquí sí sigues usando el ID real -->
                <a href="editar_producto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning text-dark">✏️ Editar</a>
                <a href="#" class="btn btn-sm btn-danger" data-id="<?= $p['id'] ?>" onclick="return eliminarProducto(this);">🗑️ Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <p class="text-muted">No hay productos registrados aún.</p>
      <?php endif; ?>
    </div>
  </div>

  <footer class="mt-5 text-center text-muted">
    &copy; <?= date('Y') ?> Zentaury🪐 — Administración de Productos
  </footer>
</div>

<!-- 🔹 Modal Nueva Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formCategoria" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crear nueva categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nombre_categoria" class="form-label">Nombre de la categoría</label>
          <input type="text" id="nombre_categoria" name="nombre_categoria" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="imagen_categoria" class="form-label">Imagen (opcional)</label>
          <input type="file" id="imagen_categoria" name="imagen_categoria" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 🗑️ Eliminar producto vía AJAX en la misma página
async function eliminarProducto(el) {
  const id = el.getAttribute('data-id');
  if (!confirm('¿Seguro que deseas eliminar este producto?')) return false;

  try {
    const res = await fetch('productos.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ accion: 'eliminar', id })
    });
    const data = await res.json();

    if (data.success) {
      const tr = el.closest('tr');
      if (tr) tr.remove();
      alert('✅ Producto eliminado correctamente.');
    } else {
      alert('❌ No se pudo eliminar el producto.');
    }
  } catch (err) {
    console.error(err);
    alert('❌ Ocurrió un error al eliminar.');
  }
  return false;
}

// 🟢 Guardar categoría por AJAX
document.getElementById('formCategoria').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);

  const res = await fetch('guardar_categoria.php', { method: 'POST', body: formData });
  const data = await res.json();

  if (data.success) {
    const select = document.getElementById('categoria_id');
    const option = document.createElement('option');
    option.value = data.id;
    option.textContent = data.nombre;
    select.appendChild(option);
    select.value = data.id;

    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCategoria'));
    modal.hide();
    e.target.reset();
    alert('✅ Categoría creada correctamente.');
  } else {
    alert('Error: ' + data.message);
  }
});
</script>
</body>
</html>
