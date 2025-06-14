<?php

// --- CONFIGURACIÓN DE LA BASE DE DATOS NEON (PostgreSQL) ---

// NO expongas tus credenciales de Neon directamente en el navegador ni las subas a Git.
// Utiliza variables de entorno para la información sensible como la contraseña.
// Render te permite configurar estas variables de entorno de forma segura.

// Obtén las credenciales de Neon de las variables de entorno.
// Asegúrate de configurar PGHOST, PGDATABASE, PGUSER, PGPASSWORD en Render.
// Si los nombres de tus variables de entorno en Render son diferentes, ajústalos aquí.
$db_host     = getenv('PGHOST') ?: 'ep-ancient-sun-a52gp43j-pooler.us-east-2.aws.neon.tech';
$db_name     = getenv('PGDATABASE') ?: 'neondb';
$db_user     = getenv('PGUSER') ?: 'neondb_owner';
$db_password = getenv('PGPASSWORD') ?: 'npg_BgiOCvD2nK4X'; // ¡IMPORTANTE! Usar getenv() en producción. El valor directo es solo para prueba local.

// Cadena de conexión DSN para PDO (PostgreSQL)
// 'sslmode=require' es crucial porque Neon fuerza las conexiones seguras (SSL).
$dsn = "pgsql:host={$db_host};dbname={$db_name};sslmode=require";

// Variable global para la conexión a la base de datos.
// Se inicializa como null y se llena dentro del bloque try.
$pdo = null;

try {
    // Crear la conexión PDO
    $pdo = new PDO($dsn, $db_user, $db_password);

    // Configura PDO para lanzar excepciones en caso de errores.
    // Esto hace que el manejo de errores sea mucho más fácil y robusto.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Puedes establecer el charset a UTF-8. Para PostgreSQL con PDO,
    // suele manejarse bien por defecto, pero puedes confirmarlo si es necesario.
    // $pdo->exec("SET NAMES 'UTF8'"); // Más común en MySQL, no siempre necesario en PgSQL

} catch (PDOException $e) {
    // Si la conexión falla, detén la ejecución y muestra el mensaje de error.
    die("ERROR: La conexión a la base de datos falló. " . $e->getMessage());
}

// --- Lógica de la API del BOT (ejemplo) ---
// Esta función ahora puede usar la conexión $pdo para interactuar con la base de datos.

function enviar_mensaje_bot($telefono, $mensaje) {
    // Accede a la conexión PDO global
    global $pdo;

    // --- AQUÍ IRÍA TU LÓGICA PARA INTERACTUAR CON LA BASE DE DATOS Y LA API REAL DEL BOT ---
    // Por ejemplo, insertar un mensaje en una tabla:
    /*
    try {
        $stmt = $pdo->prepare("INSERT INTO mensajes_bot (telefono, mensaje, fecha_envio) VALUES (?, ?, NOW())");
        $stmt->execute([$telefono, $mensaje]);
        // Lógica para llamar a la API externa (WhatsApp, Telegram, etc.)
        // Si todo es exitoso, devuelve true.
        return true;
    } catch (PDOException $e) {
        error_log("Error al enviar mensaje y guardar en DB: " . $e->getMessage());
        return false; // Indica fallo
    }
    */

    // Para la demo, simplemente devolvemos true (como en tu código original).
    // En un caso real, aquí iría el código para enviar el mensaje real y gestionar DB.
    return true;
}

?>
