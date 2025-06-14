<?php

// --- CONFIGURACIÓN DE LA BASE DE DATOS NEON (PostgreSQL) ---

// Obtén las credenciales de Neon de las variables de entorno.
// Asegúrate de configurar PGHOST, PGDATABASE, PGUSER, PGPASSWORD en Render.
// Puedes dejar valores por defecto solo para pruebas locales.
$db_host     = getenv('PGHOST') ?: 'ep-ancient-sun-a52gp43j-pooler.us-east-2.aws.neon.tech';
$db_name     = getenv('PGDATABASE') ?: 'neondb';
$db_user     = getenv('PGUSER') ?: 'neondb_owner';
$db_password = getenv('PGPASSWORD') ?: 'npg_BgiOCvD2nK4X'; // Solo para pruebas locales

// Cadena de conexión DSN para PDO (PostgreSQL)
$dsn = "pgsql:host={$db_host};dbname={$db_name};sslmode=require";

// Variable global para la conexión a la base de datos.
$pdo = null;

try {
    // Crear la conexión PDO
    $pdo = new PDO($dsn, $db_user, $db_password);

    // Configura PDO para lanzar excepciones en caso de errores.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ⚠️ Esta línea soluciona el error del plan cacheado de PostgreSQL
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

} catch (PDOException $e) {
    die("ERROR: La conexión a la base de datos falló. " . $e->getMessage());
}

// --- Lógica de la API del BOT (ejemplo) ---
function enviar_mensaje_bot($telefono, $mensaje) {
    global $pdo;

    // Ejemplo de lógica que podrías usar:
    /*
    try {
        $stmt = $pdo->prepare("INSERT INTO mensajes_bot (telefono, mensaje, fecha_envio) VALUES (?, ?, NOW())");
        $stmt->execute([$telefono, $mensaje]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al enviar mensaje: " . $e->getMessage());
        return false;
    }
    */

    // Simulación por ahora
    return true;
}
?>
