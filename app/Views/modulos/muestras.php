<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .dt-buttons.btn-group .btn {
        margin-right: .5rem;
        border-radius: .375rem !important;
    }
    .dt-buttons.btn-group .btn:last-child { margin-right: 0; }
    .table th { white-space: nowrap; }

    /* Estilos para el modal de evaluación */
    #modalEvaluarContent {
        position: relative;
        min-height: 200px;
    }

    #modalEvaluarContent.loading::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    #modalEvaluarContent.loading::after {
        content: 'Cargando información de la muestra...';
        display: block;
        margin-top: 1rem;
        color: #0d6efd;
        font-weight: 500;
    }

    .modal-body h6 {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #6c757d;
    }

    .modal-body p {
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .modal-body .bg-light {
        background-color: #f8f9fa !important;
    }

    /* Ajustes para los badges de estado */
    .badge {
        font-size: 0.8em;
        padding: 0.35em 0.65em;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Muestras</h1>
        <span class="badge bg-primary">Gestión de Muestras</span>
    </div>
</div>

<?php if(session('message')): ?>
    <div class="alert alert-<?= session('message.type') ?> alert-dismissible fade show" role="alert">
        <?= session('message.text') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaMuestrasDecision" class="table table-hover table-striped align-middle" style="width:100%">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Cliente ID</th>
                    <th>Prototipo ID</th>
                    <th>Fecha Aprobación</th>
                    <th>Solicitante</th>
                    <th>Fecha Solicitud</th>
                    <th>Decisión</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($muestrasDecision)): ?>
                    <?php foreach ($muestrasDecision as $row): ?>
                        <tr data-comentarios="<?= esc($row['comentarios'] ?? '') ?>"
                            data-observaciones="<?= esc($row['observaciones'] ?? '') ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= esc($row['clienteId']) ?></td>
                            <td><?= esc($row['prototipoId']) ?></td>
                            <td><?= !empty($row['fecha']) ? date('d/m/Y', strtotime($row['fecha'])) : 'N/A' ?></td>
                            <td><?= esc($row['solicitadaPor']) ?></td>
                            <td><?= !empty($row['fechaSolicitud']) ? date('d/m/Y', strtotime($row['fechaSolicitud'])) : 'N/A' ?></td>
                            <td><?= esc($row['decision']) ?></td>
                            <td><?= esc($row['estado']) ?></td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary me-2 btn-ver"
                                        data-bs-toggle="modal"
                                        data-bs-target="#verModal"
                                        data-id="<?= $row['id'] ?>"
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-evaluar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#evaluarModal"
                                        data-id="<?= $row['id'] ?>"
                                        title="Evaluar muestra">
                                    <i class="fas fa-clipboard-check"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver muestra (solo lectura) -->
<div class="modal fade" id="verModal" tabindex="-1" aria-labelledby="verModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verModalLabel">Ver Detalles de Muestra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cliente ID</label>
                        <p class="form-control-plaintext" id="verClienteId">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Prototipo ID</label>
                        <p class="form-control-plaintext" id="verPrototipoId">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha Aprobación</label>
                        <p class="form-control-plaintext" id="verFecha">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Solicitante</label>
                        <p class="form-control-plaintext" id="verSolicitadaPor">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha Solicitud</label>
                        <p class="form-control-plaintext" id="verFechaSolicitud">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Decisión</label>
                        <p class="form-control-plaintext" id="verDecision">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <p class="form-control-plaintext" id="verEstado">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Comentarios</label>
                        <p class="form-control-plaintext bg-light p-2 rounded" id="verComentarios">-</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Observaciones</label>
                        <p class="form-control-plaintext bg-light p-2 rounded" id="verObservaciones">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para evaluar muestra -->
<div class="modal fade" id="evaluarModal" tabindex="-1" aria-labelledby="evaluarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="evaluarModalLabel">Evaluar Muestra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalEvaluarContent">
                <form id="formEvaluarMuestra">
                    <input type="hidden" id="muestraId" name="muestraId">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="clienteId" class="form-label">Cliente ID</label>
                            <input type="text" class="form-control" id="clienteId" name="clienteId">
                        </div>
                        <div class="col-md-6">
                            <label for="prototipoId" class="form-label">Prototipo ID</label>
                            <input type="text" class="form-control" id="prototipoId" name="prototipoId">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha" class="form-label">Fecha Aprobación</label>
                            <input type="date" class="form-control" id="fecha" name="fecha">
                        </div>
                        <div class="col-md-6">
                            <label for="solicitadaPor" class="form-label">Solicitante</label>
                            <input type="text" class="form-control" id="solicitadaPor" name="solicitadaPor">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fechaSolicitud" class="form-label">Fecha Solicitud</label>
                            <input type="date" class="form-control" id="fechaSolicitud" name="fechaSolicitud">
                        </div>
                        <div class="col-md-6">
                            <label for="decision" class="form-label">Decisión</label>
                            <select class="form-select" id="decision" name="decision">
                                <option value="">Seleccionar...</option>
                                <option value="Aprobada">Aprobada</option>
                                <option value="Rechazada">Rechazada</option>
                                <option value="Pendiente">Pendiente</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Seleccionar...</option>
                                <option value="Aprobada">Aprobada</option>
                                <option value="Rechazada">Rechazada</option>
                                <option value="Pendiente">Pendiente</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="comentarios" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comentarios" name="comentarios" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEvaluacion">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Cargar los datos en el modal de Ver (solo lectura)
        $('#verModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const row = button.closest('tr');

            // Obtener los datos de la fila
            const clienteId = row.find('td:eq(1)').text().trim();
            const prototipoId = row.find('td:eq(2)').text().trim();
            const fecha = row.find('td:eq(3)').text().trim();
            const solicitadaPor = row.find('td:eq(4)').text().trim();
            const fechaSolicitud = row.find('td:eq(5)').text().trim();
            const decision = row.find('td:eq(6)').text().trim();
            const estado = row.find('td:eq(7)').text().trim();
            const comentarios = row.data('comentarios') || '-';
            const observaciones = row.data('observaciones') || '-';

            // Poblar el modal de solo lectura
            $('#verClienteId').text(clienteId);
            $('#verPrototipoId').text(prototipoId);
            $('#verFecha').text(fecha);
            $('#verSolicitadaPor').text(solicitadaPor);
            $('#verFechaSolicitud').text(fechaSolicitud);
            $('#verDecision').text(decision);
            $('#verEstado').text(estado);
            $('#verComentarios').text(comentarios);
            $('#verObservaciones').text(observaciones);
        });

        // Cargar los datos de la muestra en el modal
        $('#evaluarModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const row = button.closest('tr');

            // Obtener los datos de la fila
            const id = row.find('td:eq(0)').text().trim();
            const clienteId = row.find('td:eq(1)').text().trim();
            const prototipoId = row.find('td:eq(2)').text().trim();
            const fecha = row.find('td:eq(3)').text().trim();
            const solicitadaPor = row.find('td:eq(4)').text().trim();
            const fechaSolicitud = row.find('td:eq(5)').text().trim();
            const decision = row.find('td:eq(6)').text().trim();
            const estado = row.find('td:eq(7)').text().trim();
            const comentarios = row.data('comentarios') || '';
            const observaciones = row.data('observaciones') || '';

            // Función para convertir fecha de d/m/Y a Y-m-d
            function convertirFecha(fechaStr) {
                if (!fechaStr || fechaStr === 'N/A' || fechaStr === 'Pendiente') return '';
                const partes = fechaStr.split('/');
                if (partes.length === 3) {
                    return `${partes[2]}-${partes[1]}-${partes[0]}`;
                }
                return '';
            }

            console.log('Datos cargados:', {id, clienteId, prototipoId, fecha, solicitadaPor, fechaSolicitud, decision, estado, comentarios, observaciones});
            console.log('Estado value:', estado, 'Length:', estado.length);

            // Poblar el formulario
            $('#muestraId').val(id);
            $('#clienteId').val(clienteId);
            $('#prototipoId').val(prototipoId);
            $('#fecha').val(convertirFecha(fecha));
            $('#solicitadaPor').val(solicitadaPor);
            $('#fechaSolicitud').val(convertirFecha(fechaSolicitud));
            $('#decision').val(decision);
            $('#comentarios').val(comentarios);
            $('#observaciones').val(observaciones);

            // Establecer el estado con un pequeño delay para asegurar que el DOM esté listo
            setTimeout(function() {
                $('#estado').val(estado);
                console.log('Estado después de set:', $('#estado').val());
            }, 50);
        });

        // Limpiar el modal al cerrarlo
        $('#evaluarModal').on('hidden.bs.modal', function () {
            $('#formEvaluarMuestra')[0].reset();
        });

        // Manejar el clic en el botón Guardar Cambios
        $('#btnGuardarEvaluacion').on('click', function() {
            const formData = {
                id: $('#muestraId').val(),
                clienteId: $('#clienteId').val(),
                prototipoId: $('#prototipoId').val(),
                fecha: $('#fecha').val(),
                solicitadaPor: $('#solicitadaPor').val(),
                fechaSolicitud: $('#fechaSolicitud').val(),
                decision: $('#decision').val(),
                estado: $('#estado').val(),
                comentarios: $('#comentarios').val(),
                observaciones: $('#observaciones').val()
            };

            console.log('Guardando datos:', formData);

            // Aquí puedes agregar la llamada AJAX para guardar
            $.ajax({
                url: '<?= base_url('muestras/guardar') ?>',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert('Datos guardados correctamente');
                    $('#evaluarModal').modal('hide');
                    location.reload(); // Recargar la página para ver los cambios
                },
                error: function(xhr) {
                    alert('Error al guardar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                }
            });
        });

        // Fecha actual para nombres de archivos exportados
        const hoy = new Date().toISOString().slice(0,10);

        // Inicializar DataTable para la tabla de muestras con decisión
        $('#tablaMuestrasDecision').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            responsive: true,
            order: [[0, 'desc']],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B>" +
                "<'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend:'copy',  text:'Copiar', exportOptions:{ columns: ':visible' } },
                { extend:'csv',   text:'CSV',   filename:'muestras_decision_'+hoy },
                { extend:'excel', text:'Excel', filename:'muestras_decision_'+hoy },
                { extend:'pdf',   text:'PDF',   filename:'muestras_decision_'+hoy, title:'Muestras con Decisión',
                    orientation:'landscape', pageSize:'A4' },
                { extend:'print', text:'Imprimir' }
            ]
        });
    });
</script>
<?= $this->endSection() ?>
