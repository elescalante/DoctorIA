<?php
// CONFIGURACIÓN DE LA BASE DE DATOS
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'rock2807_admin'); // Tu usuario de la base de datos
define('DB_PASSWORD', 'arpa4cvrarpa4cvr'); // Tu contraseña
define('DB_NAME', 'rock2807_consultorio_db');

// Crear conexión
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("ERROR: La conexión a la base de datos falló. " . $conn->connect_error);
}

// Establecer el charset a UTF-8 para evitar problemas con tildes y eñes
$conn->set_charset("utf8mb4");

// Lógica de la API del BOT (ejemplo)
// En un caso real, esta función se conectaría a la API de WhatsApp, Telegram, etc.
function enviar_mensaje_bot($telefono, $mensaje) {
    // Esta es una simulación. Aquí iría el código real para enviar el mensaje.
    // Por ejemplo, usando cURL para llamar a una API.
    // file_put_contents('bot_log.txt', "Para: $telefono | Mensaje: $mensaje\n", FILE_APPEND);
    // Para la demo, simplemente devolvemos true.
    return true;
}
?>