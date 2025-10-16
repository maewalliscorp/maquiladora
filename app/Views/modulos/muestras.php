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
    <a href="<?= base_url('muestras/nueva') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nueva Muestra
    </a>
</div>

<?php if(session('message')): ?>
    <div class="alert alert-<?= session('message.type') ?> alert-dismissible fade show" role="alert">
        <?= session('message.text') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Listado de Muestras</h5>
            <div class="d-flex">
                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="refreshTable">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaMuestras" class="table table-hover table-striped align-middle" style="width:100%">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Código Prototipo</th>
                    <th>Solicitante</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha Envío</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($muestras as $muestra): ?>
                    <tr>
                        <td><?= $muestra['muestraId'] ?></td>
                        <td><?= esc($muestra['codigoPrototipo']) ?></td>
                        <td><?= esc($muestra['solicitadaPor']) ?></td>
                        <td><?= $muestra['fechaSolicitud'] ? date('d/m/Y', strtotime($muestra['fechaSolicitud'])) : 'N/A' ?></td>
                        <td><?= $muestra['fechaEnvio'] ? date('d/m/Y', strtotime($muestra['fechaEnvio'])) : 'Pendiente' ?></td>
                        <td>
                            <?php
                            $badgeClass = 'secondary';
                            if ($muestra['estado'] === 'Aprobada') $badgeClass = 'success';
                            elseif ($muestra['estado'] === 'Pendiente') $badgeClass = 'warning';
                            elseif ($muestra['estado'] === 'Rechazada') $badgeClass = 'danger';
                            ?>
                            <span class="badge bg-<?= $badgeClass ?>"><?= $muestra['estado'] ?: 'Pendiente' ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-evaluar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#evaluarModal"
                                        data-id="<?= $muestra['muestraId'] ?>"
                                        title="Evaluar muestra">
                                    <i class="fas fa-clipboard-check me-1"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para evaluar muestra -->
<div class="modal fade" id="evaluarModal" tabindex="-1" aria-labelledby="evaluarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="evaluarModalLabel">Evaluar Muestra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalEvaluarContent">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">ID de Muestra</h6>
                            <p id="muestraId">-</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Estado</h6>
                            <span id="estadoBadge" class="badge bg-secondary">-</span>
                        </div>
                    </div>

                    <div class="row mb-3" id="clienteRow" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-muted">Cliente</h6>
                            <p id="clienteNombre">-</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Fecha de Aprobación</h6>
                            <p id="fechaAprobacion">-</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Decisión</h6>
                            <p id="decision">-</p>
                        </div>
                    </div>

                    <div class="row mb-3" id="observacionesRow" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-muted">Observaciones</h6>
                            <p id="observaciones" class="p-2 bg-light rounded">-</p>
                        </div>
                    </div>

                    <div class="row mb-3" id="comentariosRow" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-muted">Comentarios</h6>
                            <p id="comentarios" class="p-2 bg-light rounded">-</p>
                        </div>
                    </div>
                </div>
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

        // Cargar los datos de la muestra en el modal
        $('#evaluarModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const muestraId = button.data('id');
            const modal = $(this);

            // Mostrar indicador de carga
            modal.find('#modalEvaluarContent').addClass('loading');

            // Cargar los datos de evaluación vía AJAX
            console.log('Solicitando datos para muestra ID:', muestraId);
            $.get(`<?= base_url('muestras/evaluar/') ?>${muestraId}`)
                .done(function(response) {
                    console.log('Respuesta recibida:', response);

                    if (!response) {
                        throw new Error('La respuesta del servidor está vacía');
                    }

                    // Asegurarse de que response es un objeto
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    const evaluacion = data.evaluacion || data; // Manejar ambos formatos de respuesta

                    if (!evaluacion) {
                        throw new Error('No se encontraron datos de evaluación');
                    }

                    console.log('Datos de evaluación:', evaluacion);

                    // Actualizar los campos del modal
                    $('#muestraId').text(evaluacion.muestraId || muestraId);

                    // Actualizar estado con badge
                    const estado = evaluacion.estado || 'Pendiente';
                    let badgeClass = 'secondary';
                    if (estado === 'Aprobada') badgeClass = 'success';
                    else if (estado === 'Pendiente') badgeClass = 'warning';
                    else if (estado === 'Rechazada') badgeClass = 'danger';

                    $('#estadoBadge')
                        .removeClass('bg-success bg-warning bg-danger bg-secondary')
                        .addClass(`bg-${badgeClass}`)
                        .text(estado);

                    // Actualizar cliente si existe
                    if (evaluacion.clienteNombre) {
                        $('#clienteNombre').text(evaluacion.clienteNombre);
                        $('#clienteRow').show();
                    } else {
                        $('#clienteRow').hide();
                    }

                    // Actualizar fecha de aprobación
                    $('#fechaAprobacion').text(
                        evaluacion.fechaAprobacion
                            ? new Date(evaluacion.fechaAprobacion).toLocaleString()
                            : 'Pendiente'
                    );

                    // Actualizar decisión
                    const decision = evaluacion.decision || '';
                    $('#decision').text(decision ? decision.charAt(0).toUpperCase() + decision.slice(1) : 'Pendiente');

                    // Actualizar observaciones si existen
                    if (evaluacion.observaciones) {
                        $('#observaciones').html(String(evaluacion.observaciones).replace(/\n/g, '<br>'));
                        $('#observacionesRow').show();
                    } else {
                        $('#observacionesRow').hide();
                    }

                    // Actualizar comentarios si existen
                    if (evaluacion.comentarios) {
                        $('#comentarios').html(String(evaluacion.comentarios).replace(/\n/g, '<br>'));
                        $('#comentariosRow').show();
                    } else {
                        $('#comentariosRow').hide();
                    }

                    // Mostrar el botón de guardar si es necesario
                    $('#btnGuardarEvaluacion').toggle(estado === 'Pendiente');

                })
                .fail(function(xhr, status, error) {
                    console.error('Error en la petición AJAX:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });

                    let errorMessage = 'Error al cargar la información de la muestra';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.error || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }

                    // Mostrar mensaje de error en el modal
                    $('#modalEvaluarContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${errorMessage}
                        <div class="mt-2 small">Código de error: ${xhr.status}</div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                `);
                })
                .always(function() {
                    modal.find('#modalEvaluarContent').removeClass('loading');
                });
        });

        // Limpiar el modal al cerrarlo
        $('#evaluarModal').on('hidden.bs.modal', function () {
            // Restablecer los campos
            $('#muestraId, #clienteNombre, #fechaAprobacion, #decision, #observaciones, #comentarios')
                .text('-');

            // Ocultar secciones opcionales
            $('#clienteRow, #observacionesRow, #comentariosRow').hide();

            // Restablecer el badge de estado
            $('#estadoBadge')
                .removeClass('bg-success bg-warning bg-danger')
                .addClass('bg-secondary')
                .text('-');
        });

        // Manejar el clic en el botón Guardar Cambios
        $('#btnGuardarEvaluacion').on('click', function() {
            // Aquí irá la lógica para guardar los cambios
            alert('Función de guardar cambios se implementará aquí');
        });

        // Fecha actual para nombres de archivos exportados
        const hoy = new Date().toISOString().slice(0,10);

        // Inicializar DataTable con botones de exportación
        const table = $('#tablaMuestras').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            columnDefs: [
                { targets: -1, orderable: false, searchable: false } // Deshabilitar ordenamiento en columna de acciones
            ],
            pageLength: 10,
            responsive: true,
            order: [[0, 'desc']],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B>" +
                "<'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend:'copy',  text:'Copiar', exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'csv',   text:'CSV',   filename:'muestras_'+hoy, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'excel', text:'Excel', filename:'muestras_'+hoy, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'pdf',   text:'PDF',   filename:'muestras_'+hoy, title:'Listado de Muestras',
                    orientation:'landscape', pageSize:'A4', exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'print', text:'Imprimir', exportOptions:{ columns: ':not(:last-child)' } }
            ]
        });

        // Botón de actualizar
        $('#refreshTable').on('click', function() {
            table.ajax.reload(null, false); // false = no reinicia la paginación
        });
    });
</script>
<?= $this->endSection() ?>
