<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'] ?? '';

    if ($accion == 'agregar') {
        // ... (Tu código existente para agregar citas normales) ...
    } elseif ($accion == 'actualizar') {
        // ... (Tu código existente para actualizar citas) ...
    } elseif ($accion == 'efectuada' || $accion == 'cancelar' || $accion == 'desbloquear') {
        // ... (Tu código existente para cambiar estados de citas) ...
    } elseif ($accion == 'bloquear_rango') {
        $fecha_bloqueo = $_POST['fecha_bloqueo'] ?? '';
        $hora_inicio_str = $_POST['hora_inicio'] ?? '';
        $hora_fin_str = $_POST['hora_fin'] ?? '';

        // Validar que los datos mínimos estén presentes
        if (empty($fecha_bloqueo) || empty($hora_inicio_str) || empty($hora_fin_str)) {
            header("Location: dashboard.php?error=Datos incompletos para bloquear horario.");
            exit();
        }

        // Convertir horas a formato adecuado si es necesario (generalmente vienen como HH:MM)
        // Y asegurar que sean horas válidas.
        $hora_inicio = date("H:i:s", strtotime($hora_inicio_str));
        $hora_fin = date("H:i:s", strtotime($hora_fin_str));

        // Asumimos intervalos de 1 hora, basados en step="3600" del input time
        $interval_seconds = 3600;

        // Iniciar una transacción para asegurar que todas las inserciones se hagan o ninguna
        $pdo->beginTransaction();

        try {
            // Loop para cada hora en el rango
            for ($current_timestamp = strtotime($fecha_bloqueo . ' ' . $hora_inicio);
                 $current_timestamp <= strtotime($fecha_bloqueo . ' ' . $hora_fin);
                 $current_timestamp += $interval_seconds) {

                $fecha_cita = date("Y-m-d", $current_timestamp);
                $hora_cita = date("H:i:s", $current_timestamp);

                // Preparar la consulta INSERT
                $stmt = $pdo->prepare("INSERT INTO citas (
                    nombre_paciente,
                    cedula_paciente,
                    email_paciente,
                    telefono_paciente,
                    especialidad,
                    fecha_cita,
                    hora_cita,
                    estado
                ) VALUES (
                    :nombre_paciente,
                    :cedula_paciente,
                    :email_paciente,
                    :telefono_paciente,
                    :especialidad,
                    :fecha_cita,
                    :hora_cita,
                    :estado
                )");

                // *** ESTO ES LO CRÍTICO: Definir valores para las columnas NOT NULL ***
                // Que no vienen del formulario de bloqueo.
                $nombre_placeholder = "Horario Bloqueado"; // O lo que prefieras
                $cedula_placeholder = null; // Cédula puede ser NULL en DB
                $email_placeholder = null; // Email puede ser NULL en DB
                $telefono_placeholder = "N/A"; // Teléfono es NOT NULL, así que necesita un valor
                $especialidad_placeholder = "Bloqueado"; // Especialidad es NOT NULL, así que necesita un valor
                $estado_bloqueado = "bloqueado";

                // Ejecutar la consulta con los valores
                $stmt->execute([
                    ':nombre_paciente' => $nombre_placeholder,
                    ':cedula_paciente' => $cedula_placeholder,
                    ':email_paciente' => $email_placeholder,
                    ':telefono_paciente' => $telefono_placeholder, // Aquí se envía "N/A"
                    ':especialidad' => $especialidad_placeholder, // Aquí se envía "Bloqueado"
                    ':fecha_cita' => $fecha_cita,
                    ':hora_cita' => $hora_cita,
                    ':estado' => $estado_bloqueado
                ]);
            }

            $pdo->commit(); // Confirmar todas las inserciones si no hubo errores
            header("Location: dashboard.php?success=Horario(s) bloqueado(s) exitosamente.");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack(); // Deshacer todas las inserciones si hubo un error
            die("ERROR al bloquear horario(s): " . $e->getMessage());
        }
    }
} else {
    // Redireccionar si no es una solicitud POST válida
    header("Location: dashboard.php");
    exit();
}

// Puedes añadir aquí el cierre de la conexión si es necesario, aunque PDO la maneja.
// $pdo = null;
?>
