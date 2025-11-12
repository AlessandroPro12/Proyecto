<?php
session_start();

// Después de verificar el login exitoso (esto lo haces en tu flujo):
$_SESSION['usuario'] = $user['usuario'] ?? null;
$_SESSION['rol'] = $user['rol'] ?? null; // "admin" o "usuario"
$_SESSION['id'] = $user['id'] ?? null;

require_once '../models/models_admin.php';

$mensaje_error = "";
$mensaje_exito = "";

$admin = new AdminModel();
$conn = $admin->conexion();

// 🧩 Token CSRF (previene ataques cross-site)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido. Recarga la página.");
    }

    // 🧩 LOGIN
    if ($_POST['accion'] === 'login') {
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['password']);
        $user = $admin->login($usuario, $password);

        if ($user) {
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['id'] = $user['id'];

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

    // 🧩 REGISTRO (SIN reCAPTCHA)
    if ($_POST['accion'] === 'registrar') {
        $usuario = trim($_POST['usuario']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Validaciones básicas
        if (strlen($usuario) < 3 || strlen($password) < 6) {
            $mensaje_error = "El usuario o la contraseña son demasiado cortos.";
        } else {
            $ok = $admin->registrarUsuario($usuario, $email, $password, 'usuario');
            if ($ok) {
                $mensaje_exito = "Cuenta creada correctamente. Ahora puedes iniciar sesión.";
            } else {
                $mensaje_error = "No se pudo registrar (quizá el usuario o correo ya existen).";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <title>Login / Registro - Sistema de Ventas</title>
    <style>
        
        <?php echo file_get_contents('../assets/css/login.css'); ?>

        /* Transiciones suaves */
        .hidden-form { display: none; opacity: 0; transform: scale(0.98); transition: all 0.3s ease; }
        .active-form { display: block; opacity: 1; transform: scale(1); transition: all 0.3s ease; }

        /* Ajuste visual para etiquetas flotantes */
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
            <h2 id="titulo-form">¡Hola de nuevo!</h2>
            <p id="subtitulo-form">Accede a tu cuenta</p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje_error): ?>
            <div class="error-message show"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <?php if ($mensaje_exito): ?>
            <div class="success-message show"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php endif; ?>

        <!-- 🔹 FORMULARIO LOGIN -->
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

        <!-- 🔹 FORMULARIO REGISTRO (sin reCAPTCHA) -->
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

</body>
</html>
