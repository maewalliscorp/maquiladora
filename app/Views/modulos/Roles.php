<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="mb-0">Roles</h1>
        <button type="button" class="btn btn-success" id="btnAbrirAgregar">
            <i class="bi bi-plus-lg"></i> Agregar Rol
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Lista de Roles</strong>
        </div>
        <div class="card-body">
            <table id="tablaRoles" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($roles)): foreach ($roles as $r): ?>
                    <tr>
                        <td><?= esc($r['id']) ?></td>
                        <td class="rol-nombre"><?= esc($r['nombre'] ?? '-') ?></td>
                        <td class="rol-descripcion"><?= esc($r['descripcion'] ?? '-') ?></td>
                        <td>
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-rol-editar"
                                    data-id="<?= (int)$r['id'] ?>"
                                    data-nombre="<?= esc($r['nombre'] ?? '', 'attr') ?>"
                                    data-descripcion="<?= esc($r['descripcion'] ?? '', 'attr') ?>"
                                    title="Editar rol">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Agregar Rol -->
    <div class="modal fade" id="modalAgregarRol" tabindex="-1" aria-labelledby="modalAgregarRolLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalAgregarRolLabel">Agregar Rol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAgregarRol">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ar-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="ar-nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="ar-descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="ar-descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnAgregarGuardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Rol -->
    <div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarRolLabel">Editar Rol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarRol">
                    <div class="modal-body">
                        <input type="hidden" id="er-id" name="id">
                        <div class="mb-3">
                            <label for="er-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="er-nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="er-descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="er-descripcion" name="descripcion" rows="3"></textarea>
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

<?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(function(){
            const dt = $('#tablaRoles').DataTable({
                language: {
                    sProcessing: 'Procesando...',
                    sLengthMenu: 'Mostrar _MENU_ registros',
                    sZeroRecords: 'No se encontraron resultados',
                    sEmptyTable: 'Ningún dato disponible en esta tabla',
                    sInfo: 'Mostrando _START_ a _END_ de _TOTAL_',
                    sInfoEmpty: 'Mostrando 0 a 0 de 0',
                    sInfoFiltered: '(filtrado de _MAX_)',
                    sSearch: 'Buscar:',
                    sLoadingRecords: 'Cargando...',
                    oPaginate: { sFirst: 'Primero', sLast: 'Último', sNext: 'Siguiente', sPrevious: 'Anterior' }
                }
            });

            // Abrir modal Agregar
            $('#btnAbrirAgregar').on('click', function(){
                $('#ar-nombre').val('');
                $('#ar-descripcion').val('');
                const m = new bootstrap.Modal(document.getElementById('modalAgregarRol'));
                m.show();
            });

            // Guardar nuevo rol
            $('#formAgregarRol').on('submit', function(e){
                e.preventDefault();
                const payload = {
                    nombre: $('#ar-nombre').val().trim(),
                    descripcion: $('#ar-descripcion').val().trim()
                };
                if (!payload.nombre) { alert('El nombre es obligatorio'); return; }
                // Evitar envíos múltiples
                const $btn = $('#btnAgregarGuardar');
                if ($btn.prop('disabled')) return; // ya en curso
                $btn.prop('disabled', true).text('Guardando...');
                $.ajax({
                    url: '<?= base_url('modulo11/roles/agregar') ?>',
                    method: 'POST',
                    data: payload,
                    dataType: 'json'
                })
                    .done(function(r){
                        if (r && r.success && r.id) {
                            // Añadir fila a la DataTable
                            const accionHtml = '<button type="button" class="btn btn-sm btn-outline-primary btn-rol-editar"'
                                + ' data-id="'+ r.id +'"'
                                + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                + ' data-descripcion="'+ $('<div>').text(payload.descripcion).html() +'"'
                                + ' title="Editar rol">'
                                + ' <i class="bi bi-pencil"></i>'
                                + '</button>';
                            dt.row.add([r.id, payload.nombre, payload.descripcion, accionHtml]).draw(false);
                            bootstrap.Modal.getInstance(document.getElementById('modalAgregarRol'))?.hide();
                        } else {
                            alert(r && r.message ? r.message : 'No se pudo agregar el rol');
                        }
                    })
                    .fail(function(xhr){
                        let msg = 'Error al agregar el rol';
                        try { const j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e) {}
                        alert(msg);
                    })
                    .always(function(){
                        $btn.prop('disabled', false).text('Guardar');
                    });
            });

            // Abrir modal con datos
            $(document).on('click', '.btn-rol-editar', function(){
                const id  = $(this).data('id');
                const nom = $(this).data('nombre') || '';
                const des = $(this).data('descripcion') || '';
                $('#er-id').val(id);
                $('#er-nombre').val(nom);
                $('#er-descripcion').val(des);
                const m = new bootstrap.Modal(document.getElementById('modalEditarRol'));
                m.show();
            });

            // Guardar cambios (AJAX)
            $('#formEditarRol').on('submit', function(e){
                e.preventDefault();
                const payload = {
                    id: $('#er-id').val(),
                    nombre: $('#er-nombre').val().trim(),
                    descripcion: $('#er-descripcion').val().trim()
                };
                if (!payload.nombre) { alert('El nombre es obligatorio'); return; }
                $.ajax({
                    url: '<?= base_url('modulo11/roles/actualizar') ?>',
                    method: 'POST',
                    data: payload,
                    dataType: 'json'
                })
                    .done(function(r){
                        if (r && r.success) {
                            // Refrescar fila en DataTable usando API
                            const id = payload.id;
                            const $row = $('#tablaRoles tbody tr').filter(function(){ return $(this).find('td:first').text().trim() == String(id); });
                            if ($row.length) {
                                const accionHtml = '<button type="button" class="btn btn-sm btn-outline-primary btn-rol-editar"'
                                    + ' data-id="'+ id +'"'
                                    + ' data-nombre="'+ $('<div>').text(payload.nombre).html() +'"'
                                    + ' data-descripcion="'+ $('<div>').text(payload.descripcion).html() +'"'
                                    + ' title="Editar rol">'
                                    + ' <i class="bi bi-pencil"></i>'
                                    + '</button>';
                                dt.row($row).data([id, payload.nombre, payload.descripcion, accionHtml]).draw(false);
                            }
                            bootstrap.Modal.getInstance(document.getElementById('modalEditarRol'))?.hide();
                        } else {
                            alert(r && r.message ? r.message : 'No se pudo actualizar el rol');
                        }
                    })
                    .fail(function(xhr){
                        let msg = 'Error al actualizar el rol';
                        try { const j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e) {}
                        alert(msg);
                    });
            });
        });
    </script>

<?= $this->endSection() ?>