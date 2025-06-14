<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar_cita') {
        $nombre = $_POST['nombre'] ?? 'N/A';
        $doctor = $_POST['doctor'] ?? 'N/A';
        $especialidad = $_POST['especialidad'] ?? 'N/A'; // evita null en campos NOT NULL
        $telefono = $_POST['telefono'] ?? 'N/A';
        $fecha = $_POST['fecha'] ?? null;
        $hora = $_POST['hora'] ?? null;
        $estado = $_POST['estado'] ?? 'pendiente';
        $notas = $_POST['notas'] ?? null;

        try {
            $sql = "INSERT INTO citas (nombre_paciente, doctor, especialidad, telefono_paciente, fecha_cita, hora_cita, estado, notas)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $doctor, $especialidad, $telefono, $fecha, $hora, $estado, $notas]);
            echo "Cita guardada correctamente.";
        } catch (PDOException $e) {
            echo "ERROR al guardar cita: " . $e->getMessage();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'consultar_citas') {
        try {
            $stmt = $pdo->query("SELECT * FROM citas ORDER BY fecha_cita ASC, hora_cita ASC");
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            header('Content-Type: application/json');
            echo json_encode($citas);
        } catch (PDOException $e) {
            echo "ERROR al consultar citas: " . $e->getMessage();
        }
    }
}
?>
