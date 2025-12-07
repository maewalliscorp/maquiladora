<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .progress {
        height: 20px;
    }
    .progress-bar {
        font-size: 12px;
        line-height: 20px;
    }
    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Mis Pedidos</h1>
    <span class="badge bg-primary">Cliente</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Órdenes de Producción Asignadas</strong>
    </div>
    <div class="card-body">
        <table id="tablaOrdenes" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>Folio OP</th>
                <th>Folio Pedido</th>
                <th>Producto</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estatus</th>
                <th>Avance</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?= esc($orden['id']) ?></td>
                        <td>
                            <strong><?= esc($orden['folio'] ?? 'OP-' . str_pad($orden['id'], 4, '0', STR_PAD_LEFT)) ?></strong>
                        </td>
                        <td><?= esc($orden['folio_oc'] ?? '-') ?></td>
                        <td>
                            <?php if ($orden['disenio_codigo']): ?>
                                <div>
                                    <strong><?= esc($orden['disenio_codigo']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= esc($orden['disenio_nombre']) ?></small>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Sin diseño asignado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($orden['fechaInicioPlan'] ?? '-') ?></td>
                        <td><?= esc($orden['fechaFinPlan'] ?? '-') ?></td>
                        <td>
                            <?php
                            $badgeClass = 'secondary';
                            $estatus = strtolower($orden['status'] ?? 'planeada');
                            if ($estatus === 'completada' || $estatus === 'finalizada') {
                                $badgeClass = 'success';
                            } elseif ($estatus === 'en proceso' || $estatus === 'producción') {
                                $badgeClass = 'info';
                            } elseif ($estatus === 'atrasada') {
                                $badgeClass = 'warning';
                            } elseif ($estatus === 'cancelada') {
                                $badgeClass = 'danger';
                            }
                            ?>
                            <span class="badge bg-<?= $badgeClass ?>"><?= esc(ucfirst($orden['status'] ?? 'Planeada')) ?></span>
                        </td>
                        <td>
                            <div class="progress" style="min-width: 100px;">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: <?= esc($orden['porcentaje_avance']) ?>%;"
                                     aria-valuenow="<?= esc($orden['porcentaje_avance']) ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= esc($orden['porcentaje_avance']) ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-info btn-action btn-ver-detalles"
                                    data-id="<?= (int)$orden['id'] ?>" 
                                    title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary btn-action btn-descargar-pdf"
                                    data-id="<?= (int)$orden['id'] ?>" 
                                    title="Descargar PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2 mb-0">No tienes órdenes de producción asignadas</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detalles de Orden -->
<div class="modal fade" id="detallesOrdenModal" tabindex="-1" aria-labelledby="detallesOrdenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="detallesOrdenModalLabel">Detalles de Orden de Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <div id="detalles-contenido">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-descargar-pdf-modal">
                    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    const tablaOrdenes = $('#tablaOrdenes').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es.json'
        },
        order: [[0, 'desc']]
    });

    let currentOrdenId = null;

    // Ver detalles de orden
    $(document).on('click', '.btn-ver-detalles', function() {
        const id = $(this).data('id');
        currentOrdenId = id;
        
        $('#detallesOrdenModal').modal('show');
        cargarDetallesOrden(id);
    });

    // Descargar PDF
    $(document).on('click', '.btn-descargar-pdf', function() {
        const id = $(this).data('id');
        descargarPDF(id);
    });

    // Descargar PDF desde modal
    $('#btn-descargar-pdf-modal').click(function() {
        if (currentOrdenId) {
            descargarPDF(currentOrdenId);
        }
    });

    function cargarDetallesOrden(id) {
        $.ajax({
            url: '<?= base_url('pedidos-clientes/detalles') ?>/' + id,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mostrarDetallesOrden(response.data);
                } else {
                    $('#detalles-contenido').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Error al cargar los detalles
                        </div>
                    `);
                }
            },
            error: function() {
                $('#detalles-contenido').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Error de conexión
                    </div>
                `);
            }
        });
    }

    function mostrarDetallesOrden(orden) {
        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-primary">Información General</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Folio OP:</strong></td><td>${orden.folio || 'OP-' + String(orden.id).padStart(4, '0')}</td></tr>
                        <tr><td><strong>Folio Pedido:</strong></td><td>${orden.folio_oc || '-'}</td></tr>
                        <tr><td><strong>Fecha Pedido:</strong></td><td>${orden.fecha_oc || '-'}</td></tr>
                        <tr><td><strong>Estatus:</strong></td><td><span class="badge bg-info">${orden.status || 'Planeada'}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Fechas de Producción</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Inicio Plan:</strong></td><td>${orden.fechaInicioPlan || '-'}</td></tr>
                        <tr><td><strong>Fin Plan:</strong></td><td>${orden.fechaFinPlan || '-'}</td></tr>
                        <tr><td><strong>Cantidad Plan:</strong></td><td>${orden.cantidadPlan || '0'} unidades</td></tr>
                        <tr><td><strong>Avance:</strong></td><td>
                            <div class="progress" style="min-width: 100px;">
                                <div class="progress-bar bg-success" style="width: ${orden.porcentaje_avance || 0}%;">
                                    ${orden.porcentaje_avance || 0}%
                                </div>
                            </div>
                        </td></tr>
                    </table>
                </div>
            </div>
        `;

        if (orden.disenio_nombre) {
            html += `
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary">Información del Diseño</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Código:</strong></td><td>${orden.disenio_codigo || '-'}</td></tr>
                            <tr><td><strong>Nombre:</strong></td><td>${orden.disenio_nombre || '-'}</td></tr>
                            <tr><td><strong>Versión:</strong></td><td>${orden.disenio_version || '-'}</td></tr>
                            <tr><td><strong>Descripción:</strong></td><td>${orden.disenio_descripcion || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
        }

        if (orden.tallas_detalle && orden.tallas_detalle.length > 0) {
            html += `
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary">Detalle por Tallas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sexo</th>
                                        <th>Talla</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            
            orden.tallas_detalle.forEach(talla => {
                html += `
                    <tr>
                        <td>${talla.sexo_nombre || '-'}</td>
                        <td>${talla.talla_nombre || '-'}</td>
                        <td>${talla.cantidad || '0'}</td>
                    </tr>
                `;
            });
            
            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        if (orden.asignaciones && orden.asignaciones.length > 0) {
            html += `
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary">Asignaciones de Tareas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tarea</th>
                                        <th>Empleado</th>
                                        <th>Estado</th>
                                        <th>Fecha Asignación</th>
                                        <th>Fecha Límite</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            
            orden.asignaciones.forEach(asignacion => {
                const estadoClass = asignacion.estado === 'Completada' ? 'success' : 
                                  asignacion.estado === 'En Progreso' ? 'info' : 'warning';
                html += `
                    <tr>
                        <td>${asignacion.tarea || '-'}</td>
                        <td>${asignacion.empleado_no ? '#' + asignacion.empleado_no + ' ' : ''}${asignacion.empleado_nombre || '-'}</td>
                        <td><span class="badge bg-${estadoClass}">${asignacion.estado || '-'}</span></td>
                        <td>${asignacion.fecha_asignacion || '-'}</td>
                        <td>${asignacion.fecha_limite || '-'}</td>
                    </tr>
                `;
            });
            
            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#detalles-contenido').html(html);
    }

    function descargarPDF(id) {
        window.open('<?= base_url('pedidos-clientes/descargar-pdf') ?>/' + id, '_blank');
    }
});
</script>
<?= $this->endSection() ?>
