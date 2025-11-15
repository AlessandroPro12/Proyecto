<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/models_admin.php';
$admin = new AdminModel();
$admin->conexion();
$clientes = $admin->listarClientes();

// --- AGREGAR CLIENTE ---
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];

    $admin->agregarCliente($nombre, $telefono, $correo, $direccion);
    header("Location: clientes.php");
    exit;
}

// --- ACTUALIZAR CLIENTE ---
if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $admin->actualizarCliente($id, $nombre, $telefono, $correo, $direccion);
    header("Location: clientes.php");
    exit;
}

// --- ELIMINAR CLIENTE ---
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $admin->eliminarCliente($id);
    header("Location: clientes.php");
    exit;
}

// --- LISTAR CLIENTES ---
$clientes = $admin->listarClientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
        :root{
            --bg-start: #0b1324;
            --bg-end: #2b2350;
            --panel-bg: rgba(255,255,255,0.98);
            --accent-start: #3b5bfd;
            --accent-end: #8a43ff;
            --muted: #6c757d;
            --danger: #ef4444;
            --warning: #f59e0b;
            --soft: #f6f8ff;
        }

        *{ box-sizing: border-box; font-family: 'Montserrat', sans-serif; }

        body{
            margin: 0;
            background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-end) 100%);
            color: #0f172a;
            min-height: 100vh;
            padding: 28px 20px;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }
        .logout,
        .nav-logout,
        .btn-logout,
        a[href*="logout"],
        a[href*="logout.php"],
        a[href*="salir"],
        a[href*="salir.php"],
        a[href*="cerrar_sesion"],
        a[href*="cerrar.php"] {
            display: none !important;
        }

        
        .page-wrap{
            max-width: 1100px;
            margin: 0 auto;
        }

        .card{
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 18px 40px rgba(11,19,36,0.45);
            overflow: hidden;
        }

      
        .panel-header{
            background: linear-gradient(90deg, #02031bff, #323461ff);
            color: #fff;
            padding: 18px 22px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 8px 30px rgba(33,18,77,0.35);
            margin-bottom: 20px;
        }

        .panel-header .logo {
            background: rgba(255,255,255,0.12);
            width: 56px;
            height: 56px;
            border-radius: 10px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:22px;
            box-shadow: inset 0 -6px 18px rgba(0,0,0,0.06);
        }

        .panel-header h1{
            margin:0;
            font-size:1.5rem;
            font-weight:700;
            letter-spacing: -0.2px;
        }

        .panel-header p{
            margin:0;
            opacity:0.9;
            font-weight:500;
            font-size:0.95rem;
        }

        /* Contenido interno */
        .content-grid{
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
        }

        /* Form card */
        .form-card{
            background: linear-gradient(180deg, rgba(246,248,255,1), rgba(251,252,255,0.98));
            padding: 16px;
            border-radius: 12px;
            border: 1px solid rgba(59,91,253,0.06);
            box-shadow: 0 10px 24px rgba(15,23,42,0.06);
        }

        label{ display:block; font-weight:600; color:#24303f; margin-top:8px; font-size:0.95rem; }
        input[type="text"], input[type="email"], textarea{
            width:100%; padding:10px 12px; margin-top:6px; border-radius:8px;
            border:1px solid #e6eef8; background:#fff; font-size:0.95rem; color:#0f172a;
        }
        textarea{ min-height:90px; resize:vertical; }

        /* Botones */
        .btn{
            display:inline-block; padding:10px 14px; border-radius:10px; font-weight:700;
            color:#fff; text-decoration:none; border:none; cursor:pointer;
        }

        .btn-primary{
            background: linear-gradient(90deg, var(--accent-start), var(--accent-end));
            box-shadow: 0 10px 30px rgba(59,91,253,0.18);
            transition: transform .14s ease, box-shadow .14s ease;
        }
        .btn-primary:hover{ transform: translateY(-3px); }

        .btn-muted{
            background: linear-gradient(90deg, #6c757d, #52595f);
        }

        /* Tabla estilo limpio */
  

        table{
            width:100%; border-collapse: collapse; background:transparent;
        }

        th, td{
            padding:14px 12px; text-align:left; vertical-align:middle; font-size:0.95rem;
            border-bottom: 1px solid rgba(15,23,42,0.06);
        }

        th{
            background: linear-gradient(90deg, rgba(244,247,255,1), rgba(236,240,255,1));
            font-weight:700; color:#0b1324;
        }

        tr:hover td{ background: rgba(255,255,255,0.9); transform: translateX(3px); transition: all .18s ease; }

         .acciones a{
            display:inline-block; padding:8px 10px; border-radius:8px; font-weight:700; text-decoration:none; color:#fff; margin-right:4px;
            font-size:0.9rem;
        }
        .editar{ background: linear-gradient(90deg, #f59e0b, #f2a21b); color:#081018; }
        .eliminar{ background: linear-gradient(90deg, #ef4444, #d233b3); }

        .empty-row{
            text-align:center; padding:22px; color:#475569; background:#fff; border-radius:8px; font-weight:600;
        }
        
    </style>
</head>
<body>

<main class="contenido page-wrap">
    <div class="card">
        <div class="panel-header">
            <div class="logo">👥</div>
            <div>
                <h1>Gestión de Clientes</h1>
                <p>Lista, agrega y edita clientes — diseño limpio y consistente</p>
            </div>
        </div>

        <div class="content-grid">
            <!-- Formulario de agregar -->
            <div class="form-card">
                <a href="dashboard.php" class="btn btn-muted" style="margin-bottom:10px; display:inline-block;">← Volver al panel</a>

                <h2 style="margin:6px 0 10px; color:var(--bg-end);">Agregar Cliente</h2>
                <form method="POST" style="margin-top:6px;">
                    <input type="hidden" name="accion" value="agregar">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" required>
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" required>
                    <label>Correo:</label>
                    <input type="email" name="correo" required>
                    <label>Dirección:</label>
                    <textarea name="direccion"></textarea>
                    <div style="margin-top:12px;">
                        <button class="btn btn-primary" type="submit">Agregar</button>
                    </div>
                </form>
            </div>

            <!-- Tabla de clientes -->
            <div class="table-wrap">
                <h2 style="margin:8px 6px 12px; color:var(--bg-end);">Lista de Clientes</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                    <?php if ($clientes): ?>
                        <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><?= htmlspecialchars($c['telefono']) ?></td>
                            <td><?= htmlspecialchars($c['correo']) ?></td>
                            <td><?= htmlspecialchars($c['direccion']) ?></td>
                            <td class="acciones">
                                <a class="editar" href="clientes.php?editar=<?= $c['id'] ?>">Editar</a>
                                <a class="eliminar" href="clientes.php?eliminar=<?= $c['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este cliente?');">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="empty-row">No hay clientes registrados.</td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Formulario de edición (mantengo la lógica original, solo ajusto estilos mínimos) -->
            <?php
            // --- FORMULARIO DE EDICIÓN ---
            if (isset($_GET['editar'])):
                $idEditar = $_GET['editar'];
                $clienteEditar = null;
                foreach ($clientes as $cli) {
                    if ($cli['id'] == $idEditar) $clienteEditar = $cli;
                }
                if ($clienteEditar):
            ?>
            <div class="form-card" style="margin-top:6px;">
                <h2 style="margin:6px 0 10px; color:var(--bg-end);">Editar Cliente</h2>
                <form method="POST">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id" value="<?= $clienteEditar['id'] ?>">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($clienteEditar['nombre']) ?>" required>
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($clienteEditar['telefono']) ?>" required>
                    <label>Correo:</label>
                    <input type="email" name="correo" value="<?= htmlspecialchars($clienteEditar['correo']) ?>" required>
                    <label>Dirección:</label>
                    <textarea name="direccion"><?= htmlspecialchars($clienteEditar['direccion']) ?></textarea>
                    <div style="margin-top:12px;">
                        <button class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                </form>
            </div>
            <?php endif; endif; ?>
        </div>
    </div>
</main>

</body>
</html>