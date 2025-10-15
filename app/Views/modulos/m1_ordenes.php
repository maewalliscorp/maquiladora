<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Espaciado y estilo de los botones de exportación */
    .dt-buttons.btn-group .btn{
        margin-right:.5rem;
        border-radius:.375rem !important;
    }
    .dt-buttons.btn-group .btn:last-child{ margin-right:0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Órdenes de Producción</h1>
    {{ ... }}
    <span class="badge bg-primary">Módulo 1</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Órdenes de Producción</strong>
    </div>
    <div class="card-body">
        <table id="tablaOrdenes" class="table table-striped table-bordered text-center align-middle">
            <thead class="table-light">
            <tr>
                <th>OP</th>
                <th>Cliente</th>
                <th>Diseño</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?= esc($orden['op']) ?></td>
                        <td><?= esc($orden['cliente']) ?></td>
                        <td><?= esc($orden['diseno'] ?? '') ?></td>
                        <td><?= esc($orden['ini']) ?></td>
                        <td><?= esc($orden['fin']) ?></td>
                        <td>
                            <?php $estatusActual = trim($orden['estatus'] ?? ''); ?>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="spinner-border spinner-border-sm text-primary op-estatus-saving" role="status" style="display:none;" aria-hidden="true"></div>
                                <select class="form-select form-select-sm op-estatus-select" data-id="<?= esc($orden['opId'] ?? '') ?>" data-prev="<?= esc($estatusActual) ?>" style="min-width: 150px;">
                                    <option value="Planificada" <?= strcasecmp($estatusActual,'Planificada')===0 ? 'selected' : '' ?>>Planificada</option>
                                    <option value="En proceso"  <?= strcasecmp($estatusActual,'En proceso')===0 ? 'selected' : '' ?>>En proceso</option>
                                    <option value="Completada"  <?= strcasecmp($estatusActual,'Completada')===0 ? 'selected' : '' ?>>Completada</option>
                                    <option value="Pausada"     <?= strcasecmp($estatusActual,'Pausada')===0 ? 'selected' : '' ?>>Pausada</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-sm btn-outline-info btn-ver-op" data-folio="<?= esc($orden['op'] ?? '') ?>" data-bs-toggle="modal" data-bs-target="#opDetalleModal">
                                    <i class="bi bi-eye"></i> Ver
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-agregar-op" data-id="<?= esc($orden['opId'] ?? '') ?>" data-folio="<?= esc($orden['op'] ?? '') ?>" data-bs-toggle="modal" data-bs-target="#opAsignacionesModal">
                                    <i class="bi bi-person-plus"></i> Agregar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Detalle OP -->
    <div class="modal fade" id="opDetalleModal" tabindex="-1" aria-labelledby="opDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="opDetalleLabel">Detalle de Orden de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-2">
                        <dt class="col-sm-3">ID</dt><dd class="col-sm-9" id="op-id">-</dd>
                        <dt class="col-sm-3">Folio</dt><dd class="col-sm-9" id="op-folio">-</dd>
                        <dt class="col-sm-3">Estatus</dt><dd class="col-sm-9" id="op-status">-</dd>
                        <dt class="col-sm-3">Cantidad plan</dt><dd class="col-sm-9" id="op-cant">-</dd>
                        <dt class="col-sm-3">Inicio plan</dt><dd class="col-sm-9" id="op-ini">-</dd>
                        <dt class="col-sm-3">Fin plan</dt><dd class="col-sm-9" id="op-fin">-</dd>
                    </dl>
                    <h6 class="mt-3">Diseño</h6>
                    <dl class="row mb-2">
                        <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9" id="op-dis-nombre">-</dd>
                        <dt class="col-sm-3">Versión</dt><dd class="col-sm-9" id="op-dis-version">-</dd>
                        <dt class="col-sm-3">Fecha versión</dt><dd class="col-sm-9" id="op-dis-fecha">-</dd>
                        <dt class="col-sm-3">Aprobado</dt><dd class="col-sm-9" id="op-dis-aprobado">-</dd>
                        <dt class="col-sm-3">Notas</dt><dd class="col-sm-9" id="op-dis-notas">-</dd>
                        <dt class="col-sm-3">Archivos</dt>
                        <dd class="col-sm-9">
                            <div id="op-dis-archivos">
                                <div id="opDisCarousel" class="carousel slide" data-bs-ride="false" style="display:none;">
                                    <div class="carousel-inner"></div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#opDisCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Anterior</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#opDisCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Siguiente</span>
                                    </button>
                                </div>
                                <span id="op-dis-archivos-na" class="text-muted">—</span>
                            </div>
                        </dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asignaciones OP -->
    <div class="modal fade" id="opAsignacionesModal" tabindex="-1" aria-labelledby="opAsignacionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="opAsignacionesLabel">Asignación de Tareas a OP <span id="asg-opid"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Empleados disponibles (marque para asignar)</label>
                            <div id="asg-disponibles-list" class="border rounded p-2" style="max-height:260px; overflow:auto;"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Desde</label>
                            <input type="datetime-local" id="asg-desde" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hasta</label>
                            <input type="datetime-local" id="asg-hasta" class="form-control" />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small text-muted">Seleccione uno o más empleados y luego presione Asignar.</div>
                        <button id="asg-btn-assign-selected" type="button" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Asignar seleccionados
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle" id="asg-tabla">
                            <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Puesto</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <?= $this->endSection() ?>

    <?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Export helpers (Buttons) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function(){
            const langES = {
                sProcessing:"Procesando...",
                sLengthMenu:"Mostrar _MENU_ registros",
                sZeroRecords:"No se encontraron resultados",
                sEmptyTable:"Ningún dato disponible en esta tabla",
                sInfo:"Mostrando _START_ a _END_ de _TOTAL_",
                sInfoEmpty:"Mostrando 0 a 0 de 0",
                sInfoFiltered:"(filtrado de _MAX_ en total)",
                sSearch:"Buscar:",
                oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
                oAria:{ sSortAscending:": Orden asc", sSortDescending:": Orden desc" },
                buttons:{ copy:"Copiar" }
            };
            const fecha = new Date().toISOString().slice(0,10);
            const fileName = 'ordenes_produccion_' + fecha;

            // DataTable + Botones
            $('#tablaOrdenes').DataTable({
                language: langES,
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false } // Acciones
                ],
                dom:
                    "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend:'copy',  text:'Copy',  exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'csv',   text:'CSV',   filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'excel', text:'Excel', filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'pdf',   text:'PDF',   filename:fileName, title:fileName,
                        orientation:'landscape', pageSize:'A4',
                        exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'print', text:'Print', exportOptions:{ columns: ':not(:last-child)' } }
                ]
            });

            // ---------------- Asignaciones ----------------
            function cargarAsignaciones(opId){
                const $modal = $('#opAsignacionesModal');
                $modal.find('#asg-opid').text(opId);
                const $list = $modal.find('#asg-disponibles-list');
                const $tbody = $modal.find('#asg-tabla tbody');
                $list.html('<div class="text-muted">Cargando empleados...</div>');
                $tbody.html('<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>');
                $.getJSON('<?= base_url('modulo1/ordenes') ?>/' + opId + '/asignaciones?t=' + Date.now())
                    .done(function(data){
                        const emps = data.empleados || [];
                        if (!emps.length) {
                            $list.html('<div class="text-muted">No hay empleados disponibles.</div>');
                        } else {
                            const items = emps.map(function(e){
                                const label = (e.noEmpleado?('['+e.noEmpleado+'] '):'') + e.nombre + ' ' + (e.apellido||'');
                                return `<div class="form-check">
                        <input class="form-check-input asg-chk" type="checkbox" value="${e.id}" id="asg-chk-${e.id}">
                        <label class="form-check-label" for="asg-chk-${e.id}">${label}</label>
                      </div>`;
                            });
                            $list.html(items.join(''));
                        }
                        $tbody.empty();
                        if (!data.asignadas || !data.asignadas.length){
                            $tbody.html('<tr><td colspan="5" class="text-center text-muted">Sin asignaciones</td></tr>');
                        } else {
                            data.asignadas.forEach(function(a){
                                const nombre = (a.noEmpleado?('['+a.noEmpleado+'] '):'') + (a.nombre||'') + ' ' + (a.apellido||'');
                                const tr = `<tr>
                <td>${nombre}</td>
                <td>${a.puesto||'-'}</td>
                <td>${a.asignadoDesde||'-'}</td>
                <td>${a.asignadoHasta||'-'}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger asg-del" data-id="${a.id}"><i class="bi bi-trash"></i></button>
                </td>
              </tr>`;
                                $tbody.append(tr);
                            });
                        }
                    })
                    .fail(function(xhr){
                        $tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar asignaciones</td></tr>');
                        console.error('Asignaciones error', xhr?.status, xhr?.responseText);
                    });
            }

            $(document).on('click', '.btn-agregar-op', function(){
                let opId = parseInt($(this).data('id') || 0, 10);
                const folio = ($(this).data('folio')||'').toString();
                const $modal = $('#opAsignacionesModal');
                const setAndLoad = (id) => {
                    $modal.data('opid', id);
                    $('#asg-btn-add').data('opid', id);
                    cargarAsignaciones(id);
                };
                if (opId > 0) { setAndLoad(opId); return; }
                if (folio) {
                    $.getJSON('<?= base_url('modulo1/ordenes/folio') ?>/' + encodeURIComponent(folio) + '/json?t=' + Date.now())
                        .done(function(data){
                            const id = parseInt(data.id||0,10);
                            if (id>0) setAndLoad(id); else alert('No se pudo resolver la OP por folio.');
                        })
                        .fail(function(xhr){
                            console.error('No se pudo resolver OP por folio', folio, xhr?.status, xhr?.responseText);
                            alert('No se pudo resolver la OP por folio.');
                        });
                    return;
                }
                alert('No se pudo determinar el ID de la OP.');
            });

            $('#opAsignacionesModal').on('shown.bs.modal', function(){
                const opId = $(this).data('opid');
                if (opId) cargarAsignaciones(opId);
            });

            $(document).on('click', '#asg-btn-assign-selected', function(){
                const $modal = $('#opAsignacionesModal');
                const opId = $modal.data('opid');
                const desde = $modal.find('#asg-desde').val() || '';
                const hasta = $modal.find('#asg-hasta').val() || '';
                const empleados = $('#asg-disponibles-list .asg-chk:checked')
                    .map(function(){ return parseInt($(this).val(),10); }).get()
                    .filter(n=>!isNaN(n) && n>0);
                if (!opId || empleados.length===0){ alert('Seleccione al menos un empleado.'); return; }
                const url = empleados.length > 1
                    ? '<?= base_url('modulo1/ordenes/asignaciones/agregar-multiple') ?>'
                    : '<?= base_url('modulo1/ordenes/asignaciones/agregar') ?>';
                const payload = empleados.length > 1
                    ? { opId, empleados, desde, hasta }
                    : { opId, empleadoId: empleados[0], desde, hasta };
                $.ajax({ url, method: 'POST', data: payload, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .done(function(){
                        cargarAsignaciones(opId);
                    })
                    .fail(function(xhr){
                        alert('No se pudo asignar: ' + (xhr?.status||''));
                        console.error('asignar fail', xhr?.status, xhr?.responseText);
                    });
            });

            $(document).on('click', '.asg-del', function(){
                const asignacionId = $(this).data('id');
                const opId = $('#opAsignacionesModal').data('opid');
                if (!asignacionId || !opId) return;
                if (!confirm('¿Eliminar esta asignación?')) return;
                $.ajax({
                    url: '<?= base_url('modulo1/ordenes/asignaciones/eliminar') ?>',
                    method: 'POST',
                    data: { asignacionId },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).done(function(){
                    cargarAsignaciones(opId);
                }).fail(function(xhr){
                    alert('No se pudo eliminar: ' + (xhr?.status||''));
                    console.error('eliminar fail', xhr?.status, xhr?.responseText);
                });
            });

            // Guardar estatus inline
            $(document).on('change', '.op-estatus-select', function(){
                const $sel = $(this);
                const id = $sel.data('id');
                const estatus = $sel.val();
                const $td = $sel.closest('td');
                const $spin = $td.find('.op-estatus-saving');
                if (!id || !estatus) return;
                $sel.prop('disabled', true);
                $spin.show();
                $.ajax({
                    url: '<?= base_url('modulo1/ordenes/estatus') ?>',
                    method: 'POST',
                    data: { id, estatus },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).done(function(){
                    $sel.addClass('is-valid');
                    setTimeout(()=> $sel.removeClass('is-valid'), 1200);
                }).fail(function(){
                    const prev = $sel.data('prev') || '';
                    if (prev) $sel.val(prev);
                    $sel.addClass('is-invalid');
                    setTimeout(()=> $sel.removeClass('is-invalid'), 1500);
                    alert('No se pudo actualizar el estatus de la OP.');
                }).always(function(){
                    $sel.data('prev', estatus);
                    $sel.prop('disabled', false);
                    $spin.hide();
                });
            });

            // Ver detalle (modal)
            $(document).on('click', '.btn-ver-op', function(){
                const folio = $(this).data('folio');
                if (!folio) return;
                const $modal = $('#opDetalleModal');
                const $btn = $(this);
                $btn.prop('disabled', true);
                const setText = (sel, val) => $modal.find(sel).text(val);
                setText('#op-id','Cargando...'); setText('#op-folio','Cargando...'); setText('#op-status','Cargando...');
                setText('#op-cant','Cargando...'); setText('#op-ini','Cargando...'); setText('#op-fin','Cargando...');
                setText('#op-dis-nombre','Cargando...'); setText('#op-dis-version','Cargando...'); setText('#op-dis-fecha','Cargando...');
                setText('#op-dis-aprobado','Cargando...'); setText('#op-dis-notas','');

                const $car = $('#opDisCarousel'), $inner = $car.find('.carousel-inner');
                $inner.empty(); $car.hide(); $('#op-dis-archivos-na').show();

                $.getJSON('<?= base_url('modulo1/ordenes/folio') ?>/' + encodeURIComponent(folio) + '/json?t=' + Date.now())
                    .done(function(data){
                        setText('#op-id', data.id ?? '-');
                        setText('#op-folio', data.folio || '-');
                        setText('#op-status', data.status || '-');
                        setText('#op-cant', (data.cantidadPlan ?? '') || '-');
                        setText('#op-ini', data.fechaInicioPlan || '-');
                        setText('#op-fin', data.fechaFinPlan || '-');
                        if (data.diseno){
                            setText('#op-dis-nombre', data.diseno.nombre || '-');
                            setText('#op-dis-version', data.diseno.version || '-');
                            setText('#op-dis-fecha', data.diseno.fecha || '-');
                            const aprobado = (data.diseno.aprobado===1 || data.diseno.aprobado==='1') ? 'Sí'
                                : (data.diseno.aprobado===0 || data.diseno.aprobado==='0' ? 'No' : '-');
                            setText('#op-dis-aprobado', aprobado);
                            setText('#op-dis-notas', data.diseno.notas || '-');

                            const files = [];
                            if (data.diseno.archivoCadUrl) files.push({url: data.diseno.archivoCadUrl, label:'CAD'});
                            if (data.diseno.archivoPatronUrl) files.push({url: data.diseno.archivoPatronUrl, label:'Patrón'});
                            if (Array.isArray(data.diseno.archivos)) {
                                data.diseno.archivos.forEach((u,i)=>{ if(u) files.push({url:u, label:'Archivo '+(i+1)}) });
                            }
                            if (files.length){
                                const buildSlideContent = (url, label) => {
                                    const u = String(url || '');
                                    const extMatch = u.match(/\.([a-z0-9]+)(?:\?|#|$)/i);
                                    const ext = extMatch ? extMatch[1].toLowerCase() : '';
                                    const isImg   = /^(png|jpg|jpeg|gif|webp|bmp|svg)$/.test(ext);
                                    const isPdf   = ext === 'pdf';
                                    const isVideo = /^(mp4|webm|ogv|ogg)$/.test(ext);
                                    const isAudio = /^(mp3|wav|ogg)$/.test(ext);
                                    const isOffice= /^(doc|docx|xls|xlsx|ppt|pptx)$/.test(ext);
                                    const isText  = /^(txt|csv|json|xml|md|log)$/.test(ext);
                                    const isCad   = /^(dwg|dxf|stp|step|igs|iges)$/.test(ext);
                                    const safeUrl = encodeURI(u);

                                    if (isImg)   return `<img src="${safeUrl}" class="d-block w-100" alt="${label}" style="max-height:460px; object-fit:contain; background:#f8f9fa;">`;
                                    if (isPdf)   return `<iframe src="${safeUrl}" class="d-block w-100" style="height:460px; border:0;" title="${label}"></iframe>`;
                                    if (isVideo) return `<video class="d-block w-100" style="max-height:460px; background:#000;" controls src="${safeUrl}"></video>`;
                                    if (isAudio) return `<div class="p-3 text-center bg-light"><audio controls src="${safeUrl}" style="width:100%"></audio><div class="small mt-2">${label}</div></div>`;
                                    if (isOffice){
                                        const gview = 'https://docs.google.com/gview?embedded=1&url=' + encodeURIComponent(u);
                                        return `<iframe src="${gview}" class="d-block w-100" style="height:460px; border:0;" title="${label}"></iframe>`;
                                    }
                                    if (isText)  return `<iframe src="${safeUrl}" class="d-block w-100" style="height:460px; border:0; background:#fff;" title="${label}"></iframe>`;
                                    if (isCad)   return `<div class="p-4 text-center bg-light"><div class="mb-2">Formato CAD no previsualizable en el navegador.</div><a class="btn btn-outline-primary" href="${safeUrl}" target="_blank" rel="noopener">Descargar ${label}</a></div>`;
                                    return `<div class="p-4 text-center bg-light"><div class="mb-2">Previsualización no disponible para este formato.</div><a class="btn btn-outline-primary" href="${safeUrl}" target="_blank" rel="noopener">Abrir / Descargar</a></div>`;
                                };

                                files.forEach((f, idx)=>{
                                    const content = buildSlideContent(f.url, f.label);
                                    $inner.append(`<div class="carousel-item${idx===0?' active':''}">${content}<div class="carousel-caption d-none d-md-block"><span class="badge bg-dark">${f.label}</span></div></div>`);
                                });
                                $('#op-dis-archivos-na').hide();
                                $car.show();
                            }
                        }
                    })
                    .fail(function(xhr){
                        setText('#op-id', '-'); setText('#op-folio', '-');
                        setText('#op-status', 'Error HTTP ' + (xhr?.status || '?'));
                        setText('#op-cant', '-'); setText('#op-ini', '-'); setText('#op-fin', '-');
                        setText('#op-dis-nombre', '-'); setText('#op-dis-version', '-'); setText('#op-dis-fecha', '-');
                        setText('#op-dis-aprobado', '-'); setText('#op-dis-notas', 'No se pudo cargar el detalle de la orden.');
                        console.error('OP detalle error', xhr?.status, xhr?.responseText);
                    })
                    .always(function(){
                        $btn.prop('disabled', false);
                    });
            });
        });
    </script>
    <?= $this->endSection() ?>
