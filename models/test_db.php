<?php
// test_db.php dentro de la carpeta /models

require_once __DIR__ . '/models_admin.php';  // mismo directorio

// Crear conexión
$db = new AdminModel();
$db->conexion();

// Probar login
echo "<h3>Prueba de conexión y login:</h3>";
$user = $db->login('admin', '123456');
if ($user) {
    echo "✅ Login correcto. Bienvenido: " . $user['usuario'] . "<br>";
} else {
    echo "❌ Error en login<br>";
}

// Probar listado de clientes
echo "<h3>Listado de clientes:</h3>";
$clientes = $db->listarClientes();

if ($clientes) {
    foreach ($clientes as $c) {
        echo $c['id'] . " - " . $c['nombre'] . " - " . $c['correo'] . "<br>";
    }
} else {
    echo "No hay clientes registrados<br>";
}
?>
