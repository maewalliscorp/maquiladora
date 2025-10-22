<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        /* Separación entre los botones de DataTables */
        .dt-buttons.btn-group .btn{
            margin-left: 0 !important;
            margin-right: .5rem;
            border-radius: .375rem !important;
        }
        .dt-buttons.btn-group .btn:last-child{ margin-right: 0; }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-auto">Clientes</h1>
        <button type="button" class="btn btn-success" id="btnAgregarCliente" data-bs-toggle="modal" data-bs-target="#modalCliente">
            <i class="bi bi-person-plus"></i> Agregar Cliente
        </button>
    </div>
    <div class="modal fade" id="modalClienteDel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">¿Confirmas que deseas eliminar este cliente? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDel">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Listado de Clientes</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="tabla-clientes">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th style="width: 160px;">Fecha Registro</th>
                        <th style="width: 140px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="clientes-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalClienteVer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="v_nombre" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" name="v_email" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="v_telefono" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha registro</label>
                            <input type="text" class="form-control" name="v_fechaRegistro" readonly>
                        </div>
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-md-6">
                            <label class="form-label">Calle</label>
                            <input type="text" class="form-control" name="v_calle" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Num. Ext</label>
                            <input type="text" class="form-control" name="v_numExt" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Num. Int</label>
                            <input type="text" class="form-control" name="v_numInt" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" name="v_ciudad" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" name="v_estado" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">CP</label>
                            <input type="text" class="form-control" name="v_cp" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" name="v_pais" readonly>
                        </div>
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
            const fileName = 'clientes_' + fecha;

            // Inicializar DataTable
            const tabla = $('#tabla-clientes').DataTable({
                language: langES,
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }],
                dom:
                    "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-12'tr>>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [
                    { extend:'copy',  text:'Copiar',  exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'csv',   text:'CSV',   filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'excel', text:'Excel', filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'pdf',   text:'PDF',   filename:fileName, title:fileName,
                        orientation:'portrait', pageSize:'A4',
                        exportOptions:{ columns: ':not(:last-child)' } },
                    { extend:'print', text:'Imprimir', exportOptions:{ columns: ':not(:last-child)' } }
                ]
            });

            // Cargar datos en la tabla
            function cargarClientes() {
                const tbody = document.getElementById('clientes-body');
                const fmt = (v) => v == null ? '' : String(v);
                const toDate = (v) => {
                    if (!v) return '';
                    try { const d = new Date(v); return isNaN(d) ? fmt(v) : d.toISOString().slice(0,10); } catch(e){ return fmt(v); }
                };

                fetch('<?= base_url('modulo1/clientes/json') ?>' + '?_=' + Date.now(), { headers: { 'Accept': 'application/json' }})
                    .then(r => r.json())
                    .then(data => {
                        const items = Array.isArray(data) ? data : (Array.isArray(data.items) ? data.items : []);

                        // Limpiar tabla
                        tabla.clear();

                        // Agregar datos
                        items.forEach(row => {
                            const id = row.id ?? row.ID ?? row.clienteId ?? '';
                            const nombre = row.nombre ?? row.name ?? '';
                            const email = row.email ?? row.correo ?? '';
                            const tel = row.telefono ?? row.tel ?? '';
                            const fecha = row.fechaRegistro ?? row.fecha ?? row.created_at ?? '';

                            tabla.row.add([
                                fmt(nombre),
                                fmt(email),
                                fmt(tel),
                                toDate(fecha),
                                `
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-view" data-id="${fmt(id)}" aria-label="Ver">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="${fmt(id)}" aria-label="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="${fmt(id)}" aria-label="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        `
                            ]);
                        });

                        tabla.draw();
                    })
                    .catch(() => {
                        tabla.clear().draw();
                        console.error('No fue posible cargar los clientes.');
                    });
            }

            // Cargar datos iniciales
            cargarClientes();

            // Eliminar con confirmación
            let delId = null; let delBtn = null;
            $(document).on('click', '.btn-del', function(){
                delId = $(this).data('id');
                if (!delId) return;
                delBtn = $(this);
                delBtn.prop('disabled', true);
                const m = new bootstrap.Modal(document.getElementById('modalClienteDel'));
                m.show();
                const onHidden = () => {
                    if (delBtn) delBtn.prop('disabled', false);
                    delBtn = null;
                    $('#modalClienteDel').off('hidden.bs.modal', onHidden);
                };
                $('#modalClienteDel').on('hidden.bs.modal', onHidden);
            });

            $('#btnConfirmDel').on('click', async function(){
                if (!delId) return;
                $(this).prop('disabled', true);
                try {
                    const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(delId) + '/eliminar', { method: 'POST', headers: { 'Accept': 'application/json' }});
                    if (res.ok) {
                        cargarClientes(); // Recargar datos
                        $('#modalClienteDel').modal('hide');
                        return;
                    }
                    let msg = 'No se pudo eliminar';
                    try { const js = await res.json(); if (js && js.error) msg = js.error; } catch(e) {}
                    alert(msg);
                } catch(err) { alert('Error de red al eliminar'); }
                $(this).prop('disabled', false);
            });

            let lastEditBtn = null;
            let addLocked = false;
            const btnAdd = $('#btnAgregarCliente');

            $(document).on('click', '.btn-edit', async function(){
                const id = $(this).data('id');
                if (!id) return;
                $(this).prop('disabled', true);
                lastEditBtn = $(this);

                const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }});
                const data = await res.json();
                const m = $('#modalCliente');
                m.find('[name="id"]').val(data.id || '');
                m.find('[name="nombre"]').val(data.nombre || '');
                m.find('[name="email"]').val(data.email || '');
                m.find('[name="telefono"]').val(data.telefono || '');
                m.find('[name="fechaRegistro"]').val(toDate(data.fechaRegistro) || '');
                const d = data.direccion || {};
                m.find('[name="calle"]').val(d.calle || '');
                m.find('[name="numExt"]').val(d.numExt || '');
                m.find('[name="numInt"]').val(d.numInt || '');
                m.find('[name="ciudad"]').val(d.ciudad || '');
                m.find('[name="estado"]').val(d.estado || '');
                m.find('[name="cp"]').val(d.cp || '');
                m.find('[name="pais"]').val(d.pais || '');
                m.find('.modal-title').text('Editar cliente');
                m.modal('show');
            });

            $('#formCliente').on('submit', async function(e){
                e.preventDefault();
                const fd = new FormData(this);
                const id = fd.get('id');
                const btnSave = $(this).find('button[type="submit"]');
                if (btnSave) btnSave.prop('disabled', true);
                if (btnAdd) btnAdd.prop('disabled', true);
                try {
                    const url = id ? '<?= site_url('api/clientes') ?>/' + encodeURIComponent(id) + '/editar' : '<?= site_url('api/clientes/crear') ?>';
                    const res = await fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }});
                    if (res.ok) {
                        try { await res.json(); } catch(e) {}
                        $('#modalCliente').modal('hide');
                        alert(id ? 'Cambios guardados' : 'Cliente agregado');
                        cargarClientes(); // Recargar datos
                        return;
                    }
                    let msg = 'No se pudo guardar';
                    try { const js = await res.json(); if (js && js.error) msg = js.error; } catch(e) {}
                    alert(msg);
                } catch(err) { alert('Error de red al guardar'); }
                if (btnSave) btnSave.prop('disabled', false);
                if (lastEditBtn) lastEditBtn.prop('disabled', false);
                if (btnAdd) btnAdd.prop('disabled', false);
            });

            $('#modalCliente').on('hidden.bs.modal', function(){
                if (lastEditBtn) { lastEditBtn.prop('disabled', false); lastEditBtn = null; }
                if (btnAdd) { btnAdd.prop('disabled', false); }
            });

            $('#btnAgregarCliente').on('click', function(){
                if (addLocked) return;
                addLocked = true;
                $(this).prop('disabled', true);
                const m = $('#modalCliente');
                m.find('[name="id"]').val('');
                m.find('[name="nombre"]').val('');
                m.find('[name="email"]').val('');
                m.find('[name="telefono"]').val('');
                m.find('[name="fechaRegistro"]').val('');
                m.find('[name="calle"]').val('');
                m.find('[name="numExt"]').val('');
                m.find('[name="numInt"]').val('');
                m.find('[name="ciudad"]').val('');
                m.find('[name="estado"]').val('');
                m.find('[name="cp"]').val('');
                m.find('[name="pais"]').val('');
                m.find('.modal-title').text('Agregar cliente');
                m.modal('show');
                setTimeout(()=>{ addLocked = false; }, 300);
            });

            // Ver detalle (solo lectura)
            $(document).on('click', '.btn-view', async function(){
                const id = $(this).data('id');
                if (!id) return;
                try {
                    const res = await fetch('<?= site_url('api/clientes') ?>/' + encodeURIComponent(id), { headers: { 'Accept': 'application/json' }});
                    const data = await res.json();
                    const m = $('#modalClienteVer');
                    m.find('[name="v_nombre"]').val(data.nombre || '');
                    m.find('[name="v_email"]').val(data.email || '');
                    m.find('[name="v_telefono"]').val(data.telefono || '');
                    m.find('[name="v_fechaRegistro"]').val(toDate(data.fechaRegistro) || '');
                    const d = data.direccion || {};
                    m.find('[name="v_calle"]').val(d.calle || '');
                    m.find('[name="v_numExt"]').val(d.numExt || '');
                    m.find('[name="v_numInt"]').val(d.numInt || '');
                    m.find('[name="v_ciudad"]').val(d.ciudad || '');
                    m.find('[name="v_estado"]').val(d.estado || '');
                    m.find('[name="v_cp"]').val(d.cp || '');
                    m.find('[name="v_pais"]').val(d.pais || '');
                    m.modal('show');
                } catch (err) {
                    alert('No fue posible cargar el detalle');
                }
            });

            // Función auxiliar para formatear fechas
            function toDate(v) {
                if (!v) return '';
                try {
                    const d = new Date(v);
                    return isNaN(d) ? String(v) : d.toISOString().slice(0,10);
                } catch(e){
                    return String(v);
                }
            }
        });
    </script>

    <div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCliente">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha registro</label>
                                <input type="date" class="form-control" name="fechaRegistro">
                            </div>
                            <div class="col-12"><hr class="my-2"></div>
                            <div class="col-md-6">
                                <label class="form-label">Calle</label>
                                <input type="text" class="form-control" name="calle">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Num. Ext</label>
                                <input type="text" class="form-control" name="numExt">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Num. Int</label>
                                <input type="text" class="form-control" name="numInt">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <input type="text" class="form-control" name="estado">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">CP</label>
                                <input type="text" class="form-control" name="cp">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">País</label>
                                <input type="text" class="form-control" name="pais">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>