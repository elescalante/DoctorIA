<?php
$host = 'mysql://root:xqLMXvGChCGjhzBdiunLZgTlzBjbFCoy@caboose.proxy.rlwy.net:17715/railway'; // Cambia por el host real
$port = 3306;                                  // Cambia por el puerto real
$dbname = 'railway';
$username = 'root';
$password = 'xqLMXvGChCGjhzBdiunLZgTlzBjbFCoy';

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("❌ ERROR conexión Railway: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
