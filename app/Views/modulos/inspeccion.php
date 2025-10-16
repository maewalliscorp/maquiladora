<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Estilos para botones gris intenso con letras blancas */
    .dt-buttons .btn {
        margin-right: .5rem;
        border-radius: .375rem !important;
        background-color: #6c757d !important;
        border: 1px solid #6c757d !important;
        color: #ffffff !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        transition: all 0.15s ease-in-out;
    }
    
    .dt-buttons .btn:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .dt-buttons .btn:active {
        background-color: #545b62 !important;
        border-color: #4e555b !important;
        transform: translateY(0);
    }
    
    .dt-buttons .btn:last-child { 
        margin-right: 0; 
    }
    
    /* Estilos para botones de acción minimalistas */
    .btn-accion {
        background-color: transparent !important;
        border: 1px solid #dee2e6 !important;
        color: #6c757d !important;
        width: 32px;
        height: 32px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px !important;
        transition: all 0.15s ease-in-out;
    }
    
    .btn-accion:hover {
        background-color: #f8f9fa !important;
        border-color: #adb5bd !important;
        color: #495057 !important;
        transform: translateY(-1px);
    }
    
    .btn-accion:active {
        background-color: #e9ecef !important;
        transform: translateY(0);
    }
    
    .badge { font-size: 0.8rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inspecciones</h1>
    <span class="badge bg-info">Calidad</span>
    <div class="ms-auto">
        <a href="<?= base_url('inspeccion/nueva') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nueva Inspección
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Inspecciones</strong>
    </div>
    <div class="card-body table-responsive">
        <table id="tablaInspeccion" class="table table-striped table-bordered align-middle" style="width:100%">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>No. Inspección</th>
                    <th>Orden Producción</th>
                    <th>Punto Inspección</th>
                    <th>Inspector</th>
                    <th>Fecha</th>
                    <th>Resultado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lista) && is_array($lista)): ?>
                    <?php foreach ($lista as $item): ?>
                        <tr>
                            <td><?= $item['num'] ?? '' ?></td>
                            <td><?= esc($item['numero_inspeccion'] ?? 'N/A') ?></td>
                            <td><?= esc($item['ordenProduccionId']) ?></td>
                            <td><?= esc($item['puntoInspeccionId']) ?></td>
                            <td><?= esc($item['inspectorId']) ?></td>
                            <td><?= !empty($item['fecha']) ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A' ?></td>
                            <td>
                                <?php
                                $badgeClass = 'secondary';
                                $resultado = strtolower($item['resultado'] ?? 'pendiente');
                                if (strpos($resultado, 'aprobado') !== false) {
                                    $badgeClass = 'success';
                                } elseif (strpos($resultado, 'rechazado') !== false) {
                                    $badgeClass = 'danger';
                                } elseif (strpos($resultado, 'pendiente') !== false) {
                                    $badgeClass = 'warning';
                                }
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>"><?= esc(ucfirst($resultado)) ?></span>
                            </td>
                            <td class="text-nowrap">
                                <a href="<?= base_url('inspeccion/ver/' . $item['id']) ?>" 
                                   class="btn btn-accion" 
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('inspeccion/editar/' . $item['id']) ?>" 
                                   class="btn btn-accion" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-accion btn-evaluar" 
                                        title="Evaluar"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#evaluarModal"
                                        data-id="<?= $item['id'] ?>"
                                        data-inspeccion="<?= esc($item['numero_inspeccion'], 'attr') ?>">
                                    <i class="fas fa-clipboard-check"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay inspecciones registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Evaluación -->
<div class="modal fade" id="evaluarModal" tabindex="-1" aria-labelledby="evaluarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="evaluarModalLabel">Evaluar Inspección</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <?= form_open('inspeccion/evaluar', ['id' => 'formEvaluar']) ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="inspeccion_id">
                    <div class="mb-3">
                        <label class="form-label">No. Inspección</label>
                        <input type="text" class="form-control" id="numero_inspeccion" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resultado</label>
                        <select class="form-select" name="resultado" required>
                            <option value="">Seleccione un resultado</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3"></textarea>
                    </div>
                    
                    <!-- Sección de defectos (inicialmente oculta) -->
                    <div id="defectosContainer" style="display: none;">
                        <hr>
                        <h5>Registrar Defecto</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Defecto</label>
                                    <select class="form-select" name="tipo_defecto" id="tipo_defecto">
                                        <option value="">Seleccione un tipo</option>
                                        <option value="critico">Crítico</option>
                                        <option value="mayor">Mayor</option>
                                        <option value="menor">Menor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cantidad de Piezas con Defecto</label>
                                    <input type="number" class="form-control" name="cantidad_defectos" min="1" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción del Defecto</label>
                            <textarea class="form-control" name="descripcion_defecto" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Acción Correctiva</label>
                            <textarea class="form-control" name="accion_correctiva" rows="2"></textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="agregarDefecto">
                            <i class="fas fa-plus me-1"></i> Agregar Otro Defecto
                        </button>
                        
                        <!-- Tabla de defectos registrados -->
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDefectos">
                                    <!-- Los defectos se agregarán aquí dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="defectos" id="defectosJson" value="[]">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Evaluación</button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- JS Bootstrap + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Helpers de exportación (Buttons) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function () {
        const langES = {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_",
            "sInfoEmpty":      "Mostrando 0 a 0 de 0",
            "sInfoFiltered":   "(filtrado de _MAX_)",
            "sSearch":         "Buscar:",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "buttons": { "copy": "Copiar", "colvis": "Columnas" }
        };
        const fecha = new Date().toISOString().slice(0,10);
        const fileName = 'inspecciones_' + fecha;

        var table = $('#tablaInspeccion').DataTable({
            language: langES,
            columnDefs: [{ 
                targets: -1, 
                orderable: false, 
                searchable: false 
            }],
            dom:
                "<'row mb-3'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { 
                    extend: 'copy',  
                    text: 'Copy',  
                    className: 'btn',
                    exportOptions: { 
                        columns: ':not(:last-child)' 
                    } 
                },
                { 
                    extend: 'csv',   
                    text: 'CSV',   
                    className: 'btn',
                    filename: fileName, 
                    exportOptions: { 
                        columns: ':not(:last-child)' 
                    } 
                },
                { 
                    extend: 'excel', 
                    text: 'Excel', 
                    className: 'btn',
                    filename: fileName, 
                    exportOptions: { 
                        columns: ':not(:last-child)' 
                    } 
                },
                { 
                    extend: 'pdf',   
                    text: 'PDF',   
                    className: 'btn',
                    filename: fileName, 
                    title: 'Inspecciones - ' + fecha,
                    orientation: 'landscape', 
                    pageSize: 'A4',
                    exportOptions: { 
                        columns: ':not(:last-child)' 
                    } 
                },
                { 
                    extend: 'print', 
                    text: 'Print', 
                    className: 'btn',
                    exportOptions: { 
                        columns: ':not(:last-child)' 
                    } 
                }
            ],
            order: [[5, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
        });

        // Fix for Bootstrap 5 modal compatibility
        table.on('draw', function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Evaluar button functionality
        $('.btn-evaluar').on('click', function() {
            const id = $(this).data('id');
            const inspeccion = $(this).data('inspeccion');
            
            $('#inspeccion_id').val(id);
            $('#numero_inspeccion').val(inspeccion);
            
            // Reset form
            $('#formEvaluar')[0].reset();
            $('#defectosContainer').hide();
            $('#tablaDefectos').empty();
            $('#defectosJson').val('[]');
        });

        // Show/hide defectos section based on resultado
        $('select[name="resultado"]').on('change', function() {
            if ($(this).val() === 'rechazado') {
                $('#defectosContainer').show();
            } else {
                $('#defectosContainer').hide();
            }
        });

        // Agregar defecto functionality
        $('#agregarDefecto').on('click', function() {
            const tipo = $('#tipo_defecto').val();
            const descripcion = $('textarea[name="descripcion_defecto"]').val();
            const cantidad = $('input[name="cantidad_defectos"]').val();
            const accion = $('textarea[name="accion_correctiva"]').val();

            if (!tipo || !descripcion) {
                alert('Por favor complete el tipo y descripción del defecto');
                return;
            }

            const defectoId = Date.now();
            const newRow = `
                <tr id="defecto-${defectoId}">
                    <td>${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</td>
                    <td>${descripcion}</td>
                    <td>${cantidad}</td>
                    <td>${accion || 'N/A'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-defecto" data-id="${defectoId}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#tablaDefectos').append(newRow);
            
            // Update JSON hidden field
            updateDefectosJson();
            
            // Clear form fields
            $('textarea[name="descripcion_defecto"]').val('');
            $('textarea[name="accion_correctiva"]').val('');
            $('input[name="cantidad_defectos"]').val('1');
        });

        // Eliminar defecto
        $(document).on('click', '.btn-eliminar-defecto', function() {
            const defectoId = $(this).data('id');
            $(`#defecto-${defectoId}`).remove();
            updateDefectosJson();
        });

        function updateDefectosJson() {
            const defectos = [];
            $('#tablaDefectos tr').each(function() {
                const cells = $(this).find('td');
                if (cells.length > 0) {
                    defectos.push({
                        tipo: $(cells[0]).text(),
                        descripcion: $(cells[1]).text(),
                        cantidad: $(cells[2]).text(),
                        accion_correctiva: $(cells[3]).text()
                    });
                }
            });
            $('#defectosJson').val(JSON.stringify(defectos));
        }
    });
</script>
<?= $this->endSection() ?>