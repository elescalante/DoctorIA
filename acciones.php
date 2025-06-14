<?php
// ... (código existente) ...

    // --- Lógica para AÑADIR NUEVA CITA ---
    if ($accion == 'agregar') {
        // ... (Tu código existente para recoger y sanear datos) ...

        // 2. Validación básica de campos requeridos (basado en tu schema NOT NULL)
        if (empty($nombre_paciente) || empty($telefono_paciente) || empty($especialidad) || empty($fecha_cita) || empty($hora_cita)) {
            header("Location: dashboard.php?error=" . urlencode("Por favor, complete todos los campos requeridos para la cita."));
            exit();
        }

        // --- NUEVA VALIDACIÓN DE DISPONIBILIDAD DE HORARIO ---
        try {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM citas WHERE fecha_cita = :fecha_cita AND hora_cita = :hora_cita AND estado IN ('pendiente', 'reprogramada', 'bloqueado')");
            $stmt_check->execute([
                ':fecha_cita' => $fecha_cita,
                ':hora_cita' => $hora_cita
            ]);
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                // Si ya existe una cita o un horario bloqueado para esa hora, no permitir la inserción
                header("Location: dashboard.php?error=" . urlencode("El horario seleccionado ya está ocupado. Por favor, elija otra fecha u hora."));
                exit();
            }
        } catch (PDOException $e) {
            header("Location: dashboard.php?error=" . urlencode("Error al verificar disponibilidad de horario: " . $e->getMessage()));
            exit();
        }
        // --- FIN DE LA NUEVA VALIDACIÓN ---

        // ... (Tu código existente para definir el estado y el bloque try-catch para INSERT) ...

        try {
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

            $stmt->execute([
                ':nombre_paciente' => $nombre_paciente,
                ':cedula_paciente' => !empty($cedula_paciente) ? $cedula_paciente : null,
                ':email_paciente' => !empty($email_paciente) ? $email_paciente : null,
                ':telefono_paciente' => $telefono_paciente,
                ':especialidad' => $especialidad,
                ':fecha_cita' => $fecha_cita,
                ':hora_cita' => $hora_cita,
                ':estado' => $estado
            ]);

            header("Location: dashboard.php?success=" . urlencode("Cita añadida exitosamente."));
            exit();

        } catch (PDOException $e) {
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

// ... (Resto del código de acciones.php) ...
