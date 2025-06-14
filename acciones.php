<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar_cita') {
        $nombre       = $_POST['nombre']       ?? 'N/A';
        $doctor       = $_POST['doctor']       ?? 'N/A';
        $especialidad = $_POST['especialidad'] ?? 'N/A';
        $telefono     = $_POST['telefono']     ?? 'N/A';
        $fecha        = $_POST['fecha']        ?? null;
        $hora         = $_POST['hora']         ?? null;
        $estado       = $_POST['estado']       ?? 'pendiente';
        $notas        = $_POST['notas']        ?? '';

        try {
            // Validar si ya existe una cita para ese doctor en esa fecha y hora
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE fecha_cita = ? AND hora_cita = ? AND doctor = ?");
            $stmtCheck->execute([$fecha, $hora, $doctor]);
            $existe = $stmtCheck->fetchColumn();

            if ($existe > 0) {
                echo "Ya existe una cita asignada para ese doctor en esa fecha y hora.";
                exit;
            }

            $sql = "INSERT INTO citas (nombre_paciente, doctor, especialidad, telefono_paciente, fecha_cita, hora_cita, estado, notas)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $doctor, $especialidad, $telefono, $fecha, $hora, $estado, $notas]);

            // Redirigir a dashboard o devolver JSON si fue petición AJAX
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(["success" => true, "message" => "Cita guardada correctamente"]);
            } else {
                header("Location: dashboard.php?mensaje=ok");
            }
            exit;

        } catch (PDOException $e) {
            error_log("Error al guardar cita: " . $e->getMessage());

            if (!headers_sent()) {
                http_response_code(500);
            }

            echo "ERROR al guardar cita: " . $e->getMessage();
            exit;
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
            error_log("Error al consultar citas: " . $e->getMessage());
            echo "ERROR al consultar citas: " . $e->getMessage();
        }
    }
}
?>
