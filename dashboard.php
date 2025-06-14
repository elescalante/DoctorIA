<?php
// Incluye el archivo de configuración, que ahora define $pdo
require_once 'config.php'; // Usa require_once para asegurar que se incluya una sola vez.

// --- Consulta para citas activas ---
// Utiliza $pdo en lugar de $conn
$sql_citas = "SELECT * FROM citas WHERE estado IN ('pendiente', 'reprogramada') ORDER BY fecha_cita, hora_cita";
$citas_stmt = null; // Usaremos un statement para las citas

try {
    $citas_stmt = $pdo->query($sql_citas); // Ejecuta la consulta con PDO
} catch (PDOException $e) {
    // Manejo de errores en caso de que la consulta falle
    die("ERROR al consultar citas: " . $e->getMessage());
}

// --- Consulta para horarios bloqueados ---
// Utiliza $pdo en lugar de $conn
// Nota: CURDATE() y NOW() son funciones de MySQL.
// En PostgreSQL, usa CURRENT_DATE o NOW() para obtener la fecha/hora actual.
// Si tu campo fecha_cita es tipo DATE, CURRENT_DATE es lo más adecuado.
$sql_bloqueados = "SELECT * FROM citas WHERE estado = 'bloqueado' AND fecha_cita >= CURRENT_DATE ORDER BY fecha_cita, hora_cita";
$bloqueados_stmt = null; // Usaremos un statement para los bloqueados

try {
    $bloqueados_stmt = $pdo->query($sql_bloqueados); // Ejecuta la consulta con PDO
} catch (PDOException $e) {
    // Manejo de errores en caso de que la consulta falle
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
                        // Con PDO, puedes usar fetchAll para obtener todos los resultados de una vez
                        // O iterar directamente sobre el statement como en MySQLi, pero con fetch()
                        // Corregido: Usar $citas_stmt y rowCount()
                        if ($citas_stmt && $citas_stmt->rowCount() > 0):
                            while($cita = $citas_stmt->fetch(PDO::FETCH_ASSOC)): // fetch() para obtener fila por fila
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
                    <thead><tr><th>Fecha</th><th>Hora</th><th class="text-center">Acción</th></tr></thead>
                    <tbody>
                        <?php
                        // Corregido: Usar $bloqueados_stmt y rowCount()
                        if ($bloqueados_stmt && $bloqueados_stmt->rowCount() > 0):
                            while($b = $bloqueados_stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                                <tr>
                                    <td><?= date("d/m/Y", strtotime($b['fecha_cita'])) ?></td>
                                    <td><?= date("h:i A", strtotime($b['hora_cita'])) ?></td>
                                    <td class="text-center"><a href="acciones.php?accion=desbloquear&id=<?= $b['id'] ?>" class="btn btn-outline-success btn-sm" title="Desbloquear"><i class="bi bi-unlock-fill"></i></a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
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
                .then(response => response.json())
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
                .catch(error => console.error('Error fetching cita data:', error));
        });
    });
});
</script>
</body>
</html>
<?php
// Ya no usamos $conn->close() con PDO.
// La conexión PDO se cierra automáticamente cuando el script termina.
// Si necesitas cerrar explícitamente, puedes establecer $pdo = null;
?>
