<?php
session_start();
require_once './config/db.php';

/* ========= Helpers para rutas de imágenes =========
   Devuelven la URL correcta independientemente de si
   el archivo está en / o en /views/.
--------------------------------------------------- */
function urlImgProducto(?string $archivo): string {
  $archivo = $archivo ?: 'default.png';
  $rutaBase = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../' : './';
  return $rutaBase . 'img/productos/' . rawurlencode($archivo);
}
function urlImgCategoria(?string $archivo): string {
  $archivo = $archivo ?: 'default.png';
  $rutaBase = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../' : './';
  return $rutaBase . 'img/categorias/' . rawurlencode($archivo);
}

// Cargar productos desde la base de datos
try {
    $stmt = $pdo->query("SELECT p.id, p.nombre, p.precio, p.imagen, c.nombre AS categoria 
                         FROM productos p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
                         ORDER BY p.id DESC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mercado Global - Sistema de Ventas</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color: #f8fafc;
  color: #1e293b;
  font-family: 'Montserrat', 'Segoe UI', Roboto, sans-serif;
}
header {
  background-color: #02031bff;
  color: white;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
}
header h1 a { color: white; text-decoration: none; font-weight: 700; }
header nav a { color: white; margin: 0 10px; text-decoration: none; font-weight: 500; }
.header_x nav a:hover { text-decoration: underline; }
.search input { border-radius: 8px; border: none; padding: 8px 14px; width: 250px; }

/* ===== Banner ===== */
.banner {
  background: url('./img/banner/coño.jpg') no-repeat center center/cover;
  text-align: center;
  padding: 4rem 1rem;
}
.banner_x h2 { font-size: 2rem; font-weight: bold; }
.banner_x p { font-size: 1rem; opacity: 0.9; }
.banner_x .btn {
  background-color: #C9A66B; color: white; font-weight: 600;
  border-radius: 8px; padding: 10px 20px; border: none;
}
.banner_x .btn:hover { background-color: #02031bff; }

.container { max-width: 1200px; margin: auto; padding: 2rem 1rem; }

/* ===== Categorías ===== */
.cats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 20px; text-align: center; margin-bottom: 2rem;
}
.cat {
  background: white; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  padding: 15px; transition: transform 0.2s ease;
}
.cat_x:hover { transform: scale(1.05); }
.cat_x img { width: 70px; height: 70px; object-fit: contain; margin-bottom: 10px; }

/* ===== Productos ===== */
.grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;
}
.card {
  background: white; border-radius: 12px; box-shadow: 0 3px 6px rgba(0,0,0,0.08);
  padding: 15px; transition: all 0.2s ease;
}
.card_x:hover { transform: translateY(-3px); box-shadow: 0 6px 10px rgba(0,0,0,0.15); }

/* Imagen de producto: cubre y se recorta bonito */
.product-cover{
  width: 100%;
  height: 180px;       /* ajusta si lo quieres más alto */
  object-fit: cover;   /* <- la clave */
  border-radius: 8px;
  margin-bottom: 10px;
  background: #f1f5f9; /* fondo por si tarda en cargar */
  display: block;
}

.card_x .title { font-weight: 600; margin-bottom: 5px; }
.card_x .price { color: #16a34a; font-weight: 700; margin-bottom: 10px; }
.card_x .meta { display: flex; justify-content: space-between; align-items: center; }
.card_x .meta-buttons {
  display: flex;
  gap: 6px;
}
.card_x .meta small { color: #64748b; }
.card_x button {
  background-color: #6366f1; border: none; color: white; border-radius: 6px;
  padding: 6px 12px; cursor: pointer; font-weight: 500;
}
.card_x button:hover { background-color: #4f46e5; }

footer {
  text-align: center; padding: 1rem; background-color: #1e293b; color: white; margin-top: 2rem;
}
.btn-crear { background-color: #22c55e; border: none; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; }
.btn-crear_x:hover { background-color: #16a34a; }
</style>
</head>
<body>

<header class="header_x">
  <h1><a href="index.php">Zentaury🪐</a></h1>
  <div class="search"><input placeholder="Buscar artículos"></div>
  <nav>
    <a href="index.php">🏠Inicio</a>
    <?php if (isset($_SESSION['rol'])): ?>
      <?php if ($_SESSION['rol'] === 'admin'): ?>
        <a href="views/productos.php">💻Productos</a>
        <a href="contacto.php">📱Contacto</a>
        <a href="views/carrito.php">🛒 Carrito
          <?php if (!empty($_SESSION['carrito'])): ?>
            <span class="badge bg-light text-dark"><?= count($_SESSION['carrito']) ?></span>
          <?php endif; ?>
        </a>
        <a href="views/dashboard.php">🧠Panel para Admin</a>
      <?php elseif ($_SESSION['rol'] === 'usuario'): ?>
        <a href="views/usuario_inicio.php" class="btn btn-primary btn-sm fw-semibold ms-2">🏠 Mi Panel</a>
      <?php endif; ?>
      <a href="views/logout.php" class="btn btn-sm btn-light ms-2">Salir</a>
    <?php else: ?>
      <a href="views/login.php" class="btn btn-light btn-sm ms-2">Iniciar sesión</a>
    <?php endif; ?>
  </nav>
</header>

<section class="banner banner_x">
  <div class="inner container">
    <h2>¡Descuentos de otra GALAXIA!</h2>
    <p>Detalles que marcan la diferencia</p>
    <button class="btn" onclick="location.href='productos.php'">Empieza a Comprar</button>
  </div>
</section>

<main class="container">
  <!-- REORDEN: Primero productos, luego categorías (sin eliminar líneas) -->
  <h3 class="section mb-4">Top Productos</h3>

  <div class="grid">
    <?php if (count($productos) > 0): ?>
      <?php foreach ($productos as $p): ?>
        <div class="card card_x" data-cat="<?= htmlspecialchars($p['categoria']) ?>">
          <!-- Cambiado: usa helper + clase product-cover -->
          <img
            src="<?= urlImgProducto($p['imagen'] ?? null) ?>"
            alt="<?= htmlspecialchars($p['nombre']) ?>"
            class="product-cover">
          <div class="title"><?= htmlspecialchars($p['nombre']) ?></div>
          <div class="price">$<?= number_format($p['precio'], 0, ',', '.') ?> COP</div>
          <div class="meta">
            <small><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></small>
            <div class="meta-buttons">
              <button
                class="btn-agregar"
                data-id="<?= $p['id'] ?>"
                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                data-precio="<?= $p['precio'] ?>">
                🛒 Agregar
              </button>

              <button
                class="btn-favorito"
                data-id="<?= $p['id'] ?>"
                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                data-precio="<?= $p['precio'] ?>">
                ⭐ Favorito
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted">No hay productos disponibles.</p>
    <?php endif; ?>
  </div>

  <h3 class="section mb-4 mt-5">Categorías</h3>

  <div class="cats">
    <?php if (count($categorias) > 0): ?>
      <?php foreach ($categorias as $cat): ?>
        <div class="cat cat_x">
          <img
            src="<?= urlImgCategoria($cat['imagen'] ?? null) ?>"
            alt="<?= htmlspecialchars($cat['nombre']) ?>">
          <div><button><?= htmlspecialchars($cat['nombre']) ?></button></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted">No hay categorías registradas.</p>
    <?php endif; ?>
  </div>
</main>

<footer>
  © <?= date('Y') ?> Zentaury🪐- support@zentaury.com
</footer>

<script>
document.querySelectorAll('.btn-agregar').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const nombre = btn.dataset.nombre;
    const precio = btn.dataset.precio;

    const res = await fetch('agregar_carrito.php', {
      method: 'POST',
      body: new URLSearchParams({ id, nombre, precio })
    });

    const data = await res.json();
    if (data.success) {
      alert("✅ Producto agregado al carrito");
    } else {
      alert("❌ Error al agregar al carrito");
    }
  });
});
</script>
<script>
document.querySelectorAll('.btn-favorito').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const nombre = btn.dataset.nombre;
    const precio = btn.dataset.precio;

    const res = await fetch('agregar_favorito.php', {
      method: 'POST',
      body: new URLSearchParams({ id, nombre, precio })
    });

    const data = await res.json();
    if (data.success) {
      alert("⭐ Producto agregado a favoritos");
    } else {
      alert("❌ Error al agregar a favoritos");
    }
  });
});
</script>

</body>
</html>
