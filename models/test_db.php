<?php
<<<<<<< HEAD
// test_db.php dentro de la carpeta /models

require_once __DIR__ . '/models_admin.php';  // mismo directorio
=======
include "models_admin.php";
>>>>>>> 05b0f60fb022e9e47d1d995e3f29c3431e22f156

// Crear conexión
$db = new AdminModel();
$db->conexion();

<<<<<<< HEAD
// Probar login
echo "<h3>Prueba de conexión y login:</h3>";
$user = $db->login('admin', '123456');
=======
// Probar login con usuario admin
echo "<h3>Prueba de conexión y login:</h3>";
$user = $db->login("admin", "123456"); // pon aquí un usuario que exista en tu tabla admins
>>>>>>> 05b0f60fb022e9e47d1d995e3f29c3431e22f156
if ($user) {
    echo "✅ Login correcto. Bienvenido: " . $user['usuario'] . "<br>";
} else {
    echo "❌ Error en login<br>";
}

// Probar listado de clientes
echo "<h3>Listado de clientes:</h3>";
$clientes = $db->listarClientes();
<<<<<<< HEAD

if ($clientes) {
    foreach ($clientes as $c) {
        echo $c['id'] . " - " . $c['nombre'] . " - " . $c['correo'] . "<br>";
=======
if ($clientes) {
    foreach ($clientes as $c) {
        echo $c['id']." - ".$c['nombre']." - ".$c['correo']."<br>";
>>>>>>> 05b0f60fb022e9e47d1d995e3f29c3431e22f156
    }
} else {
    echo "No hay clientes registrados<br>";
}
?>
