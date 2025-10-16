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

    /* === Estilo de botones como la captura (relleno gris, separados, bordes suaves) === */
    .dt-buttons { gap: .5rem; flex-wrap: wrap; }
    .dt-buttons.btn-group > .btn {
        border-radius: .65rem !important; /* NO pill */
        margin-left: 0 !important;        /* anula el -1px de .btn-group */
        padding: .40rem .85rem !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <h1 class="me-3">Inventario de Almacenes</h1>
        <span class="badge bg-primary">Logística / Almacén</span>
    </div>
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#agregarModal">
        <i class="bi bi-plus-circle me-1"></i> Agregar
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Existencias por ubicación</strong>
    </div>
    <div class="card-body">
        <table id="tablaInventario" class="table table-striped table-bordered text-center align-middle w-100">
            <thead>
            <tr class="thead-toolbar">
                <th colspan="10" class="text-start">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label for="selectAlmacen" class="col-form-label fw-semibold">Almacén:</label>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3">
                            <select id="selectAlmacen" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach (($almacenes ?? []) as $a): ?>
                                    <option value="<?= (int)$a['id'] ?>"><?= esc($a['codigo'].' - '.$a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto small text-muted">
                            Exporta con Copy / CSV / Excel / PDF / Print
                        </div>
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

<!-- Modal Detalle (Ver) -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
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

                <!-- Tabla de lotes -->
                <div class="mt-3">
                    <h6 class="mb-2">Lotes del artículo</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Lote</th><th>F. Fab.</th><th>F. Cad.</th><th>Días</th></tr></thead>
                            <tbody id="tbodyLotes"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial de movimientos -->
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
        </div>
    </div>
</div>

<!-- Modal Agregar (demo UI) -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="agregarModalLabel" class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar existencias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregar" class="row g-3">
                    <div class="col-md-6"><label class="form-label">Artículo (ID o SKU)</label><input type="text" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Ubicación (ID)</label><input type="number" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Lote (opcional)</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Cantidad</label><input type="number" class="form-control" min="0" step="0.01" required></div>
                    <div class="col-md-3"><label class="form-label">Unidad</label><input type="text" class="form-control" placeholder="pzas"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Guardar (demo)</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
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
        // Español
        const langES = {
            sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_", sZeroRecords:"No se encontraron resultados",
            sEmptyTable:"Sin datos", sInfo:"Mostrando _START_–_END_ de _TOTAL_", sInfoEmpty:"Mostrando 0–0 de 0",
            sInfoFiltered:"(filtrado de _MAX_)", sSearch:"Buscar:", oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copy", csv:"CSV", excel:"Excel", pdf:"PDF", print:"Print" }
        };

        const $sel = $('#selectAlmacen');
        const $tabla = $('#tablaInventario');

        // Defaults de Buttons para aspecto "relleno gris" y separados.
        $.extend(true, $.fn.dataTable.Buttons.defaults, {
            dom: {
                container: { className: 'dt-buttons btn-group d-inline-flex flex-wrap gap-2' },
                button:    { className: 'btn btn-secondary' } // Relleno gris
            }
        });

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
                {data:'cantidad'},
                {data:'stockMin'},
                {data:'stockMax'},
                {data:null, render:(row)=>{
                        const st = row.estadoCaducidad;
                        let cls='bg-secondary', txt='Sin fecha';
                        if(st==='ok'){ cls='bg-success'; txt='OK'; }
                        if(st==='por_caducar'){ cls='bg-warning text-dark'; txt='Por caducar'; }
                        if(st==='caducado'){ cls='bg-danger'; txt='Caducado'; }
                        return `<span class="badge badge-pill ${cls}">${txt}</span>`;
                    }
                },
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
                    }
                }
            ],
            order:[[0,'asc'],[1,'asc']]
        });

        $sel.on('change', ()=> dt.ajax.reload());

        // ----- VER -----
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

            const st = row.estadoCaducidad;
            let cls='bg-secondary', txt='Sin fecha';
            if(st==='ok'){ cls='bg-success'; txt='OK'; }
            if(st==='por_caducar'){ cls='bg-warning text-dark'; txt='Por caducar'; }
            if(st==='caducado'){ cls='bg-danger'; txt='Caducado'; }
            $('#dEstado').html(`<span class="badge badge-pill ${cls}">${txt}</span>`);

            // Lotes (API con fallback al lote de la fila)
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
                })
                .catch(()=>{
                    const html = `
          <tr>
            <td>${row.loteCodigo ?? '-'}</td>
            <td>${fmt(row.fechaFabricacion)}</td>
            <td>${fmt(row.fechaCaducidad)}</td>
            <td>${row.diasCaduca ?? '-'}</td>
          </tr>`;
                    $('#tbodyLotes').html(html);
                });

            // Historial (opcional)
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

        // ----- EDITAR -----
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

            // Ubicaciones del almacén
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
            }catch(_){}

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

        // Eliminar (UI demo)
        $(document).on('click','.btn-del', function(){
            if(confirm('¿Eliminar este registro?')){
                dt.row($(this).closest('tr')).remove().draw(false);
            }
        });
    })();
</script>
<?= $this->endSection() ?>
