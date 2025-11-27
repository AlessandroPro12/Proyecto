<?php
session_start();
require_once './config/db.php';

// 1) Parámetros
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$q     = isset($_GET['q'])   ? trim($_GET['q'])   : '';

// 2) Nombre de categoría
$nombreCategoria = null;
if ($catId > 0) {
  $st = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ?");
  $st->execute([$catId]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if ($row) $nombreCategoria = $row['nombre']; else $catId = 0;
}

// 3) Productos filtrados
$sql = "SELECT p.id, p.nombre, p.precio, p.imagen, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE 1=1";
$params = [];
if ($catId > 0) { $sql .= " AND p.categoria_id = ?"; $params[] = $catId; }
if ($q !== '')  { $sql .= " AND p.nombre LIKE ?";     $params[] = "%$q%"; }
$sql .= " ORDER BY p.id DESC";

$st = $pdo->prepare($sql);
$st->execute($params);
$productos = $st->fetchAll(PDO::FETCH_ASSOC);

// 4) Categorías (atajos)
$cats = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $nombreCategoria ? 'Productos en '.htmlspecialchars($nombreCategoria) : 'Todos los productos' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{ font-family: system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; background:#f8fafc; color:#1e293b; }
  .wrap{ max-width:1200px; margin:auto; padding:1.2rem 1rem; }
  .grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap:1rem; }
  .card{ background:#fff; border-radius:12px; box-shadow:0 3px 6px rgba(0,0,0,.08); padding:14px; }
  .card img{ width:100%; height:180px; object-fit:contain; border-radius:8px; margin-bottom:8px; background:#f1f5f9; }
  .price{ color:#16a34a; font-weight:800; }
</style>
</head>
<body>
<div class="wrap">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <a href="index.php" class="btn btn-link p-0">← Volver</a>
      <h3 class="m-0"><?= $nombreCategoria ? 'Productos en '.htmlspecialchars($nombreCategoria) : 'Todos los productos' ?></h3>
      <small class="text-muted"><?= count($productos) ?> resultado(s)</small>
    </div>
    <form class="d-flex gap-2" method="get" action="productos_tienda.php">
      <?php if ($catId>0): ?><input type="hidden" name="cat" value="<?= (int)$catId ?>"><?php endif; ?>
      <input class="form-control" type="text" name="q" placeholder="Buscar..." value="<?= htmlspecialchars($q) ?>">
      <button class="btn btn-primary" type="submit">Buscar</button>
    </form>
  </div>

  <div class="mb-3">
    <?php foreach ($cats as $c): ?>
      <a href="productos_tienda.php?cat=<?= (int)$c['id'] ?>" class="btn btn-outline-secondary btn-sm mb-1"><?= htmlspecialchars($c['nombre']) ?></a>
    <?php endforeach; ?>
    <a href="productos_tienda.php" class="btn btn-outline-dark btn-sm mb-1">Todas</a>
  </div>

  <div class="grid">
    <?php if (!empty($productos)): ?>
      <?php foreach ($productos as $p): ?>
        <div class="card">
          <img src="./img/productos/<?= htmlspecialchars($p['imagen'] ?: 'default.png') ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
          <div class="fw-bold mb-1"><?= htmlspecialchars($p['nombre']) ?></div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="price">$<?= number_format((float)$p['precio'], 0, ',', '.') ?> COP</div>
            <small class="text-muted"><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></small>
          </div>
          <button class="btn btn-sm btn-primary mt-2 btn-agregar" data-id="<?= (int)$p['id'] ?>">🛒 Agregar</button>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No se encontraron productos.</p>
    <?php endif; ?>
  </div>
</div>

<script>
// Está en RAÍZ, así que llama SIN ../
document.querySelectorAll('.btn-agregar').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    try {
      const res = await fetch('agregar_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id })
      });
      const data = await res.json();
      alert(data?.success ? '✅ Producto agregado' : '❌ No se pudo agregar');
    } catch (e) {
      alert('❌ Error de red o JSON');
    }
  });
});
</script>
</body>
</html>
