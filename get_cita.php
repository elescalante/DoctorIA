<?php
// Incluye el archivo de configuración para la conexión a la base de datos
require_once 'config.php';

// Establece el encabezado para que el navegador sepa que la respuesta es JSON
header('Content-Type: application/json');

// Obtiene el ID de la cita de la URL (parámetro GET)
$citaId = $_GET['id'] ?? null;

// Verifica si se proporcionó un ID
if (!$citaId) {
    echo json_encode(['error' => 'ID de cita no proporcionado.']);
    exit();
}

try {
    // Prepara la consulta para seleccionar todos los datos de la cita por su ID
    $stmt = $pdo->prepare("SELECT * FROM citas WHERE id = :id");
    // Ejecuta la consulta vinculando el ID
    $stmt->execute([':id' => $citaId]);
    // Obtiene la fila como un array asociativo
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cita) {
        // Formatea la fecha y la hora al formato adecuado para los campos input HTML
        // 'Y-m-d' para <input type="date"> y 'H:i' para <input type="time">
        $cita['fecha_cita'] = date('Y-m-d', strtotime($cita['fecha_cita']));
        $cita['hora_cita'] = date('H:i', strtotime($cita['hora_cita']));

        // Devuelve los datos de la cita en formato JSON
        echo json_encode($cita);
    } else {
        // Si no se encuentra la cita, devuelve un error JSON
        echo json_encode(['error' => 'Cita no encontrada.']);
    }

} catch (PDOException $e) {
    // Manejo de errores en caso de problemas con la base de datos
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
