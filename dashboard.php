<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluye el archivo de configuración, que ahora define $pdo
require_once 'config.php';

// --- Consulta para citas activas ---
$sql_citas = "SELECT * FROM citas WHERE estado IN ('pendiente', 'reprogramada') ORDER BY fecha_cita, hora_cita";
$citas_stmt = null;

try {
    $citas_stmt = $pdo->query($sql_citas);
} catch (PDOException $e) {
    die("ERROR al consultar citas: " . $e->getMessage());
}

// --- Consulta para horarios bloqueados ---
$sql_bloqueados = "SELECT * FROM citas WHERE estado = 'bloqueado' AND fecha_cita >= CURRENT_DATE ORDER BY fecha_cita, hora_cita";
$bloqueados_stmt = null;

try {
    $bloqueados_stmt = $pdo->query($sql_bloqueados);
} catch (PDOException $e) {
    die("ERROR al consultar horarios bloqueados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Citas Avanzado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container mt-4">
    <h1 class="h2 mb-4">🗓️ Panel de Administración de Citas</h1>

    <?php
    // --- Sección para mostrar mensajes de éxito o error ---
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_GET['success']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }

    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_GET['error']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
    ?>

    <div class="accordion mb-4" id="managementAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    <strong><i class="bi bi-person-plus-fill"></i> Añadir Nueva Cita</strong>
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#managementAccordion">
                <div class="accordion-body">
                    <form action="acciones.php" method="POST">
                        <input type="hidden" name="accion" value="agregar">
                        <div class="row g-3">
                            <div class="col-md-6"><input type="text" class="form-control" name="nombre_paciente" placeholder="Nombre del Paciente" required></div>
                            <div class="col-md-6"><input type="text" class="form-control" name="cedula_paciente" placeholder="Cédula de Identidad"></div>
                            <div class="col-md-6"><input type="email" class="form-control" name="email_paciente" placeholder="Correo Electrónico"></div>
                            <div class="col-md-6"><input type="text" class="form-control" name="telefono_paciente" placeholder="Teléfono (+1555...)" required></div>
                            <div class="col-md-4"><input type="text" class="form-control" name="especialidad" placeholder="Especialidad" required></div>
                            <div class="col-md-3"><input type="date" class="form-control" name="fecha_cita" required></div>
                            <div class="col-md-3"><input type="time" class="form-control" name="hora_cita" required></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Añadir</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <strong><i class="bi bi-lock-fill"></i> Bloquear Rango de Horario</strong>
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#managementAccordion">
                <div class="accordion-body">
                    <form action="acciones.php" method="POST" class="row g-3 align-items-end">
                        <input type="hidden" name="accion" value="bloquear_rango">
                        <div class="col-md-4"><label for="fecha_bloqueo" class="form-label">Fecha</label><input type="date" class="form-control" id="fecha_bloqueo" name="fecha_bloqueo" required></div>
                        <div class="col-md-3"><label for="hora_inicio" class="form-label">Hora Inicio</label><input type="time" class="form-control" id="hora_inicio" name="hora_inicio" step="3600" required></div>
                        <div class="col-md-3"><label for="hora_fin" class="form-label">Hora Fin</label><input type="time" class="form-control" id="hora_fin" name="hora_fin" step="3600" required></div>
                        <div class="col-md-2"><button type="submit" class="btn btn-danger w-100">Bloquear</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white"><strong>Próximas Citas</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Paciente</th><th>Cédula</th><th>Email</th><th>Teléfono</th><th>Especialidad</th><th>Fecha/Hora</th><th>Estado</th><th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($citas_stmt && $citas_stmt->rowCount() > 0):
                            while($cita = $citas_stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($cita['nombre_paciente'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($cita['cedula_paciente'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($cita['email_paciente'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($cita['telefono_paciente'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($cita['especialidad'] ?? '') ?></td>
                                    <td><?= date("d/m/Y", strtotime($cita['fecha_cita'])) ?> <?= date("h:i A", strtotime($cita['hora_cita'])) ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= ucfirst($cita['estado']) ?></span></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="acciones.php?accion=efectuada&id=<?= $cita['id'] ?>" class="btn btn-success" title="Marcar como Efectuada"><i class="bi bi-check-lg"></i></a>
                                            <button type="button" class="btn btn-info text-white reprogramar-btn" data-id="<?= $cita['id'] ?>" data-bs-toggle="modal" data-bs-target="#reprogramarModal" title="Reprogramar/Editar Cita"><i class="bi bi-pencil-fill"></i></button>
                                            <a href="acciones.php?accion=cancelar&id=<?= $cita['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro?');" title="Cancelar Cita"><i class="bi bi-trash-fill"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted">No hay citas pendientes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white"><strong>Horarios Bloqueados</strong></div>
        <div class="card-body">
             <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Fecha</th><th>Rango Horario Bloqueado</th><th class="text-center">Acciones</th></tr></thead>
                    <tbody>
                        <?php
                        // Recuperar y agrupar los horarios bloqueados
                        $bloqueados_agrupados = [];
                        if ($bloqueados_stmt && $bloqueados_stmt->rowCount() > 0) {
                            $todos_bloqueados = $bloqueados_stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Agrupar por fecha para identificar rangos
                            foreach ($todos_bloqueados as $b) {
                                $fecha_bloqueo = $b['fecha_cita'];
                                $hora_bloqueo = $b['hora_cita'];
                                $id_bloqueo = $b['id'];

                                if (!isset($bloqueados_agrupados[$fecha_bloqueo])) {
                                    $bloqueados_agrupados[$fecha_bloqueo] = [];
                                }
                                $bloqueados_agrupados[$fecha_bloqueo][$hora_bloqueo] = $id_bloqueo;
                            }
                        }

                        if (!empty($bloqueados_agrupados)):
                            foreach ($bloqueados_agrupados as $fecha => $horas_por_fecha):
                                ksort($horas_por_fecha); // Ordenar las horas para la fecha actual

                                $rangos_encontrados = [];
                                $current_range_start = null;
                                $ids_in_current_range = [];

                                // Iterar sobre las horas ordenadas para encontrar rangos contiguos
                                $horas_array = array_keys($horas_por_fecha);
                                for ($i = 0; $i < count($horas_array); $i++) {
                                    $current_hora = $horas_array[$i];
                                    $current_id = $horas_por_fecha[$current_hora];

                                    if ($current_range_start === null) {
                                        // Iniciar un nuevo rango
                                        $current_range_start = $current_hora;
                                        $ids_in_current_range[] = $current_id;
                                    } else {
                                        // Calcular la hora esperada si el rango es contiguo (una hora después)
                                        // strtotime('+1 hour', strtotime($horas_array[$i-1]))
                                        $expected_next_hora = date('H:i:s', strtotime($horas_array[$i-1]) + 3600); // Sumar 1 hora
                                        if ($current_hora === $expected_next_hora) {
                                            // Es parte del rango actual
                                            $ids_in_current_range[] = $current_id;
                                        } else {
                                            // El rango actual terminó, guardar y empezar uno nuevo
                                            $rangos_encontrados[] = [
                                                'start' => $current_range_start,
                                                'end' => $horas_array[$i-1], // La hora final del rango es la hora anterior
                                                'ids' => $ids_in_current_range
                                            ];
                                            $current_range_start = $current_hora;
                                            $ids_in_current_range = [$current_id];
                                        }
                                    }
                                }

                                // Guardar el último rango si existe
                                if ($current_range_start !== null) {
                                    $rangos_encontrados[] = [
                                        'start' => $current_range_start,
                                        'end' => $horas_array[count($horas_array) - 1], // La última hora es el fin del último rango
                                        'ids' => $ids_in_current_range
                                    ];
                                }

                                // Mostrar los rangos encontrados para esta fecha
                                foreach ($rangos_encontrados as $rango) {
                                    // Los IDs de los bloques dentro de este rango. Se usan para desbloquear el rango completo.
                                    $all_ids_str = implode(',', $rango['ids']);

                                    // Formatear las horas para la visualización (HH:MM)
                                    $hora_inicio_display = date("H:i", strtotime($rango['start']));
                                    // La hora fin del rango es la hora de inicio del último bloque del rango + 1 hora
                                    $hora_fin_display = date("H:i", strtotime($rango['end']) + 3600);
                        ?>
                                <tr>
                                    <td><?= date("d/m/Y", strtotime($fecha)) ?></td>
                                    <td><?= $hora_inicio_display ?> a <?= $hora_fin_display ?></td>
                                    <td class="text-center">
                                        <a href="acciones.php?accion=desbloquear_rango&ids=<?= $all_ids_str ?>" class="btn btn-outline-success btn-sm" onclick="return confirm('¿Estás seguro de desbloquear este rango completo?');" title="Desbloquear Rango"><i class="bi bi-unlock-fill"></i></a>
                                    </td>
                                </tr>
                        <?php
                                } // Fin foreach rangos_encontrados
                            endforeach; // Fin foreach bloqueados_agrupados
                        else:
                        ?>
                            <tr><td colspan="3" class="text-center text-muted">No hay horarios bloqueados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="reprogramarModal" tabindex="-1" aria-labelledby="reprogramarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reprogramarModalLabel">Editar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="acciones.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nombre</label><input type="text" class="form-control" id="edit_nombre" name="nombre_paciente" required></div>
                        <div class="col-md-6"><label class="form-label">Cédula</label><input type="text" class="form-control" id="edit_cedula" name="cedula_paciente"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" id="edit_email" name="email_paciente"></div>
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input type="text" class="form-control" id="edit_telefono" name="telefono_paciente" required></div>
                        <div class="col-md-4"><label class="form-label">Especialidad</label><input type="text" class="form-control" id="edit_especialidad" name="especialidad" required></div>
                        <div class="col-md-4"><label class="form-label">Fecha</label><input type="date" class="form-control" id="edit_fecha" name="fecha_cita" required></div>
                        <div class="col-md-4"><label class="form-label">Hora</label><input type="time" class="form-control" id="edit_hora" name="hora_cita" required></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reprogramarButtons = document.querySelectorAll('.reprogramar-btn');
    reprogramarButtons.forEach(button => {
        button.addEventListener('click', function () {
            const citaId = this.getAttribute('data-id');
            // Usamos fetch para obtener los datos de la cita del servidor
            fetch(`get_cita.php?id=${citaId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        // Llenamos el formulario del modal con los datos recibidos
                        document.getElementById('edit_id').value = data.id;
                        document.getElementById('edit_nombre').value = data.nombre_paciente;
                        document.getElementById('edit_cedula').value = data.cedula_paciente;
                        document.getElementById('edit_email').value = data.email_paciente;
                        document.getElementById('edit_telefono').value = data.telefono_paciente;
                        document.getElementById('edit_especialidad').value = data.especialidad;
                        document.getElementById('edit_fecha').value = data.fecha_cita;
                        document.getElementById('edit_hora').value = data.hora_cita;
                    }
                })
                .catch(error => console.error('Error al obtener datos de la cita:', error));
        });
    });
});
</script>
</body>
</html>
