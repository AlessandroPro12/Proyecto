<?php
session_start();
require_once __DIR__ . '/../models/models_admin.php';

$admin = new AdminModel();
$admin->conexion();

$mensaje_error = "";
$mensaje_exito = "";

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido. Recarga la página.");
    }

    $accion = $_POST['accion'] ?? '';

    // LOGIN
    if ($accion === 'login') {
        $usuario  = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $user = $admin->login($usuario, $password);

        if ($user) {
            // Guardamos datos importantes en sesión
            $_SESSION['usuario']     = $user['usuario'];
            $_SESSION['rol']         = $user['rol'];
            $_SESSION['id_usuario']  = $user['id'];          // id de la tabla usuarios
            $_SESSION['cliente_id']  = $user['cliente_id'];  // id de la tabla clientes

            if ($user['rol'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: usuario_inicio.php');
            }
            exit;
        } else {
            $mensaje_error = "Usuario o contraseña incorrectos.";
        }
    }

    // REGISTRO
    if ($accion === 'registrar') {
        $usuario  = trim($_POST['usuario'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (strlen($usuario) < 3 || strlen($password) < 6) {
            $mensaje_error = "El usuario o la contraseña son demasiado cortos.";
        } else {
            $ok = $admin->registrarUsuario($usuario, $email, $password, 'usuario');
            if ($ok) {
                $mensaje_exito = "Cuenta creada correctamente. Ahora puedes iniciar sesión.";
            } else {
                $mensaje_error = "No se pudo registrar: " . $admin->error_message;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login / Registro - Sistema de Ventas</title>
    <style>
        <?php echo file_get_contents('../assets/css/login.css'); ?>

        .hidden-form { display: none; opacity: 0; transform: scale(0.98); transition: all 0.3s ease; }
        .active-form { display: block; opacity: 1; transform: scale(1); transition: all 0.3s ease; }

        .input-wrapper label {
            position: absolute;
            left: 16px;
            top: 14px;
            color: #64748b;
            font-size: 16px;
            pointer-events: none;
            transition: all 0.2s ease;
        }
        .input-wrapper input:focus + label,
        .input-wrapper input.has-value + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #6366f1;
            background-color: #fff;
            padding: 0 4px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="login-header">
            <h2 id="titulo-form">Bienvenido</h2>
            <p id="subtitulo-form">Accede a tu cuenta</p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje_error): ?>
            <div class="error-message show"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <?php if ($mensaje_exito): ?>
            <div class="success-message show"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php endif; ?>

        <!-- FORM LOGIN -->
        <form method="POST" id="formLogin" class="active-form">
            <input type="hidden" name="accion" value="login">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <div class="input-wrapper">
                    <input type="text" name="usuario" required>
                    <label>Usuario</label>
                </div>
            </div>

            <div class="form-group password-wrapper">
                <div class="input-wrapper">
                    <input type="password" name="password" required>
                    <label>Contraseña</label>
                </div>
            </div>

            <button type="submit" class="login-btn">Iniciar sesión</button>

            <div class="signup-link">
                <p>¿No tienes cuenta? <a href="#" id="mostrarRegistro">Crear cuenta</a></p>
            </div>
        </form>

        <!-- FORM REGISTRO -->
        <form method="POST" id="formRegistro" class="hidden-form">
            <input type="hidden" name="accion" value="registrar">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <div class="input-wrapper">
                    <input type="text" name="usuario" required minlength="3">
                    <label>Usuario</label>
                </div>
            </div>

            <div class="form-group">
                <div class="input-wrapper">
                    <input type="email" name="email" required>
                    <label>Correo</label>
                </div>
            </div>

            <div class="form-group password-wrapper">
                <div class="input-wrapper">
                    <input type="password" name="password" required minlength="6">
                    <label>Contraseña</label>
                </div>
            </div>

            <button type="submit" class="login-btn">Registrarme</button>

            <div class="signup-link">
                <p>¿Ya tienes cuenta? <a href="#" id="mostrarLogin">Iniciar sesión</a></p>
            </div>
        </form>
    </div>
</div>

<script>
const formLogin = document.getElementById('formLogin');
const formRegistro = document.getElementById('formRegistro');
const titulo = document.getElementById('titulo-form');
const subtitulo = document.getElementById('subtitulo-form');

document.getElementById('mostrarRegistro').onclick = e => {
    e.preventDefault();
    formLogin.classList.remove('active-form');
    formLogin.classList.add('hidden-form');
    formRegistro.classList.remove('hidden-form');
    formRegistro.classList.add('active-form');
    titulo.textContent = "Crea tu cuenta";
    subtitulo.textContent = "Regístrate para comenzar";
};

document.getElementById('mostrarLogin').onclick = e => {
    e.preventDefault();
    formRegistro.classList.remove('active-form');
    formRegistro.classList.add('hidden-form');
    formLogin.classList.remove('hidden-form');
    formLogin.classList.add('active-form');
    titulo.textContent = "Bienvenido";
    subtitulo.textContent = "Accede a tu cuenta";
};

// Etiquetas flotantes
document.querySelectorAll('.input-wrapper input').forEach(input => {
  input.addEventListener('input', () => {
    input.classList.toggle('has-value', input.value.trim() !== '');
  });
});
</script>

</body>
</html>
