<?php
<<<<<<< HEAD
// Mostrar errores en desarrollo
=======
// Mostrar errores en el entorno de desarrollo (puedes quitarlo luego)
>>>>>>> 05b0f60fb022e9e47d1d995e3f29c3431e22f156
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

<<<<<<< HEAD
// ====== CONFIGURACIÓN DE CONEXIÓN LOCAL (APPSERV) ======
$host = 'localhost';     // <─ importante
$db   = 'db2';           // nombre de la BD que creaste en phpMyAdmin
$user = 'root';          // usuario MySQL de AppServ (por defecto root)
$pass = '123456789';              // contraseña MySQL (vacía si no pusiste ninguna)
$charset = 'utf8mb4';

// Si tu MySQL usa otro puerto (ej: 3307), usar:
/// $host = '127.0.0.1:3307';

=======
// ======== CONFIGURACIÓN DE CONEXIÓN =========
$host = 'db-venta.mysql.database.azure.com'; // Host de tu base de datos
$db   = 'db2';     // Nombre de la base de datos
$user = 'admin12';            // Usuario de la base de datos
$pass = 'Alexander22';      // ⚠️ Coloca tu contraseña real de InfinityFree
$charset = 'utf8mb4';

// ======== CONEXIÓN PDO =========
>>>>>>> 05b0f60fb022e9e47d1d995e3f29c3431e22f156
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
<<<<<<< HEAD
    die("❌ Error al conectar a la base de datos: " . $e->getMessage());
}
=======
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?>
>>>>>>> 05b0f60fb022e9e47d1d995e3f29c3431e22f156
