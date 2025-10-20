<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .btn-icon{ padding:.35rem .55rem; border-width:2px; }
    .table thead th{ vertical-align: middle; }
    .badge-pill { border-radius: 1rem; padding:.35rem .6rem; }
    .thead-toolbar th{ background:#f0f6ff; }
    .img-thumb{ width:42px; height:42px; object-fit:cover; border-radius:.5rem; border:1px solid #e5e7eb; }
    .dt-buttons { gap:.5rem; flex-wrap:wrap; }
    .dt-buttons.btn-group>.btn{ border-radius:.65rem!important; margin-left:0!important; padding:.40rem .85rem!important; }
    .hint{ font-size:.85rem; color:#64748b; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Inventario de Almacenes</h1>
        <span class="badge bg-primary">Logística / Almacén</span>
    </div>
    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#agregarModal">
        <i class="bi bi-plus-circle me-1"></i> Agregar
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-header"><strong>Existencias por ubicación</strong></div>
    <div class="card-body">
        <table id="tablaInventario" class="table table-striped table-bordered text-center align-middle w-100">
            <thead>
            <tr class="thead-toolbar">
                <th colspan="10" class="text-start">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto"><label for="selectAlmacen" class="col-form-label fw-semibold">Almacén:</label></div>
                        <div class="col-12 col-sm-4 col-md-3">
                            <select id="selectAlmacen" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach (($almacenes ?? []) as $a): ?>
                                    <option value="<?= (int)$a['id'] ?>"><?= esc($a['codigo'].' - '.$a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto small text-muted"></div>
                    </div>
                </th>
            </tr>
            <tr>
                <th>Almacén</th>
                <th>Ubicación</th>
                <th>SKU</th>
                <th>Artículo</th>
                <th>Unidad</th>
                <th>Cantidad</th>
                <th>Mín</th>
                <th>Máx</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal VER -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 id="detalleModalLabel" class="modal-title">Detalle de inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="p-2 bg-light rounded">
                            <div class="fw-bold">Artículo</div>
                            <div id="dArticulo"></div>
                            <div class="small text-muted">SKU: <span id="dSku"></span> • UM: <span id="dUM"></span></div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <img id="dImg" class="img-thumb ms-md-auto" src="<?= base_url('img/placeholder.png') ?>" alt="img">
                    </div>

                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded">
                            <div class="fw-bold">Almacén / Ubicación</div>
                            <div><span id="dAlmacen"></span> / <span id="dUbicacion"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded">
                            <div class="fw-bold">Lote actual</div>
                            <div>Código: <span id="dLote"></span> • Fab: <span id="dFab"></span> • Cad: <span id="dCad"></span> • Días: <span id="dDias"></span></div>
                            <div class="small text-muted">Notas: <span id="dNotas"></span></div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>Cantidad: <strong id="dCant"></strong> (Mín: <span id="dMin"></span>, Máx: <span id="dMax"></span>)</div>
                    <div id="dEstado"></div>
                </div>

                <div class="mt-3">
                    <h6 class="mb-2">Lotes del artículo</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Lote</th><th>F. Fab.</th><th>F. Cad.</th><th>Días</th></tr></thead>
                            <tbody id="tbodyLotes"></tbody>
                        </table>
                    </div>
                </div>

                <div id="historialWrap" class="mt-3 d-none">
                    <h6 class="mb-2">Últimos movimientos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead><tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Ref</th><th>Notas</th></tr></thead>
                            <tbody id="tbodyMovs"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div></div>
</div>

<!-- Modal AGREGAR -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 id="agregarModalLabel" class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar existencias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="agExistente">
                            <label class="form-check-label" for="agExistente">Artículo en existencia</label>
                        </div>
                        <div class="hint mt-1">
                            Activa para buscar y seleccionar un artículo ya registrado.
                            Si no existe, deja apagado para crear uno nuevo.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Almacén</label>
                        <select id="agAlmacen" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach (($almacenes ?? []) as $a): ?>
                                <option value="<?= (int)$a['id'] ?>"><?= esc($a['codigo'].' - '.$a['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ubicación</label>
                        <select id="agUbicacion" class="form-select"><option value="">Seleccione...</option></select>
                    </div>

                    <!-- Buscador con spinner + datalist -->
                    <input type="hidden" id="agArticuloId">
                    <div class="col-md-8">
                        <label class="form-label">Artículo</label>
                        <div class="input-group align-items-center">
                            <input type="text" id="agArticulo" class="form-control" list="dlArticulo" placeholder="Buscar por nombre o SKU...">
                            <span class="input-group-text bg-transparent border-0" id="agArtSpin" style="display:none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </div>
                        <datalist id="dlArticulo"></datalist>
                        <div class="hint">Escribe 3+ caracteres para buscar (coincide por nombre o SKU).</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nombre del artículo (nuevo)</label>
                        <input type="text" id="agNombre" class="form-control" placeholder="Solo si crearás uno nuevo">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unidad</label>
                        <input type="text" id="agUM" class="form-control" placeholder="MTS, KG, PZ...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" id="agCantidad" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Mín.</label>
                        <input type="number" id="agMin" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Máx.</label>
                        <input type="number" id="agMax" class="form-control" step="0.01">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Lote (opcional)</label>
                        <input type="text" id="agLote" class="form-control" placeholder="Código de lote">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notas de lote (opcional)</label>
                        <input type="text" id="agNotas" class="form-control" placeholder="Observaciones">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agPer">
                            <label class="form-check-label" for="agPer">Producto perecedero (capturar fechas)</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">F. Fabricación</label>
                        <input type="date" id="agFab" class="form-control" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">F. Caducidad</label>
                        <input type="date" id="agCad" class="form-control" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Operación</label>
                        <select id="agOperacion" class="form-select">
                            <option value="sumar">Sumar</option>
                            <option value="restar">Restar</option>
                            <option value="reemplazar">Reemplazar</option>
                        </select>
                    </div>

                    <!-- Resumen existencias del artículo -->
                    <div class="col-12 d-none" id="agExistenciasWrap">
                        <div class="border rounded p-2">
                            <div class="fw-semibold mb-2">Existencias actuales del artículo</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0">
                                    <thead><tr><th>Almacén</th><th>Ubicación</th><th>Lote</th><th>F. Fab.</th><th>F. Cad.</th><th>Cant.</th></tr></thead>
                                    <tbody id="agExistenciasBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" id="btnGuardarAgregar">Guardar</button>
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div></div>
</div>

<!-- Modal EDITAR (igual que antes) -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header">
                <h5 id="editarModalLabel" class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar" class="row g-3">
                    <input type="hidden" id="eStockId"><input type="hidden" id="eArticuloId"><input type="hidden" id="eLoteId">
                    <div class="col-md-6"><label class="form-label">Almacén</label><input type="text" id="eAlmacen" class="form-control" disabled></div>
                    <div class="col-md-6"><label class="form-label">Ubicación</label><select id="eUbicacionId" class="form-select"></select></div>
                    <div class="col-md-4"><label class="form-label">SKU</label><input type="text" id="eSKU" class="form-control" disabled></div>
                    <div class="col-md-4"><label class="form-label">Artículo</label><input type="text" id="eArticuloNombre" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Unidad</label><input type="text" id="eUMEdit" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Mín.</label><input type="number" id="eMin" class="form-control" step="0.01"></div>
                    <div class="col-md-4"><label class="form-label">Máx.</label><input type="number" id="eMax" class="form-control" step="0.01"></div>
                    <div class="col-md-4"><label class="form-label">Cantidad</label><input type="number" id="eCantidad" class="form-control" step="0.01" required></div>
                    <div class="col-md-4"><label class="form-label">Lote</label><input type="text" id="eLoteCodigo" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">F. Fabricación</label><input type="date" id="eFechaFab" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">F. Caducidad</label><input type="date" id="eFechaCad" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Notas de lote</label><input type="text" id="eNotas" class="form-control"></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" type="button" id="btnGuardarEdit">Guardar cambios</button></div>
        </div></div>
</div>

<!-- Modal ERROR / AVISO -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="errorModalLabel" class="modal-title">Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="errorModalMsg">Mensaje…</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Entendido</button>
                <button type="button" class="btn btn-primary" id="btnUsarExistente" style="display:none">Usar artículo existente</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    (function(){
        const langES = {
            sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_", sZeroRecords:"No se encontraron resultados",
            sEmptyTable:"Sin datos", sInfo:"Mostrando _START_–_END_ de _TOTAL_", sInfoEmpty:"Mostrando 0–0 de 0",
            sInfoFiltered:"(filtrado de _MAX_)", sSearch:"Buscar:", oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copy", csv:"CSV", excel:"Excel", pdf:"PDF", print:"Print" }
        };

        $.extend(true, $.fn.dataTable.Buttons.defaults, {
            dom: {
                container: { className: 'dt-buttons btn-group d-inline-flex flex-wrap gap-2' },
                button:    { className: 'btn btn-secondary' }
            }
        });

        const $sel   = $('#selectAlmacen');
        const $tabla = $('#tablaInventario');

        function stockBadge(row){
            const qty = parseFloat(row.cantidad ?? 0);
            const hasMin = row.stockMin !== null && row.stockMin !== undefined && row.stockMin !== '';
            const hasMax = row.stockMax !== null && row.stockMax !== undefined && row.stockMax !== '';
            const min = hasMin ? parseFloat(row.stockMin) : null;
            const max = hasMax ? parseFloat(row.stockMax) : null;

            if(!hasMin && !hasMax) return '<span class="badge badge-pill bg-secondary">Sin rango</span>';
            if(hasMin && qty < min) return '<span class="badge badge-pill bg-warning text-dark">Bajo</span>';
            if(hasMax && qty > max) return '<span class="badge badge-pill bg-danger">Alto</span>';
            return '<span class="badge badge-pill bg-success">OK</span>';
        }
        function caducidadBadge(row){
            const st = row.estadoCaducidad;
            let cls='bg-secondary', txt='Sin fecha';
            if(st==='ok'){ cls='bg-success'; txt='OK'; }
            if(st==='por_caducar'){ cls='bg-warning text-dark'; txt='Por caducar'; }
            if(st==='caducado'){ cls='bg-danger'; txt='Caducado'; }
            return `<span class="badge badge-pill ${cls}">${txt}</span>`;
        }

        function showErrorModal(msg, title='Aviso', opts={}){
            $('#errorModalLabel').text(title);
            $('#errorModalMsg').html(msg);
            const $btn = $('#btnUsarExistente');
            if (opts.usarExistenteId){
                $btn.show().off('click').on('click', async ()=>{
                    $('#agExistente').prop('checked', true).trigger('change');
                    $('#agArticuloId').val(opts.usarExistenteId).trigger('change');
                    bootstrap.Modal.getInstance(document.getElementById('errorModal')).hide();
                });
            } else { $btn.hide().off('click'); }
            new bootstrap.Modal(document.getElementById('errorModal')).show();
        }

        async function existeStock(params){
            const qs = new URLSearchParams(params).toString();
            const r = await fetch("<?= site_url('api/inventario/existe') ?>?"+qs);
            if(!r.ok) return null;
            const js = await r.json();
            return js && js.exists ? (js.data || null) : null;
        }

        async function buscarArticulos(q){
            if(!q) return [];
            const $spin = $('#agArtSpin'); $spin.show();
            try{
                const r = await fetch("<?= site_url('api/articulos/buscar') ?>?q="+encodeURIComponent(q));
                if(!r.ok) return [];
                const js = await r.json();
                return Array.isArray(js.data) ? js.data : [];
            } finally { $spin.hide(); }
        }

        async function cargarArticuloDetalle({id=null, sku=null}){
            const qs = new URLSearchParams();
            if(id) qs.set('id', id);
            if(sku) qs.set('sku', sku);

            const r = await fetch("<?= site_url('api/articulos/detalle') ?>?"+qs.toString());
            if(!r.ok) return null;
            const js = await r.json();
            if(!js || !js.data) return null;
            const a = js.data;

            $('#agArticuloId').val(a.id);
            $('#agArticulo').val(a.nombre || (a.sku?`(SKU ${a.sku})`:''));
            $('#agNombre').val(a.nombre||'');
            $('#agUM').val(a.unidadMedida||'');
            if(a.stockMin!==undefined) $('#agMin').val(a.stockMin ?? '');
            if(a.stockMax!==undefined) $('#agMax').val(a.stockMax ?? '');

            const rr = await fetch("<?= site_url('api/inventario/resumen-articulo') ?>/"+a.id);
            let html = '';
            if(rr.ok){
                const j2 = await rr.json();
                const rows = Array.isArray(j2.data)? j2.data : [];
                html = rows.length
                    ? rows.map(x=>`<tr><td>${x.almacenCodigo||''}</td><td>${x.ubicacionCodigo||''}</td><td>${x.loteCodigo||'-'}</td><td>${(x.fechaFab||'').slice(0,10)}</td><td>${(x.fechaCad||'').slice(0,10)}</td><td>${parseFloat(x.cantidad||0).toFixed(2)}</td></tr>`).join('')
                    : '<tr><td colspan="6" class="text-muted">Sin existencias registradas.</td></tr>';
            }
            $('#agExistenciasBody').html(html);
            $('#agExistenciasWrap').removeClass('d-none');

            return a;
        }

        // === DataTable con paginación 10 por página y números de página ===
        const dt = $tabla.DataTable({
            language: langES,
            ajax:{
                url: "<?= site_url('api/inventario') ?>",
                dataSrc: 'data',
                data: function(){ return { almacenId: $sel.val() }; }
            },
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend:'copyHtml5' },
                { extend:'csvHtml5'  },
                { extend:'excelHtml5', title:'inventario_<?= date('Ymd') ?>' },
                { extend:'pdfHtml5',  orientation:'landscape', pageSize:'A4', title:'Inventario' },
                { extend:'print'     }
            ],
            columns:[
                {data:'almacenNombre'},
                {data:'ubicacionCodigo'},
                {data:'sku'},
                {data:'articuloNombre'},
                {data:'unidadMedida'},
                {data:'cantidad', render:(v)=> (v==null?'' : parseFloat(v).toFixed(2))},
                {data:'stockMin',  render:(v)=> (v==null?'' : parseFloat(v).toFixed(2))},
                {data:'stockMax',  render:(v)=> (v==null?'' : parseFloat(v).toFixed(2))},
                {data:null, render:(row)=> stockBadge(row)},
                {data:null, orderable:false, searchable:false, render:(row)=>{
                        const payload = encodeURIComponent(JSON.stringify(row));
                        return `
                    <div class="btn-group" role="group">
                      <button class="btn btn-sm btn-outline-info btn-icon btn-ver" title="Ver" data-row="${payload}">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-primary btn-icon btn-edit" title="Editar" data-row="${payload}">
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger btn-icon btn-del" title="Eliminar" data-row="${payload}">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>`;
                    }}
            ],
            order:[[0,'asc'],[1,'asc']],
            pageLength: 10,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Todos']],
            pagingType: 'numbers' // muestra 1,2,3,… en la paginación
        });

        $sel.on('change', ()=> dt.ajax.reload());

        /* ===== VER ===== */
        $(document).on('click','.btn-ver', function(){
            const row = JSON.parse(decodeURIComponent(this.dataset.row));
            const fmt = v => v ? String(v).slice(0,10) : '-';

            $('#detalleModalLabel').text(`Detalle: ${row.articuloNombre}`);
            $('#dArticulo').text(row.articuloNombre || '');
            $('#dSku').text(row.sku || '');
            $('#dUM').text(row.unidadMedida || '');
            $('#dAlmacen').text(`${row.almacenCodigo || ''} - ${row.almacenNombre || ''}`);
            $('#dUbicacion').text(row.ubicacionCodigo || '');
            $('#dLote').text(row.loteCodigo || '-');
            $('#dFab').text(fmt(row.fechaFabricacion));
            $('#dCad').text(fmt(row.fechaCaducidad));
            $('#dDias').text(row.diasCaduca ?? '-');
            $('#dNotas').text(row.loteNotas || '-');
            $('#dCant').text(row.cantidad ?? '');
            $('#dMin').text(row.stockMin ?? '-');
            $('#dMax').text(row.stockMax ?? '-');
            $('#dImg').attr('src', row.urlImagen || "<?= base_url('img/placeholder.png') ?>");

            const combo = `
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="small text-muted">Stock:</span> ${stockBadge(row)}
                <span class="small text-muted ms-2">Caducidad:</span> ${caducidadBadge(row)}
              </div>`;
            $('#dEstado').html(combo);

            $('#tbodyLotes').html('<tr><td colspan="4" class="text-muted">Cargando...</td></tr>');
            const qs = new URLSearchParams({ articuloId: row.articuloId, almacenId: row.almacenId || '', ubicacionId: row.ubicacionId || '' }).toString();

            fetch("<?= site_url('api/inventario/lotes') ?>?"+qs)
                .then(r=>r.json())
                .then(({data})=>{
                    const fmt2 = v => v ? String(v).slice(0,10) : '-';
                    let html = '';
                    if (Array.isArray(data) && data.length){
                        html = data.map(l => `
                          <tr>
                            <td>${l.loteCodigo ?? '-'}</td>
                            <td>${fmt2(l.fechaFabricacion)}</td>
                            <td>${fmt2(l.fechaCaducidad)}</td>
                            <td>${l.diasCaduca ?? '-'}</td>
                          </tr>`).join('');
                    } else {
                        html = `
                          <tr>
                            <td>${row.loteCodigo ?? '-'}</td>
                            <td>${fmt(row.fechaFabricacion)}</td>
                            <td>${fmt(row.fechaCaducidad)}</td>
                            <td>${row.diasCaduca ?? '-'}</td>
                          </tr>`;
                    }
                    $('#tbodyLotes').html(html);
                }).catch(()=>{});

            $('#tbodyMovs').empty(); $('#historialWrap').addClass('d-none');
            fetch("<?= site_url('api/inventario/movimientos') ?>/"+row.articuloId+"?loteId="+(row.loteId||'')+"&ubicacionId="+(row.ubicacionId||''))
                .then(r=>r.json()).then(({data})=>{
                if(Array.isArray(data) && data.length){
                    const html = data.map(m=>(
                        `<tr>
                          <td>${(m.fecha||'').replace('T',' ').slice(0,19)}</td>
                          <td>${m.tipo||''}</td>
                          <td>${m.cantidad||''}</td>
                          <td>${(m.refTipo||'')}-${(m.refId||'')}</td>
                          <td>${m.notas||''}</td>
                        </tr>`
                    )).join('');
                    $('#tbodyMovs').html(html);
                    $('#historialWrap').removeClass('d-none');
                }
            }).catch(()=>{});

            new bootstrap.Modal(document.getElementById('detalleModal')).show();
        });

        /* ===== EDITAR ===== */
        $(document).on('click','.btn-edit', async function(){
            const row = JSON.parse(decodeURIComponent(this.dataset.row));
            const fmt = v => v ? String(v).slice(0,10) : '';

            $('#eStockId').val(row.stockId || '');
            $('#eArticuloId').val(row.articuloId || '');
            $('#eLoteId').val(row.loteId || '');
            $('#eAlmacen').val(`${row.almacenCodigo || ''} - ${row.almacenNombre || ''}`);
            $('#eSKU').val(row.sku || '');
            $('#eArticuloNombre').val(row.articuloNombre || '');
            $('#eUMEdit').val(row.unidadMedida || '');
            $('#eMin').val(row.stockMin ?? '');
            $('#eMax').val(row.stockMax ?? '');
            $('#eCantidad').val(row.cantidad ?? 0);
            $('#eLoteCodigo').val(row.loteCodigo || '');
            $('#eFechaFab').val(fmt(row.fechaFabricacion));
            $('#eFechaCad').val(fmt(row.fechaCaducidad));
            $('#eNotas').val(row.loteNotas || '');

            const sel = $('#eUbicacionId').empty();
            try{
                const res = await fetch("<?= site_url('api/ubicaciones') ?>?almacenId="+(row.almacenId||''));
                const js  = await res.json();
                (js.data||[]).forEach(u=>{
                    const opt = document.createElement('option');
                    opt.value = u.id; opt.textContent = u.codigo + (u.id==row.ubicacionId ? ' (actual)' : '');
                    if (u.id == row.ubicacionId) opt.selected = true;
                    sel.append(opt);
                });
            }catch(_){ }

            new bootstrap.Modal(document.getElementById('editarModal')).show();
        });

        $('#btnGuardarEdit').on('click', async function(){
            const payload = {
                stockId:        $('#eStockId').val(),
                articuloId:     $('#eArticuloId').val(),
                loteId:         $('#eLoteId').val(),
                ubicacionId:    $('#eUbicacionId').val(),
                cantidad:       $('#eCantidad').val(),
                articuloNombre: $('#eArticuloNombre').val(),
                unidadMedida:   $('#eUMEdit').val(),
                stockMin:       $('#eMin').val(),
                stockMax:       $('#eMax').val(),
                loteCodigo:     $('#eLoteCodigo').val(),
                fechaFabricacion: $('#eFechaFab').val(),
                fechaCaducidad:   $('#eFechaCad').val(),
                loteNotas:      $('#eNotas').val()
            };

            try{
                const res = await fetch("<?= site_url('api/inventario/editar') ?>", {
                    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
                });
                const js = await res.json();
                if(js.ok){
                    bootstrap.Modal.getInstance(document.getElementById('editarModal')).hide();
                    dt.ajax.reload(null,false);
                }else{
                    alert(js.message || 'No se pudo guardar');
                }
            }catch(e){ alert('Error al guardar'); }
        });

        /* ===== ELIMINAR ===== */
        $(document).on('click','.btn-del', async function(){
            const row = JSON.parse(decodeURIComponent(this.dataset.row));
            if(!confirm('¿Eliminar este registro?')) return;
            try{
                const res = await fetch("<?= site_url('api/inventario/eliminar') ?>/"+row.stockId, { method:'DELETE' });
                const js  = await res.json();
                if(js.ok) dt.ajax.reload(null,false);
                else alert(js.message || 'No se pudo eliminar');
            }catch(e){ alert('Error al eliminar'); }
        });

        /* ===== AGREGAR ===== */
        const $m   = $('#agregarModal');
        const $alm = $('#agAlmacen');
        const $ubi = $('#agUbicacion');

        function toggleFechas(){
            const on = $('#agPer').is(':checked');
            $('#agFab').prop('disabled', !on);
            $('#agCad').prop('disabled', !on);
        }
        $('#agPer').on('change', toggleFechas); toggleFechas();

        async function loadUbicaciones(almacenId){
            $ubi.empty().append(new Option('Cargando...', ''));
            try{
                const r = await fetch("<?= site_url('api/ubicaciones') ?>?almacenId="+(almacenId||''));
                const js = await r.json();
                $ubi.empty().append(new Option('Seleccione...', ''));
                (js.data||[]).forEach(u => $ubi.append(new Option(u.codigo, u.id)));
            }catch(_){ $ubi.empty().append(new Option('Error', '')); }
        }
        $alm.on('change', ()=> loadUbicaciones($alm.val()));

        function setExistenteMode(on){
            $('#agUM,#agMin,#agMax,#agNombre').prop('disabled', on);
            $('#agArticulo').prop('disabled', !on);
            if(!on){
                $('#agArticulo').val(''); $('#dlArticulo').empty(); $('#agArtSpin').hide();
                $('#agArticuloId').val('');
                $('#agExistenciasBody').empty(); $('#agExistenciasWrap').addClass('d-none');
            }
        }

        $m.on('show.bs.modal', ()=>{
            if ($sel.val()) $alm.val($sel.val());
            $alm.trigger('change');
            $('#agOperacion').val('sumar');
            $('#agExistente').prop('checked', false);
            setExistenteMode(false);
        });

        $('#agExistente').on('change', function(){ setExistenteMode(this.checked); });

        $('#agArticulo').on('input', async function(){
            if(!$('#agExistente').is(':checked')) return;
            const q = this.value.trim();
            if(q.length < 3 && !/^\d+$/.test(q)) { $('#dlArticulo').empty(); return; }

            let lista = await buscarArticulos(q);

            if((!lista || !lista.length) && $.fn.dataTable.isDataTable('#tablaInventario')){
                const rows = $('#tablaInventario').DataTable().rows().data().toArray();
                const val = q.toLowerCase();
                const uniq = new Map();
                rows.forEach(r=>{
                    const nombre = (r.articuloNombre||'').toLowerCase();
                    const sku = (r.sku||'').toLowerCase();
                    if(nombre.includes(val) || sku.includes(val)){
                        const key = r.articuloId || r.sku || r.articuloNombre;
                        if(!uniq.has(key)){
                            uniq.set(key, { id: r.articuloId || null, nombre: r.articuloNombre || '', sku: r.sku || '' });
                        }
                    }
                });
                lista = Array.from(uniq.values()).slice(0,15);
            }

            const dl = $('#dlArticulo').empty();
            (lista||[]).forEach(a=>{
                const label = (a.nombre||'') + (a.sku ? ` (SKU: ${a.sku})` : '');
                dl.append(`<option value="${label}" data-id="${a.id||''}"></option>`);
            });
        });

        $('#agArticulo').on('change blur', async function(){
            if(!$('#agExistente').is(':checked')) return;
            const v = this.value.trim();
            let id = null;
            if(/^\d+$/.test(v)) id = parseInt(v,10);
            if(!id){
                const opt = Array.from(document.querySelectorAll('#dlArticulo option')).find(o => o.value === v);
                if(opt) id = parseInt(opt.getAttribute('data-id'),10);
            }
            if(id) await cargarArticuloDetalle({id});
        });

        $('#btnGuardarAgregar').on('click', async ()=>{
            const existente = $('#agExistente').is(':checked');

            const payload = {
                articuloId:  parseInt($('#agArticuloId').val(),10) || null,
                articuloTexto: ($('#agNombre').val()||'').trim(),
                unidadMedida:  ($('#agUM').val()||'').trim(),
                ubicacionId:   parseInt($('#agUbicacion').val(),10) || 0,
                cantidad:      ($('#agCantidad').val()===''? null : parseFloat($('#agCantidad').val())),
                stockMin:      ($('#agMin').val()===''? null : parseFloat($('#agMin').val())),
                stockMax:      ($('#agMax').val()===''? null : parseFloat($('#agMax').val())),
                loteCodigo:    ($('#agLote').val()||'').trim(),
                loteNotas:     ($('#agNotas').val()||'').trim(),
                fechaFabricacion: $('#agPer').is(':checked') ? ($('#agFab').val()||null) : null,
                fechaCaducidad:   $('#agPer').is(':checked') ? ($('#agCad').val()||null) : null,
                operacion:        $('#agOperacion').val(),
                autoCrear:        !existente
            };

            if (!payload.ubicacionId){ alert('Selecciona una ubicación.'); return; }
            if (payload.cantidad===null || isNaN(payload.cantidad)){ alert('Captura la cantidad.'); return; }
            if (existente && !payload.articuloId){ alert('Activas “Artículo en existencia”: selecciona uno de la lista.'); return; }
            if (!existente && !payload.articuloTexto){ alert('Escribe el nombre del nuevo artículo.'); return; }
            if (payload.cantidad <= 0 && payload.operacion!=='reemplazar'){
                alert('La cantidad debe ser > 0. Para ajustes exactos usa "Reemplazar".');
                return;
            }

            const ex = await existeStock({
                ubicacionId: payload.ubicacionId,
                articuloId: payload.articuloId || '',
                loteCodigo: payload.loteCodigo || ''
            });
            if(!ex && payload.operacion==='restar'){
                alert('No puedes restar porque el artículo aún no existe en esa ubicación/lote.');
                return;
            }
            if(ex && payload.operacion==='reemplazar'){
                const ok = confirm(`Este artículo ya existe con ${ex.cantidad} ${ex.unidadMedida||''}. ¿Deseas REEMPLAZAR por ${payload.cantidad}?`);
                if(!ok) return;
            }

            try{
                const res = await fetch("<?= site_url('api/inventario/agregar') ?>", {
                    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
                });

                let js = null; try { js = await res.json(); } catch(_){}

                if (res.status === 409 && js && js.code === 'duplicate') {
                    showErrorModal(
                        `El artículo <strong>${js.nombre || ''}</strong> ya existe (SKU: <code>${js.sku || 's/n'}</code>).<br>
                         Para modificar existencias, activa <em>“Artículo en existencia”</em> y selecciona el artículo.`,
                        'Artículo ya en existencia',
                        { usarExistenteId: js.articuloId || null }
                    );
                    return;
                }

                if (!res.ok || !js || !js.ok){ alert((js && js.message) || 'No se pudo guardar'); return; }

                bootstrap.Modal.getInstance(document.getElementById('agregarModal')).hide();
                dt.ajax.reload(null, false);
            }catch(e){
                alert('Error de red al guardar');
            }
        });

    })();
</script>
<?= $this->endSection() ?>
