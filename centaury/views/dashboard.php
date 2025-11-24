<?php
session_start();

// 🔒 Solo los administradores pueden acceder
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../views/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> 🧠adminitracion</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg: #f8fafc;
  --text: #1e293b;
  --muted:#64748b;
  --card-border:#e5e7eb;
  --card-bg:#fff;
  --card-header:#f1f5f9;
  --brand-1:#6366f1; /* primario */
  --brand-2:#10b981; /* success */
  --brand-3:#f59e0b; /* warning */
  --brand-4:#334155; /* secondary */
}

*{ box-sizing: border-box; }
html,body{ height:100%; }
body {
  margin:0;
  font-family: 'Montserrat', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  background-color: var(--bg);
  color: var(--text);
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
  overflow-y: auto;
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
  background: #070716ff;
  color: #fff;
}
.sidebar h5 { font-weight: 700; margin: 0; }
.sidebar .section-title {
  font-size: .8rem; text-transform: uppercase;
  letter-spacing: .06em; color: rgba(255,255,255,0.7);
  margin: 10px 16px 6px;
}
.avatar {
  width: 64px; height: 64px; border-radius: 50%;
  background: rgba(255,255,255,0.18);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 1.2rem;
  margin: 0 auto 8px; border: 2px solid rgba(255,255,255,0.35);
}
.sidebar .center { text-align:center; }
.sidebar .px-3 { padding: 0 12px; }
.sidebar .mb-3 { margin-bottom: 12px; }
.sidebar .small { font-size: .8rem; }
.sidebar input[type="text"]{
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  border: none;
  outline: none;
}

/* 🔹 Contenido principal */
.main-content {
  margin-left: 250px;
  padding: 2rem;
}
h3{ margin: 0 0 .25rem 0; }
.text-muted{ color: var(--muted); }

/* 🔹 Card base */
.card {
  background: var(--card-bg);
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.08);
  border: 1px solid var(--card-border);
  padding: 16px;
}
.card .text-center{ text-align:center; }

/* 🔹 Botones (similares a Bootstrap pero sin BS) */
.btn{
  display:inline-block;
  padding: 8px 12px;
  border-radius:10px;
  text-decoration:none;
  font-weight:600;
  border:1px solid transparent;
  transition: transform .06s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease;
}
.btn:active{ transform: translateY(1px); }
.btn-primary{ background: var(--brand-1); color:#fff; }
.btn-primary:hover{ filter: brightness(1.05); }
.btn-success{ background: var(--brand-2); color:#fff; }
.btn-success:hover{ filter: brightness(1.05); }
.btn-warning{ background: var(--brand-3); color:#111827; }
.btn-warning:hover{ filter: brightness(1.05); }
.btn-secondary{ background: var(--brand-4); color:#fff; }
.btn-secondary:hover{ filter: brightness(1.05); }
.btn-outline-primary{
  background: transparent; color: var(--brand-1); border-color: var(--brand-1);
}
.btn-outline-primary:hover{
  background: var(--brand-1); color:#fff;
}

/* 🔹 Grid de tarjetas (3 arriba, 2 abajo, centrado) */
.cards-grid{
  display: grid;
  gap: 24px;
  max-width: 1100px;
  margin: 16px auto 0;
  grid-template-columns: repeat(3, minmax(220px, 1fr));
  grid-template-areas:
    "pedidos     productos   categorias"
    "clientes    config      config";
  justify-content: center;
}

.card-item{ display:block; }
.card-item h5{ margin: 6px 0 8px; }

.pedidos   { grid-area: pedidos; }
.productos { grid-area: productos; }
.categorias{ grid-area: categorias; }
.clientes  { grid-area: clientes; }
.config    { grid-area: config; }

/* Responsivo: tablets */
@media (max-width: 1024px){
  .cards-grid{
    grid-template-columns: repeat(2, minmax(220px,1fr));
    grid-template-areas:
      "pedidos     productos"
      "categorias  clientes"
      "config      config";
  }
}

/* Móvil */
@media (max-width: 768px){
  .main-content{ margin-left: 0; padding: 1rem; }
  .sidebar{ position: static; width: 100%; height: auto; }
  .cards-grid{
    grid-template-columns: 1fr;
    grid-template-areas:
      "pedidos"
      "productos"
      "categorias"
      "clientes"
      "config";
    max-width: 640px;
  }
}

/* 🔹 Footer */
footer {
  text-align: center;
  padding: 20px;
  color: var(--muted);
  margin-top: 3rem;
  border-top: 1px solid #e2e8f0;
}

/* 🔹 Utilidades pequeñas */
.mt-3{ margin-top: 1rem; }
.mb-4{ margin-bottom: 1.25rem; }
.fw-bold{ font-weight: 700; }

/* 🔹 Badge de rol y espacio con “Último acceso” */
.role-badge{
  display:inline-block;
  padding:3px 8px;
  border-radius:8px;
  background:#f8fafc;
  color:#111;
  font-weight:600;
  font-size:.85rem;
  margin-bottom:8px;       /* ← espacio inferior bajo “🧠 admin” */
}
.sidebar .small{
  margin-top:12px;         /* ← espacio superior extra por si prefieres controlarlo aquí */
  opacity:.85;
  display:block;
}
</style>
</head>
<body>

<!-- 🔹 Sidebar -->
<?php
  $nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
  $inicial = mb_strtoupper(mb_substr($nombreUsuario, 0, 1, 'UTF-8'), 'UTF-8');
  $ultimoAcceso = $_SESSION['ultimo_login'] ?? date('d/m/Y H:i');
?>
<div class="sidebar">
  <div class="center mb-3 px-3">
    <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
    <h5 class="mt-2 mb-1">🧠 <?= htmlspecialchars($nombreUsuario) ?></h5>

    <!-- Badge de rol con espacio inferior -->
    <?php if (!empty($_SESSION['rol'])): ?>
      
    <?php endif; ?>

    <div class="small">Último acceso: <?= htmlspecialchars($ultimoAcceso) ?></div>
  </div>

  <!-- Buscador de menú -->
  <div class="px-3 mb-3">
    <input id="menuSearch" type="text" placeholder="Buscar en el menú...">
  </div>

  <div class="section-title">Navegación</div>
  <nav id="sideMenu">
    <a href="dashboard.php" class="active"> Inicio</a>
    <a href="productos.php"> Productos</a>
    <a href="categorias.php"> Categorías</a>
    <a href="clientes.php"> Clientes</a>
    <a href="pedidos.php"> Pedidos</a>
  </nav>

  <hr style="border-color: rgba(255,255,255,0.3); margin:12px 10px;">

  <div class="section-title">Accesos</div>
  <nav>
    <a href="../index.php" title="Ir a la tienda"> Ir a la tienda</a>
    <a href="../logout.php" title="Cerrar sesión"> Cerrar sesión</a>
  </nav>
</div>

<!-- 🔹 Contenido principal -->
<div class="main-content">
  <div class="mb-4">
    <h1 class="fw-bold">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> 👋</h1>
    <p class="text-muted">Puedes Aministrar y gestionar en este panel</p>
  </div>

  <!-- 🔹 Tarjetas (sin Bootstrap, centradas y con orden por áreas) -->
  <div class="cards-grid mt-3">
    <div class="card-item pedidos">
      <div class="card text-center">
        <h3> Pedidos</h3>
        <p class="text-muted">Revisa las ventas</p>
        <a href="pedidos.php" class="btn btn-warning">mirar pedidos</a>
      </div>
    </div>

    <div class="card-item productos">
      <div class="card text-center">
        <h3> Productos</h3>
        <p class="text-muted">Gestiona tu inventario</p>
        <a href="productos.php" class="btn btn-primary">Administrar</a>
      </div>
    </div>

    <div class="card-item categorias">
      <div class="card text-center">
        <h3> Categorías</h3>
        <p class="text-muted">Organiza tus productos</p>
        <a href="categorias.php" class="btn btn-success">Administrar</a>
      </div>
    </div>

    <div class="card-item clientes">
      <div class="card text-center">
        <h3> Clientes</h3>
        <p class="text-muted">Adminitracion de usuarios y clientes</p>
        <a href="clientes.php" class="btn btn-secondary">mirar clientes</a>
      </div>
    </div>

    <div class="card-item config">
      <div class="card text-center">
        <h3> Configuración</h3>
        <p class="text-muted">Ajustes del sistema</p>
        <a href="../index.php" class="btn btn-outline-primary">Regresar al inicio</a>
      </div>
    </div>
  </div>

  <footer>
    &copy; <?= date('Y') ?> Zentaury ventana de administradores
  </footer>
</div>

<script>
// Buscador simple del menú lateral (filtra por texto)
const input = document.getElementById('menuSearch');
const links = Array.from(document.querySelectorAll('#sideMenu a'));
input?.addEventListener('input', () => {
  const q = input.value.toLowerCase().trim();
  links.forEach(a => {
    const show = a.textContent.toLowerCase().includes(q);
    a.style.display = show ? 'block' : 'none';
  });
});
</script>
</body>
</html>
