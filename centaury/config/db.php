<?php
// Mostrar errores en el entorno de desarrollo (puedes quitarlo luego)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ======== CONFIGURACIÓN DE CONEXIÓN =========
$host = 'db-venta.mysql.database.azure.com'; // Host de tu base de datos
$db   = 'db2';     // Nombre de la base de datos
$user = 'admin12';            // Usuario de la base de datos
$pass = 'Alexander22';      // ⚠️ Coloca tu contraseña real de InfinityFree
$charset = 'utf8mb4';

// ======== CONEXIÓN PDO =========
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?>