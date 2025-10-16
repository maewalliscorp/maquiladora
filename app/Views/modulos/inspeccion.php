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
                    <th>No.</th>
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
                            <td>
                                <select class="form-select form-select-sm punto-inspeccion-select"
                                        data-inspeccion-id="<?= $item['id'] ?>"
                                        style="min-width: 150px;">
                                    <?php foreach ($puntosInspeccion as $punto): ?>
                                        <option value="<?= $punto['id'] ?>"
                                                <?= ($item['puntoInspeccionId'] == $punto['tipo']) ? 'selected' : '' ?>>
                                            <?= esc($punto['tipo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
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
                                <button type="button"
                                        class="btn btn-accion btn-evaluar"
                                        title="Evaluar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#evaluarModal"
                                        data-id="<?= $item['id'] ?>"
                                        data-inspeccion="<?= esc($item['numero_inspeccion'], 'attr') ?>"
                                        data-orden-produccion="<?= esc($item['ordenProduccionId'] ?? 'N/A', 'attr') ?>"
                                        data-punto-inspeccion="<?= esc($item['puntoInspeccionId'] ?? 'N/A', 'attr') ?>"
                                        data-inspector="<?= esc($item['inspectorId'] ?? 'N/A', 'attr') ?>"
                                        data-fecha="<?= !empty($item['fecha']) ? date('d/m/Y', strtotime($item['fecha'])) : 'N/A' ?>"
                                        data-resultado="<?= strtolower($item['resultado'] ?? 'pendiente') ?>"
                                        data-observaciones="<?= esc($item['observaciones'] ?? '', 'attr') ?>"
                                        data-defectos="<?= !empty($item['defectos']) ? esc(json_encode($item['defectos']), 'attr') : '[]' ?>">
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="evaluarModalLabel">Evaluar Inspección</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <?= form_open('inspeccion/evaluar', ['id' => 'formEvaluar']) ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="inspeccion_id">

                    <!-- Encabezado de la inspección -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>No. Inspección:</strong>
                                <div id="numero_inspeccion_display" class="fw-bold"></div>
                            </div>
                            <div class="mb-2">
                                <strong>Orden de Producción:</strong>
                                <div id="orden_produccion_display"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Punto de Inspección:</strong>
                                <div id="punto_inspeccion_display"></div>
                            </div>
                            <div class="mb-2">
                                <strong>Fecha:</strong>
                                <div id="fecha_display"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultado de la evaluación -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Resultado de la Inspección</label>
                        <select class="form-select" name="resultado" id="resultado_select" required>
                            <option value="">Seleccione un resultado</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea class="form-control" name="observaciones" id="observaciones_text" rows="3"
                                  placeholder="Ingrese las observaciones de la inspección"></textarea>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                            <tr>
                                <th width="15%">Tipo</th>
                                <th>Descripción</th>
                                <th width="10%" class="text-center">Cantidad</th>
                                <th>Acción Correctiva</th>
                                <th width="5%" class="text-center">Acción</th>
                            </tr>
                            </thead>
                            <tbody id="tablaDefectos">
                            <!-- Los nuevos defectos se agregarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="defectos" id="defectosJson" value="[]">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cerrar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
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

        // Manejar la carga de datos en el modal de evaluación
        $('.btn-evaluar').on('click', function() {
            // Obtener los datos de la inspección del botón
            const $btn = $(this);
            const id = $btn.data('id');
            const numeroInspeccion = $btn.data('inspeccion');
            const ordenProduccion = $btn.data('orden-produccion');
            const puntoInspeccion = $btn.data('punto-inspeccion');
            const inspector = $btn.data('inspector');
            const fecha = $btn.data('fecha');
            const resultado = $btn.data('resultado');
            const observaciones = $btn.data('observaciones') || '';

            // Llenar el formulario con los datos
            $('#inspeccion_id').val(id);

            // Actualizar la información de visualización
            $('#numero_inspeccion_display').text(numeroInspeccion);
            $('#orden_produccion_display').text(ordenProduccion);
            $('#punto_inspeccion_display').text(puntoInspeccion);
            $('#fecha_display').text(fecha);

            // Establecer valores del formulario
            $('#resultado_select').val(resultado);
            $('#observaciones_text').val(observaciones);

            // Cargar defectos existentes si los hay
            if (resultado === 'rechazado' && $btn.data('defectos')) {
                const defectosExistentes = JSON.parse($btn.data('defectos'));
                defectos.length = 0; // Limpiar array actual
                defectos.push(...defectosExistentes);
                actualizarTablaDefectos();
            } else {
                defectos.length = 0; // Limpiar array de defectos
                $('#tablaDefectos').empty();
            }

            // Mostrar/ocultar la sección de defectos según el resultado
            if (resultado === 'rechazado') {
                $('#defectosContainer').show();
            } else {
                $('#defectosContainer').hide();
            }
        });

        // Mostrar/ocultar la sección de defectos cuando cambia el resultado
        $('#resultado_select').on('change', function() {
            if ($(this).val() === 'rechazado') {
                $('#defectosContainer').show();
            } else {
                $('#defectosContainer').hide();
            }
        });

        // Inicializar el array de defectos
        const defectos = [];

        // Función para actualizar la tabla de defectos
        function actualizarTablaDefectos() {
            const tbody = $('#tablaDefectos');
            tbody.empty();

            defectos.forEach((defecto, index) => {
                const tr = $(`
                <tr>
                    <td>${defecto.tipo ? defecto.tipo.charAt(0).toUpperCase() + defecto.tipo.slice(1) : ''}</td>
                    <td>${defecto.descripcion || ''}</td>
                    <td class="text-center">${defecto.cantidad || ''}</td>
                    <td>${defecto.accion_correctiva || defecto.accion || ''}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-defecto"
                            data-index="${index}" title="Eliminar defecto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);

                tbody.append(tr);
            });

            // Actualizar el campo oculto con el JSON de los defectos
            $('#defectosJson').val(JSON.stringify(defectos));
        }

        // Agregar un nuevo defecto
        $('#agregarDefecto').on('click', function() {
            const tipo = $('#tipo_defecto').val();
            const descripcion = $('textarea[name="descripcion_defecto"]').val();
            const cantidad = $('input[name="cantidad_defectos"]').val();
            const accion = $('input[name="accion_correctiva"]').val();

            if (tipo && descripcion && cantidad) {
                const defecto = {
                    tipo: tipo,
                    descripcion: descripcion,
                    cantidad: cantidad,
                    accion_correctiva: accion || ''
                };

                defectos.push(defecto);
                actualizarTablaDefectos();

                // Limpiar campos
                $('#tipo_defecto').val('');
                $('textarea[name="descripcion_defecto"]').val('');
                $('input[name="cantidad_defectos"]').val('1');
                $('input[name="accion_correctiva"]').val('');

                // Enfocar el primer campo para facilitar la entrada rápida
                $('#tipo_defecto').focus();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor complete los campos obligatorios: Tipo de Defecto, Descripción y Cantidad',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });

        // Eliminar defecto
        $(document).on('click', '.btn-eliminar-defecto', function() {
            const index = $(this).data('index');
            defectos.splice(index, 1);
            actualizarTablaDefectos();
        });

        // Limpiar el modal cuando se cierre
        $('#evaluarModal').on('hidden.bs.modal', function () {
            defectos.length = 0; // Vaciar el array de defectos
            $('#tablaDefectos').empty();
            $('#defectosJson').val('[]');
            $('form#formEvaluar')[0].reset();
            $('#defectosContainer').hide();
        });

        // Manejar el envío del formulario
        $('#formEvaluar').on('submit', function(e) {
            e.preventDefault();

            // Validar que si es rechazado, tenga al menos un defecto
            if ($('#resultado_select').val() === 'rechazado' && defectos.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Defectos requeridos',
                    text: 'Debe registrar al menos un defecto para una inspección rechazada',
                    confirmButtonColor: '#0d6efd'
                });
                return false;
            }

            // Mostrar indicador de carga
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

            // Enviar el formulario vía AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message || 'La inspección se ha guardado correctamente',
                            confirmButtonColor: '#198754',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        throw new Error(response.message || 'Error al guardar la inspección');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al procesar la solicitud';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        // Manejar el cambio de punto de inspección
        $(document).on('change', '.punto-inspeccion-select', function() {
            const select = $(this);
            const inspeccionId = select.data('inspeccion-id');
            const nuevoPuntoId = select.val();

            // Mostrar indicador de carga
            const originalHtml = select.html();
            select.prop('disabled', true).addClass('opacity-75');

            // Enviar la actualización al servidor
            $.ajax({
                url: '<?= base_url('inspeccion/actualizar-punto') ?>',
                method: 'POST',
                data: {
                    id: inspeccionId,
                    puntoInspeccionId: nuevoPuntoId,
                    <?= csrf_token()?>: '<?= csrf_hash()?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Actualizar el texto mostrado en la tabla
                        select.find('option:selected').text(response.punto_tipo);

                        // Mostrar notificación de éxito
                        const toast = new bootstrap.Toast(document.querySelector('.toast'));
                        $('.toast .toast-body').text('Punto de inspección actualizado correctamente');
                        $('.toast').toast('show');
                    } else {
                        // Mostrar error
                        alert('Error al actualizar el punto de inspección: ' + (response.message || 'Error desconocido'));
                        // Recargar la página para restaurar los valores
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la petición AJAX:', error);
                    alert('Error al conectar con el servidor');
                    location.reload();
                },
                complete: function() {
                    select.prop('disabled', false).removeClass('opacity-75');
                }
            });
        });
    </script>

    <!-- Toast para notificaciones -->
    <div class="toast position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
        <div class="toast-header">
            <strong class="me-auto">Sistema</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>

<?= $this->endSection() ?>