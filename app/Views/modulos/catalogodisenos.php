<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="me-3">CATÁLOGO DE DISEÑOS</h1>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoDisenoModal">
            <i class="bi bi-plus-circle"></i> NUEVO DISEÑO
        </button>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <table id="tablaDisenos" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>VERSIÓN</th>
                    <th>MATERIALES</th>
                    <th>ACCIONES</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($disenos)): ?>
                    <?php foreach ($disenos as $d): ?>
                        <tr>
                            <td><?= esc($d['id']) ?></td>
                            <td><strong><?= esc($d['nombre']) ?></strong></td>
                            <td class="text-start"><?= esc($d['descripcion']) ?></td>
                            <td><?= esc($d['version']) ?></td>
                            <td class="text-start">
                                <?php if (!empty($d['materiales'])): ?>
                                    <ul class="material-list">
                                        <?php foreach ($d['materiales'] as $m): ?>
                                            <li><?= esc($m) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <em>Sin materiales</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary btn-ver-modal"
                                        data-id="<?= (int)$d['id'] ?>"
                                        data-nombre="<?= esc($d['nombre']) ?>"
                                        data-descripcion="<?= esc($d['descripcion']) ?>"
                                        data-version="<?= esc($d['version']) ?>"
                                        data-materiales='<?= esc(!empty($d['materiales']) ? implode(", ", $d['materiales']) : "Sin materiales") ?>'
                                        data-imagen="<?= isset($d['imagen']) ? esc($d['imagen']) : '' ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#disenoModal">
                                    <i class="bi bi-eye"></i> Ver
                                </button>
                                <a href="<?= base_url('modulo2/editardiseno/' . $d['id']) ?>"
                                   class="btn btn-sm btn-outline-primary me-1 btn-accion" title="Editar"
                                   data-id="<?= (int)$d['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar"
                                        data-id="<?= (int)$d['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Bootstrap: Nuevo Diseño -->
    <div class="modal fade" id="nuevoDisenoModal" tabindex="-1" aria-labelledby="nuevoDisenoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoDisenoLabel">Nuevo diseño</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoDiseno">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Código</label>
                                <input type="text" name="codigo" class="form-control" />
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nombre<span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Versión<span class="text-danger">*</span></label>
                                <input type="text" name="version" class="form-control" placeholder="v1.0" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notas</label>
                                <input type="text" name="notas" class="form-control" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Archivo CAD (subir cualquier formato)</label>
                                <input type="file" name="archivoCadFile" class="form-control" />
                                <small class="text-muted">Opcional: si no subes archivo, puedes poner URL manual.</small>
                                <input type="text" name="archivoCadUrl" class="form-control mt-1" placeholder="/archivos/cad/archivo.dxf" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Archivo Patrón (subir cualquier formato)</label>
                                <input type="file" name="archivoPatronFile" class="form-control" />
                                <small class="text-muted">Opcional: si no subes archivo, puedes poner URL manual.</small>
                                <input type="text" name="archivoPatronUrl" class="form-control mt-1" placeholder="/archivos/patron/archivo.pdf" />
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="aprobadoCheck" name="aprobado">
                                    <label class="form-check-label" for="aprobadoCheck">Aprobado</label>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label">Materiales (desde artículos)</label>
                                <select id="selectArticulos" class="form-select" multiple size="6"></select>
                                <small class="text-muted">Selecciona uno o varios. Luego indica cantidades abajo.</small>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width:60%">Artículo</th>
                                                <th style="width:20%">Cantidad por unidad</th>
                                                <th style="width:20%">Merma % (opc)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblMaterialesBody">
                                            <tr class="text-muted" id="rowMaterialesEmpty"><td colspan="3">Sin materiales seleccionados</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div id="nuevoDisenoAlert" class="alert alert-danger mt-3 d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarDiseno" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap: Detalles del diseño -->
    <div class="modal fade" id="disenoModal" tabindex="-1" aria-labelledby="disenoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="disenoModalLabel">Detalles del diseño</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="text-center mb-3 text-dark" id="m-imagen-wrap" style="display:none;">
                        <img id="m-imagen" src="" alt="Imagen del diseño" class="img-fluid rounded border text-dark" />
                    </div>
                    <dl class="row mb-0 text-dark">
                        <dt class="col-sm-3 fw-semibold text-dark">ID</dt>
                        <dd class="col-sm-9 text-dark" id="m-id">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Código</dt>
                        <dd class="col-sm-9 text-dark" id="m-codigo">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Nombre</dt>
                        <dd class="col-sm-9 text-dark" id="m-nombre">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Descripción</dt>
                        <dd class="col-sm-9 text-dark" id="m-descripcion">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Versión</dt>
                        <dd class="col-sm-9 text-dark" id="m-version">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Fecha versión</dt>
                        <dd class="col-sm-9 text-dark" id="m-fecha">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Notas</dt>
                        <dd class="col-sm-9 text-dark" id="m-notas">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Materiales</dt>
                        <dd class="col-sm-9 text-dark" id="m-materiales">-</dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Vista previa CAD</dt>
                        <dd class="col-sm-9 text-dark" id="m-cad">
                            <div id="m-cad-view" style="display:none;">
                                <img id="m-cad-img" src="" alt="CAD" class="img-fluid rounded border mb-2" style="max-height:300px; display:none;" />
                                <object id="m-cad-pdf" data="" type="application/pdf" width="100%" height="320" style="display:none;">
                                    <div class="text-muted">No se pudo mostrar el PDF.</div>
                                </object>
                                <div id="m-cad-fallback" class="text-muted" style="display:none;"></div>
                            </div>
                            <div id="m-cad-empty">-</div>
                        </dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Vista previa Patrón</dt>
                        <dd class="col-sm-9 text-dark" id="m-patron">
                            <div id="m-patron-view" style="display:none;">
                                <img id="m-patron-img" src="" alt="Patrón" class="img-fluid rounded border mb-2" style="max-height:300px; display:none;" />
                                <object id="m-patron-pdf" data="" type="application/pdf" width="100%" height="320" style="display:none;">
                                    <div class="text-muted">No se pudo mostrar el PDF.</div>
                                </object>
                                <div id="m-patron-fallback" class="text-muted" style="display:none;"></div>
                            </div>
                            <div id="m-patron-empty">-</div>
                        </dd>
                        <dt class="col-sm-3 fw-semibold text-dark">Aprobado</dt>
                        <dd class="col-sm-9 text-dark" id="m-aprobado">-</dd>
                    </dl>

                    <!-- Carrusel de vistas previas (Imagen/CAD/Patrón) -->
                    <div id="m-carousel" class="carousel slide mt-3" data-bs-ride="carousel" style="display:none;">
                        <div class="carousel-indicators" id="m-carousel-indicators"></div>
                        <div class="carousel-inner" id="m-carousel-inner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#m-carousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#m-carousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="m-editar" href="#" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- JS Bootstrap + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Visores para DXF (best-effort). Si no cargan, caerá en fallback de texto. -->
    <script src="https://unpkg.com/three@0.158.0/build/three.min.js"></script>
    <script src="https://unpkg.com/dxf-parser@4.11.5/dist/dxf-parser.min.js"></script>
    <script src="https://unpkg.com/three-dxf@1.0.0/dist/three-dxf.js"></script>

    <script>
        // Helper: renderizar DXF si hay librerías disponibles
        function renderDXF(containerId, url) {
            const el = document.getElementById(containerId);
            if (!el) return;
            try {
                // Opción 1: three-dxf con DXF.Viewer
                if (window.THREE && window.DXF && typeof window.DXF.Viewer === 'function') {
                    new window.DXF.Viewer(el, url);
                    return;
                }
                // Opción 2: algún visor global DxfViewer (wrapper)
                if (window.THREE && window.DxfViewer) {
                    const viewer = new window.DxfViewer(el, { autoResize: true, clearColor: 0xffffff });
                    if (typeof viewer.load === 'function') viewer.load(url);
                    return;
                }
                // Opción 3: Solo DxfParser disponible (mostrar mensaje básico)
                if (window.DxfParser) {
                    fetch(url).then(r => r.text()).then(txt => {
                        try {
                            const parser = new window.DxfParser();
                            parser.parseSync(txt); // Validación mínima
                            el.innerHTML = '<div class="p-3 text-muted">DXF cargado (se requieren librerías de render para ver la geometría).</div>';
                        } catch (e) {
                            el.innerHTML = '<div class="p-3 text-muted">No fue posible interpretar el DXF.</div>';
                        }
                    }).catch(() => {
                        el.innerHTML = '<div class="p-3 text-muted">No fue posible descargar el DXF.</div>';
                    });

            // Cargar artículos al abrir el modal (una sola vez por sesión)
            let articulosCache = null;
            const renderMateriales = () => {
                const $sel = $('#selectArticulos');
                const selected = $sel.val() || [];
                const $tb = $('#tblMaterialesBody');
                $tb.empty();
                if (selected.length === 0) {
                    $tb.append('<tr class="text-muted" id="rowMaterialesEmpty"><td colspan="3">Sin materiales seleccionados</td></tr>');
                    return;
                }
                selected.forEach(id => {
                    const art = (articulosCache || []).find(a => String(a.id) === String(id));
                    const nombre = art ? (art.nombre + (art.unidadMedida ? ' ('+art.unidadMedida+')' : '')) : ('ID ' + id);
                    const row = `
                        <tr data-id="${id}">
                            <td>${nombre}</td>
                            <td><input type="number" min="0" step="0.0001" class="form-control form-control-sm inp-cant" placeholder="0" /></td>
                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm inp-merma" placeholder="0" /></td>
                        </tr>`;
                    $tb.append(row);
                });
            };

            function cargarArticulos(){
                const url = '<?= base_url('modulo2/articulos/json') ?>';
                const $sel = $('#selectArticulos');
                $sel.empty().append('<option disabled>Cargando artículos…</option>');
                return $.getJSON(url)
                    .done(function(resp){
                        articulosCache = (resp && resp.items) ? resp.items : [];
                        $sel.empty();
                        if (!articulosCache || articulosCache.length === 0) {
                            $sel.append('<option disabled>No hay artículos disponibles</option>');
                            return;
                        }
                        articulosCache.forEach(a => {
                            const label = (a.nombre || ('ID '+a.id)) + (a.unidadMedida ? ' ('+a.unidadMedida+')' : '') + (a.sku ? ' • ' + a.sku : '');
                            $sel.append('<option value="'+a.id+'">'+label+'</option>');
                        });
                    })
                    .fail(function(xhr){
                        $sel.empty().append('<option disabled>Error cargando artículos</option>');
                        console.error('Error articulos/json', xhr && (xhr.responseText || xhr.statusText));
                    });
            }

            $('#nuevoDisenoModal').on('shown.bs.modal', function(){
                if (articulosCache === null) {
                    cargarArticulos();
                }
            });

            $(document).on('change', '#selectArticulos', renderMateriales);

            // Guardar nuevo diseño por AJAX (multipart, con archivos)
            $('#btnGuardarDiseno').on('click', function(){
                const $alert = $('#nuevoDisenoAlert');
                $alert.addClass('d-none').text('');

                const formEl = document.getElementById('formNuevoDiseno');
                const fd = new FormData(formEl);

                // Construir materials JSON a partir de la tabla
                const materials = [];
                $('#tblMaterialesBody tr').each(function(){
                    const id = $(this).data('id');
                    if (!id) return;
                    const cant = parseFloat($(this).find('.inp-cant').val() || '0');
                    const merma = $(this).find('.inp-merma').val();
                    const mermaNum = merma === '' ? null : parseFloat(merma);
                    materials.push({ articuloId: parseInt(id,10), cantidadPorUnidad: isNaN(cant)?0:cant, mermaPct: (mermaNum===null||isNaN(mermaNum))?null:mermaNum });
                });
                if (materials.length > 0) {
                    fd.append('materials', JSON.stringify(materials));
                }

                $.ajax({
                    url: '<?= base_url('modulo2/disenos/crear') ?>',
                    method: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                }).done(function(resp){
                    if (resp && resp.ok) {
                        const modalEl = document.getElementById('nuevoDisenoModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        window.location.reload();
                    } else {
                        $alert.removeClass('d-none').text(resp && resp.message ? resp.message : 'No se pudo guardar.');
                    }
                }).fail(function(xhr){
                    let msg = 'Error al guardar.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $alert.removeClass('d-none').text(msg);
                });
            });
                    return;
                }
                // Fallback final
                el.innerHTML = 'Vista previa DXF no disponible en este navegador.';
            } catch (e) {
                el.innerHTML = 'Error cargando DXF.';
            }
        }
        $(document).ready(function () {
            $('#tablaDisenos').DataTable({
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad"
                    }
                }
            });

            // Eventos para los botones de acción
            $('.btn-accion').on('click', function() {
                const action = $(this).attr('title');
                const id = $(this).data('id');

                if (action === 'Editar') {
                    // Redirigir a la página de edición con el ID del diseño
                    window.location.href = '<?= base_url('modulo2/editardiseno/') ?>' + id;
                } else if (action === 'Eliminar') {
                    if (confirm('¿Estás seguro de que deseas eliminar este diseño?')) {
                        // Lógica para eliminar
                        alert('Diseño con ID ' + id + ' eliminado (simulación)');
                    }
                }
            });

            // Abrir y poblar el modal de detalles (AJAX JSON)
            $(document).on('click', '.btn-ver-modal', function () {
                const id = $(this).data('id');

                // Limpiar contenido mientras carga
                $('#m-id').text('...');
                $('#m-codigo').text('...');
                $('#m-nombre').text('...');
                $('#m-descripcion').text('Cargando...');
                $('#m-version').text('...');
                $('#m-fecha').text('...');
                $('#m-notas').text('...');
                $('#m-materiales').text('...');
                // Reset carrusel
                const $ci = $('#m-carousel-inner');
                const $ind = $('#m-carousel-indicators');
                $ci.empty(); $ind.empty();
                $('#m-carousel').hide();
                $('#m-aprobado').text('-');
                $('#m-imagen').attr('src', '');
                $('#m-imagen-wrap').hide();
                $('#m-editar').attr('href', '#');

                $.getJSON('<?= base_url('modulo2/diseno') ?>/' + id + '/json')
                    .done(function (data) {
                        $('#m-id').text(data.id || id);
                        $('#m-codigo').text(data.codigo || '-');
                        $('#m-nombre').text(data.nombre || '-');
                        $('#m-descripcion').text(data.descripcion || '-');
                        $('#m-version').text(data.version || '-');
                        $('#m-fecha').text(data.fecha ? (new Date(data.fecha)).toISOString().slice(0,10) : '-');
                        $('#m-notas').text(data.notas || '-');
                        $('#m-materiales').text((data.materiales || []).join(', '));
                        // Helpers y armado de carrusel
                        const isImage = (u) => /\.(png|jpg|jpeg|gif|bmp|webp|svg)$/i.test(u || '');
                        const isPdf   = (u) => /\.(pdf)$/i.test(u || '');
                        const isDxf   = (u) => /\.(dxf)$/i.test(u || '');

                        let slides = [];

                        // Imagen principal si existe
                        if (data.imagenUrl && isImage(data.imagenUrl)) {
                            slides.push({
                                title: 'Imagen',
                                html: '<div class="text-center"><img src="'+data.imagenUrl+'" class="img-fluid rounded border" style="max-height:420px;" alt="Imagen"/></div>'
                            });
                        }

                        // CAD
                        if (data.archivoCadUrl) {
                            if (isImage(data.archivoCadUrl)) {
                                slides.push({title:'CAD (imagen)', html:'<div class="text-center"><img src="'+data.archivoCadUrl+'" class="img-fluid rounded border" style="max-height:420px;" alt="CAD"/></div>'});
                            } else if (isPdf(data.archivoCadUrl)) {
                                slides.push({title:'CAD (PDF)', html:'<object data="'+data.archivoCadUrl+'" type="application/pdf" width="100%" height="450"><div class="text-muted p-3">No se pudo mostrar el PDF CAD.</div></object>'});
                            } else if (isDxf(data.archivoCadUrl)) {
                                const dxfId = 'dxf-cad-'+Date.now();
                                slides.push({title:'CAD (DXF)', html:'<div id="'+dxfId+'" style="height:450px; background:#f8f9fa;" class="rounded border d-flex align-items-center justify-content-center">Cargando DXF…</div>', afterMount: function(){ renderDXF(dxfId, data.archivoCadUrl); }});
                            } else {
                                slides.push({title:'CAD', html:'<div class="p-3 text-muted">Archivo CAD: '+ data.archivoCadUrl +'</div>'});
                            }
                        }

                        // Patrón
                        if (data.archivoPatronUrl) {
                            if (isImage(data.archivoPatronUrl)) {
                                slides.push({title:'Patrón (imagen)', html:'<div class="text-center"><img src="'+data.archivoPatronUrl+'" class="img-fluid rounded border" style="max-height:420px;" alt="Patrón"/></div>'});
                            } else if (isPdf(data.archivoPatronUrl)) {
                                slides.push({title:'Patrón (PDF)', html:'<object data="'+data.archivoPatronUrl+'" type="application/pdf" width="100%" height="450"><div class="text-muted p-3">No se pudo mostrar el PDF Patrón.</div></object>'});
                            } else if (isDxf(data.archivoPatronUrl)) {
                                const dxfId2 = 'dxf-patron-'+Date.now();
                                slides.push({title:'Patrón (DXF)', html:'<div id="'+dxfId2+'" style="height:450px; background:#f8f9fa;" class="rounded border d-flex align-items-center justify-content-center">Cargando DXF…</div>', afterMount: function(){ renderDXF(dxfId2, data.archivoPatronUrl); }});
                            } else {
                                slides.push({title:'Patrón', html:'<div class="p-3 text-muted">Archivo Patrón: '+ data.archivoPatronUrl +'</div>'});
                            }
                        }

                        // Montar carrusel si hay al menos 1 slide
                        if (slides.length > 0) {
                            slides.forEach((s, idx) => {
                                const active = idx === 0 ? ' active' : '';
                                $ci.append('<div class="carousel-item'+active+'">'+ s.html +'</div>');
                                $ind.append('<button type="button" data-bs-target="#m-carousel" data-bs-slide-to="'+idx+'" '+(idx===0?'class="active" aria-current="true"':'')+' aria-label="'+(s.title||('Slide '+(idx+1)))+'"></button>');
                            });
                            $('#m-carousel').show();
                            // Ejecutar afterMount si existe (para DXF)
                            setTimeout(() => { slides.forEach(s => { if (typeof s.afterMount === 'function') s.afterMount(); }); }, 50);
                        }
                        const apr = data.aprobado;
                        $('#m-aprobado').text(apr === 1 || apr === true || apr === '1' ? 'Sí' : (apr === 0 || apr === false || apr === '0' ? 'No' : '-'));
                        $('#m-editar').attr('href', '<?= base_url('modulo2/editardiseno/') ?>' + id);

                        if (data.imagenUrl) {
                            $('#m-imagen').attr('src', data.imagenUrl);
                            $('#m-imagen-wrap').show();
                        }
                    })
                    .fail(function () {
                        $('#m-descripcion').text('No fue posible cargar los datos');
                    });
            });
        });
    </script>
<?= $this->endSection() ?>