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
