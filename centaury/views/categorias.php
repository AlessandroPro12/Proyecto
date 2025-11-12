<?php
// categorias.php (EN views/)
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ajusta la ruta según tu estructura
require_once __DIR__ . '/../config/db.php';

/* ========= RUTAS CONSISTENTES =========
   - Guardar SIEMPRE en /img/categorias (raíz del proyecto)
   - Desde views/ para mostrar: ../img/categorias
*/
define('ROOT_PATH', dirname(__DIR__));                 // .../tu-proyecto
define('CATS_IMG_DIR', ROOT_PATH . '/img/categorias'); // ruta en disco
define('CATS_IMG_URL_FROM_VIEWS', '../img/categorias'); // URL desde /views

// (Opcional) Solo admin
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { header("Location: login.php"); exit(); }

function subirImagen(string $campo): ?string {
  if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) return null;

  if (!is_dir(CATS_IMG_DIR)) mkdir(CATS_IMG_DIR, 0777, true);

  $permitidas = ['jpg','jpeg','png','gif','webp'];
  $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $permitidas)) throw new Exception('Formato de imagen no permitido');

  $nombre = uniqid('cat_', true) . '.' . $ext;             // guardamos SOLO nombre
  $destino = CATS_IMG_DIR . DIRECTORY_SEPARATOR . $nombre;

  if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
    throw new Exception('No se pudo guardar la imagen');
  }
  return $nombre;
}

function borrarImagen(?string $archivo): void {
  if (!$archivo) return;
  $path = CATS_IMG_DIR . '/' . basename($archivo);
  if (is_file($path)) @unlink($path);
}

$msg = null;
$err = null;

// ---- Acciones ----
try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
      $nombre = trim($_POST['nombre'] ?? '');
      if ($nombre === '') throw new Exception('El nombre es obligatorio');

      $img = subirImagen('imagen'); // opcional
      $stmt = $pdo->prepare("INSERT INTO categorias (nombre, imagen) VALUES (:nombre, :imagen)");
      $stmt->execute([':nombre' => $nombre, ':imagen' => $img]);
      $msg = 'Categoría creada correctamente.';

    } elseif ($accion === 'actualizar') {
      $id = (int)($_POST['id'] ?? 0);
      $nombre = trim($_POST['nombre'] ?? '');
      if ($id <= 0 || $nombre === '') throw new Exception('Datos inválidos');

      $old = $pdo->prepare("SELECT imagen FROM categorias WHERE id = ?");
      $old->execute([$id]);
      $actual = $old->fetch(PDO::FETCH_ASSOC);
      if (!$actual) throw new Exception('Categoría no encontrada');

      $nuevaImg = subirImagen('imagen'); // si no suben, queda null
      if ($nuevaImg) {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = :nombre, imagen = :imagen WHERE id = :id");
        $stmt->execute([':nombre' => $nombre, ':imagen' => $nuevaImg, ':id' => $id]);
        borrarImagen($actual['imagen'] ?? null);
      } else {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = :nombre WHERE id = :id");
        $stmt->execute([':nombre' => $nombre, ':id' => $id]);
      }
      $msg = 'Categoría actualizada.';

    } elseif ($accion === 'eliminar') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id <= 0) throw new Exception('ID inválido');

      $old = $pdo->prepare("SELECT imagen FROM categorias WHERE id = ?");
      $old->execute([$id]);
      $fila = $old->fetch(PDO::FETCH_ASSOC);
      borrarImagen($fila['imagen'] ?? null);

      $del = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
      $del->execute([$id]);
      $msg = 'Categoría eliminada.';
    }
  }
} catch (Throwable $e) {
  $err = $e->getMessage();
}

// Para edición por GET ?edit=ID
$editData = null;
if (isset($_GET['edit'])) {
  $idE = (int)$_GET['edit'];
  if ($idE > 0) {
    $q = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $q->execute([$idE]);
    $editData = $q->fetch(PDO::FETCH_ASSOC);
  }
}

// Listado
$cats = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Categorías - Mercado Global</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f8fafc;color:#1e293b;font-family:'Segoe UI',Roboto,sans-serif}
.container{max-width:1000px}
.card{border-radius:12px;box-shadow:0 3px 8px rgba(0,0,0,.06)}
.card-header{background:#6366f1;color:#fff;font-weight:600;border-radius:12px 12px 0 0!important}
.table td,.table th{vertical-align:middle}
img.thumb{width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb}
.btn-primary{background:#4f46e5;border:none}
.btn-primary:hover{background:#4338ca}
.btn-danger{background:#ef4444;border:none}
.btn-danger:hover{background:#dc2626}
.btn-secondary{background:#64748b;border:none}
.btn-secondary:hover{background:#475569}
</style>
</head>
<body class="py-4">
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">🏷️ Administración de Categorías</h3>
    <a class="btn btn-secondary" href="dashboard.php">⬅ Volver</a>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger">Error: <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Form crear/editar -->
  <div class="card mb-4">
    <div class="card-header"><?= $editData ? '✏️ Editar categoría' : '➕ Nueva categoría' ?></div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="<?= $editData ? 'actualizar' : 'crear' ?>">
        <?php if ($editData): ?><input type="hidden" name="id" value="<?= (int)$editData['id'] ?>"><?php endif; ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nombre</label>
            <input type="text" name="nombre" class="form-control" required
                   value="<?= htmlspecialchars($editData['nombre'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Imagen <?= $editData ? '(opcional para cambiar)' : '' ?></label>
            <input type="file" name="imagen" class="form-control">
          </div>
          <?php if (!empty($editData['imagen'])): ?>
            <div class="col-12">
              <small class="text-muted">Imagen actual:</small><br>
              <img class="thumb" src="<?= CATS_IMG_URL_FROM_VIEWS . '/' . htmlspecialchars($editData['imagen']) ?>" alt="actual">
            </div>
          <?php endif; ?>
        </div>

        <div class="mt-3">
          <button class="btn btn-primary"><?= $editData ? 'Guardar cambios' : 'Crear categoría' ?></button>
          <?php if ($editData): ?><a href="categorias.php" class="btn btn-secondary ms-2">Cancelar</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Listado -->
  <div class="card">
    <div class="card-header">📋 Categorías</div>
    <div class="card-body">
      <?php if (count($cats)): ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr><th>ID</th><th>Imagen</th><th>Nombre</th><th>Acciones</th></tr>
            </thead>
            <tbody>
              <?php foreach ($cats as $c): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td>
                    <img class="thumb"
                         src="<?= CATS_IMG_URL_FROM_VIEWS . '/' . htmlspecialchars($c['imagen'] ?: 'default.png') ?>"
                         alt="">
                  </td>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td>
                    <a class="btn btn-sm btn-warning text-dark" href="categorias.php?edit=<?= (int)$c['id'] ?>">✏️ Editar</a>
                    <form class="d-inline" method="post" onsubmit="return confirm('¿Eliminar esta categoría?');">
                      <input type="hidden" name="accion" value="eliminar">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button class="btn btn-sm btn-danger">🗑️ Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted mb-0">Aún no hay categorías.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
