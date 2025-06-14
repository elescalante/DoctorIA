<?php
// Incluye el archivo de configuración, que ahora define $pdo
require_once 'config.php';

// Asegúrate de que la solicitud es POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'] ?? ''; // Obtener la acción del formulario

    // --- Lógica para AÑADIR NUEVA CITA ---
    if ($accion == 'agregar') {
        // 1. Recoger y sanear los datos del formulario
        $nombre_paciente = trim($_POST['nombre_paciente'] ?? '');
        $cedula_paciente = trim($_POST['cedula_paciente'] ?? null); // Puede ser NULL en DB si no es requerido
        $email_paciente = trim($_POST['email_paciente'] ?? null);   // Puede ser NULL en DB si no es requerido
        $telefono_paciente = trim($_POST['telefono_paciente'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $fecha_cita = $_POST['fecha_cita'] ?? '';
        $hora_cita = $_POST['hora_cita'] ?? '';

        // 2. Validación básica de campos requeridos (basado en tu schema NOT NULL)
        if (empty($nombre_paciente) || empty($telefono_paciente) || empty($especialidad) || empty($fecha_cita) || empty($hora_cita)) {
            // Si falta algún campo requerido, redirigir con un error
            header("Location: dashboard.php?error=" . urlencode("Por favor, complete todos los campos requeridos para la cita."));
            exit();
        }

        // 3. Definir el estado inicial de la cita (ej. 'pendiente' por defecto)
        $estado = 'pendiente';

        try {
            // 4. Preparar la consulta SQL para insertar la nueva cita
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

            // 5. Ejecutar la consulta, pasando los valores como un array
            $stmt->execute([
                ':nombre_paciente' => $nombre_paciente,
                ':cedula_paciente' => !empty($cedula_paciente) ? $cedula_paciente : null, // Asegura NULL si está vacío
                ':email_paciente' => !empty($email_paciente) ? $email_paciente : null,     // Asegura NULL si está vacío
                ':telefono_paciente' => $telefono_paciente,
                ':especialidad' => $especialidad,
                ':fecha_cita' => $fecha_cita,
                ':hora_cita' => $hora_cita,
                ':estado' => $estado
            ]);

            // 6. Redireccionar al dashboard con un mensaje de éxito
            header("Location: dashboard.php?success=" . urlencode("Cita añadida exitosamente."));
            exit();

        } catch (PDOException $e) {
            // 7. Manejo de errores en caso de que la inserción falle
            // Puedes añadir lógica específica para ciertos códigos de error de SQL
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'duplicate key value violates unique constraint') !== false || $e->getCode() == '23505') {
                 $errorMessage = "Ya existe una cita o un horario bloqueado para esa fecha y hora.";
            } else if (strpos($errorMessage, 'violates not-null constraint') !== false || $e->getCode() == '23502') {
                 $errorMessage = "Error: Faltan datos requeridos para la cita. Revise los campos.";
            }
            header("Location: dashboard.php?error=" . urlencode("Error al añadir la cita: " . $errorMessage));
            exit();
        }
    }

    // --- Lógica para BLOQUEAR RANGO DE HORARIO (dejamos la versión que ya funciona) ---
    elseif ($accion == 'bloquear_rango') {
        $fecha_bloqueo = $_POST['fecha_bloqueo'] ?? '';
        $hora_inicio_str = $_POST['hora_inicio'] ?? '';
        $hora_fin_str = $_POST['hora_fin'] ?? '';

        if (empty($fecha_bloqueo) || empty($hora_inicio_str) || empty($hora_fin_str)) {
            header("Location: dashboard.php?error=Datos incompletos para bloquear horario.");
            exit();
        }

        $hora_inicio = date("H:i:s", strtotime($hora_inicio_str));
        $hora_fin = date("H:i:s", strtotime($hora_fin_str));

        $interval_seconds = 3600;

        $pdo->beginTransaction();

        try {
            for ($current_timestamp = strtotime($fecha_bloqueo . ' ' . $hora_inicio);
                 $current_timestamp <= strtotime($fecha_bloqueo . ' ' . $hora_fin);
                 $current_timestamp += $interval_seconds) {

                $fecha_cita = date("Y-m-d", $current_timestamp);
                $hora_cita = date("H:i:s", $current_timestamp);

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

                $nombre_placeholder = "Horario Bloqueado";
                $cedula_placeholder = null;
                $email_placeholder = null;
                $telefono_placeholder = "N/A";
                $especialidad_placeholder = "Bloqueado";
                $estado_bloqueado = "bloqueado";

                $stmt->execute([
                    ':nombre_paciente' => $nombre_placeholder,
                    ':cedula_paciente' => $cedula_placeholder,
                    ':email_paciente' => $email_placeholder,
                    ':telefono_paciente' => $telefono_placeholder,
                    ':especialidad' => $especialidad_placeholder,
                    ':fecha_cita' => $fecha_cita,
                    ':hora_cita' => $hora_cita,
                    ':estado' => $estado_bloqueado
                ]);
            }

            $pdo->commit();
            header("Location: dashboard.php?success=" . urlencode("Horario(s) bloqueado(s) exitosamente."));
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            header("Location: dashboard.php?error=" . urlencode("ERROR al bloquear horario(s): " . $e->getMessage()));
            exit();
        }
    }

    // --- Lógica para ACTUALIZAR CITA ---
    // Esta sección necesitará ser revisada con tu código de actualización específico.
    // Ejemplo de cómo debería ser, asumiendo que el modal de reprogramar/editar ya funciona
    elseif ($accion == 'actualizar') {
        $id = $_POST['id'] ?? null;
        $nombre_paciente = trim($_POST['nombre_paciente'] ?? '');
        $cedula_paciente = trim($_POST['cedula_paciente'] ?? null);
        $email_paciente = trim($_POST['email_paciente'] ?? null);
        $telefono_paciente = trim($_POST['telefono_paciente'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $fecha_cita = $_POST['fecha_cita'] ?? '';
        $hora_cita = $_POST['hora_cita'] ?? '';

        if (empty($id) || empty($nombre_paciente) || empty($telefono_paciente) || empty($especialidad) || empty($fecha_cita) || empty($hora_cita)) {
            header("Location: dashboard.php?error=" . urlencode("Datos incompletos para actualizar la cita."));
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE citas SET
                nombre_paciente = :nombre_paciente,
                cedula_paciente = :cedula_paciente,
                email_paciente = :email_paciente,
                telefono_paciente = :telefono_paciente,
                especialidad = :especialidad,
                fecha_cita = :fecha_cita,
                hora_cita = :hora_cita,
                estado = 'reprogramada' -- O el estado que corresponda al editar
                WHERE id = :id");

            $stmt->execute([
                ':nombre_paciente' => $nombre_paciente,
                ':cedula_paciente' => !empty($cedula_paciente) ? $cedula_paciente : null,
                ':email_paciente' => !empty($email_paciente) ? $email_paciente : null,
                ':telefono_paciente' => $telefono_paciente,
                ':especialidad' => $especialidad,
                ':fecha_cita' => $fecha_cita,
                ':hora_cita' => $hora_cita,
                ':id' => $id
            ]);

            header("Location: dashboard.php?success=" . urlencode("Cita actualizada exitosamente."));
            exit();

        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'duplicate key value violates unique constraint') !== false || $e->getCode() == '23505') {
                 $errorMessage = "Ya existe una cita o un horario bloqueado para esa fecha y hora.";
            } else if (strpos($errorMessage, 'violates not-null constraint') !== false || $e->getCode() == '23502') {
                 $errorMessage = "Error: Faltan datos requeridos para la actualización. Revise los campos.";
            }
            header("Location: dashboard.php?error=" . urlencode("Error al actualizar la cita: " . $errorMessage));
            exit();
        }
    }


    // --- Lógica para CAMBIAR ESTADO DE CITA (Efectuada, Cancelar, Desbloquear) ---
    // Esta parte también deberá coincidir con tu implementación existente.
    // Aquí asumo que manejas los IDs pasados por GET.
    elseif ($accion == 'efectuada' || $accion == 'cancelar' || $accion == 'desbloquear') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: dashboard.php?error=" . urlencode("ID de cita no especificado."));
            exit();
        }

        $estado_nuevo = '';
        if ($accion == 'efectuada') {
            $estado_nuevo = 'efectuada';
        } elseif ($accion == 'cancelar') {
            $estado_nuevo = 'cancelada';
        } elseif ($accion == 'desbloquear') {
            // Cuando se 'desbloquea', se borra el registro de la cita 'bloqueada'
            try {
                $stmt = $pdo->prepare("DELETE FROM citas WHERE id = :id AND estado = 'bloqueado'");
                $stmt->execute([':id' => $id]);
                if ($stmt->rowCount() > 0) {
                    header("Location: dashboard.php?success=" . urlencode("Horario desbloqueado exitosamente."));
                } else {
                    header("Location: dashboard.php?error=" . urlencode("No se encontró el horario bloqueado o no se pudo desbloquear."));
                }
                exit();
            } catch (PDOException $e) {
                header("Location: dashboard.php?error=" . urlencode("Error al desbloquear: " . $e->getMessage()));
                exit();
            }
        }

        // Si la acción es 'efectuada' o 'cancelar', actualizamos el estado
        if (!empty($estado_nuevo)) {
            try {
                $stmt = $pdo->prepare("UPDATE citas SET estado = :estado_nuevo WHERE id = :id");
                $stmt->execute([
                    ':estado_nuevo' => $estado_nuevo,
                    ':id' => $id
                ]);
                header("Location: dashboard.php?success=" . urlencode("Cita marcada como " . $estado_nuevo . " exitosamente."));
                exit();
            } catch (PDOException $e) {
                header("Location: dashboard.php?error=" . urlencode("Error al actualizar estado: " . $e->getMessage()));
                exit();
            }
        }
    }

    // --- Acción no reconocida ---
    else {
        header("Location: dashboard.php?error=" . urlencode("Acción no reconocida."));
        exit();
    }
} else {
    // Si la solicitud no es POST, redirigir al dashboard
    header("Location: dashboard.php?error=" . urlencode("Acceso inválido."));
    exit();
}

// Opcional: Cerrar la conexión PDO (aunque PHP lo hace automáticamente al finalizar el script)
// $pdo = null;
?>
