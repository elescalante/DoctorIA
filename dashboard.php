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
                        if ($citas_stmt && $citas_stmt->rowCount() > 0
