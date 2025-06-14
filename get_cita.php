<?php
header('Content-Type: application/json'); // Asegura que la respuesta sea JSON
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cita = $result->fetch_assoc();
        echo json_encode($cita);
    } else {
        echo json_encode(['error' => 'Cita no encontrada.']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'ID de cita no válido.']);
}

$conn->close();
?>