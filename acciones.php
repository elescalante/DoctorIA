<?php
include 'config.php';

// ========= GESTIÓN DE ACCIONES POST (FORMULARIOS) ===========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'agregar':
            $stmt = $pdo->prepare("INSERT INTO citas (nombre_paciente, cedula_paciente, email_paciente, telefono_paciente, especialidad, fecha_cita, hora_cita, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')");
            $stmt->execute([
                $_POST['nombre_paciente'],
                $_POST['cedula_paciente'],
                $_POST['email_paciente'],
                $_POST['telefono_paciente'],
                $_POST['especialidad'],
                $_POST['fecha_cita'],
                $_POST['hora_cita']
            ]);
            break;

        case 'actualizar':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("UPDATE citas SET nombre_paciente = ?, cedula_paciente = ?, email_paciente = ?, telefono_paciente = ?, especialidad = ?, fecha_cita = ?, hora_cita = ?, estado = 'reprogramada' WHERE id = ?");
            $stmt->execute([
                $_POST['nombre_paciente'],
                $_POST['cedula_paciente'],
                $_POST['email_paciente'],
                $_POST['telefono_paciente'],
                $_POST['especialidad'],
                $_POST['fecha_cita'],
                $_POST['hora_cita'],
                $id
            ]);

            // ¡AQUÍ LA MAGIA DEL BOT! Notificar al paciente de la reprogramación.
            $nueva_fecha = date("d/m/Y", strtotime($_POST['fecha_cita']));
            $nueva_hora = date("h:i A", strtotime($_POST['hora_cita']));
            $mensaje_bot = "Hola " . $_POST['nombre_paciente'] . ". Te informamos que tu cita ha sido reprogramada para el día $nueva_fecha a las $nueva_hora. ¡Te esperamos!";
            enviar_mensaje_bot($_POST['telefono_paciente'], $mensaje_bot);
            break;
            
        case 'bloquear_rango':
            $fecha = $_POST['fecha_bloqueo'];
            $inicio = new DateTime($fecha . ' ' . $_POST['hora_inicio']);
            $fin = new DateTime($fecha . ' ' . $_POST['hora_fin']);
            $intervalo = new DateInterval('PT1H'); // Intervalo de 1 hora

            $stmt = $pdo->prepare("INSERT INTO citas (nombre_paciente, telefono_paciente, fecha_cita, hora_cita, estado, notas) VALUES ('Horario Bloqueado', 'N/A', ?, ?, 'bloqueado', 'Bloqueado por el doctor')");
            
            while ($inicio < $fin) {
                $fecha_db = $inicio->format('Y-m-d');
                $hora_db = $inicio->format('H:i:s');
                $stmt->execute([$fecha_db, $hora_db]);
                $inicio->add($intervalo);
            }
            break;
    }
}

// ========= GESTIÓN DE ACCIONES GET (BOTONES/LINKS) =================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {
        $sql = '';

        switch ($accion) {
            case 'efectuada':
                $sql = "UPDATE citas SET estado = 'efectuada' WHERE id = ?";
                break;
            case 'cancelar':
                $sql = "UPDATE citas SET estado = 'cancelada' WHERE id = ?";
                // Puedes agregar aquí la notificación del bot para cancelación si lo deseas.
                break;
            case 'desbloquear': // Nueva acción para eliminar un bloqueo
                $sql = "DELETE FROM citas WHERE id = ? AND estado = 'bloqueado'";
                break;
        }

        if ($sql) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
        }
    }
}

// Redireccionar siempre al dashboard para ver los cambios
header("Location: dashboard.php");
exit();
?>
