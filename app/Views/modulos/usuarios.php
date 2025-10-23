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
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="me-3">Gestión de Usuarios</h1>
        <div>
            <a href="<?= base_url('modulo11/agregar') ?>" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Agregar Usuario
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Lista de Usuarios del Sistema</strong>
        </div>
        <div class="card-body">
            <table id="tablaUsuarios" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>NO. EMPLEADO</th>
                    <th>NOMBRE</th>
                    <th>EMAIL</th>
                    <th>PUESTO</th>
                    <th>ESTATUS</th>
                    <th>FECHA REGISTRO</th>
                    <th>ÚLTIMO ACCESO</th>
                    <th>ACCIONES</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    <td><?= $usuario['noEmpleado'] ?></td>
                    <td><?= $usuario['nombre'] ?></td>
                    <td><?= $usuario['email'] ?></td>
                    <td>
                        <span class="rol-badge rol-<?= strtolower(str_replace(' ', '', $usuario['puesto'])) ?>">
                            <?= $usuario['puesto'] ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $estatusText = '';
                        $estatusClass = '';
                        switch($usuario['activo']) {
                            case 1:
                                $estatusText = 'Activo';
                                $estatusClass = 'activo';
                                break;
                            case 0:
                                $estatusText = 'Inactivo';
                                $estatusClass = 'inactivo';
                                break;
                            case 2:
                                $estatusText = 'Baja de la empresa';
                                $estatusClass = 'bajadelaempresa';
                                break;
                            case 3:
                                $estatusText = 'En espera';
                                $estatusClass = 'enespera';
                                break;
                            default:
                                $estatusText = 'Desconocido';
                                $estatusClass = 'inactivo';
                        }
                        ?>
                        <span class="estatus estatus-<?= $estatusClass ?>">
                            <?= $estatusText ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($usuario['fechaAlta'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($usuario['ultimoAcceso'])) ?></td>
                    <td>
                        <button type="button" 
                            class="btn btn-sm btn-outline-primary btn-accion btn-editar" 
                            title="Editar Usuario"
                            data-id="<?= $usuario['id'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="confirmarEliminarUsuario(<?= $usuario['id'] ?>, '<?= esc($usuario['nombre']) ?>')" 
                                class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar Usuario">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button onclick="verDetalles(<?= $usuario['id'] ?>)" 
                                class="btn btn-sm btn-outline-info btn-accion" title="Ver Detalles">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div id="modalBodyContainer">
                    <div class="modal-body">
                        <form id="formEditarUsuario" method="POST">
                            <input type="hidden" name="id" id="editar_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editar_nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="editar_email" name="email" required autocomplete="username">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editar_rol" class="form-label">Rol</label>
                                <select class="form-select" id="editar_rol" name="rol" required>
                                    <!-- Los roles se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_maquiladora" class="form-label">Maquiladora</label>
                                <select class="form-select" id="editar_maquiladora" name="idmaquiladora">
                                    <!-- Las maquiladoras se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editar_activo" class="form-label">Estado de la cuenta</label>
                                <select class="form-select" id="editar_activo" name="activo" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="editar_password" name="password" placeholder="Dejar en blanco para no cambiar" autocomplete="new-password">
                                <div class="form-text">Mínimo 8 caracteres</div>
                            </div>
                        </div>
                    
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
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
    <!-- DataTables Buttons dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Inicializar DataTable
            const fecha = new Date().toISOString().slice(0,10);
            const fileName = 'usuarios_' + fecha;
            var table = $('#tablaUsuarios').DataTable({
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
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad"
                    }
                },
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true,
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
            // Exponer la instancia para usarla fuera del ready
            window.usuariosTable = table;
        });

        // Función para abrir el modal de edición con los datos del usuario
        function abrirModalEditar(id) {
            try {
                console.log('[DEBUG] Iniciando abrirModalEditar con ID:', id);
                
                // Mostrar indicador de carga
                const $modal = $('#editarUsuarioModal');
                const $modalBody = $modal.find('#modalBodyContainer');
                
                if ($modal.length === 0 || $modalBody.length === 0) {
                    console.error('[ERROR] No se encontró el modal o el contenedor del cuerpo');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo abrir el modal de edición. Revisa la consola para más detalles.'
                    });
                    return;
                }
                
                // Guardar el contenido original del modal
                const originalContent = $modalBody.html();
                
                // Mostrar indicador de carga
                $modalBody.html(`
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando datos del usuario...</p>
                            <div class="mt-2 text-muted small">ID: ${id}</div>
                        </div>
                    </div>
                `);
                
                // Mostrar el modal
                const modal = new bootstrap.Modal($modal[0]);
                modal.show();
                
                const url = '<?= base_url('modulo11/obtener_usuario/') ?>' + id;
                console.log('[DEBUG] Solicitando datos a:', url);
                
                // Obtener los datos del usuario desde el servidor
                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('[DEBUG] Enviando solicitud AJAX...');
                    },
                    success: function(response) {
                        console.log('[DEBUG] Respuesta del servidor:', response);
                        
                        try {
                            if (!response) {
                                throw new Error('No se recibió respuesta del servidor');
                            }
                            
                            if (response.success && response.data) {
                                const usuario = response.data;
                                console.log('[DEBUG] Datos del usuario recibidos:', usuario);
                                
                                // Restaurar el contenido original del modal
                                $modalBody.html(originalContent);
                                
                                // Llenar campos básicos
                                if (usuario.id) $('#editar_id').val(usuario.id);
                                if (usuario.username) $('#editar_nombre').val(usuario.username);
                                if (usuario.email) $('#editar_email').val(usuario.email);
                                
                                // Cargar roles
                                const $rolSelect = $('#editar_rol');
                                $rolSelect.empty();
                                
                                if (usuario.roles && Array.isArray(usuario.roles) && usuario.roles.length > 0) {
                                    console.log('[DEBUG] Cargando roles:', usuario.roles);
                                    usuario.roles.forEach(rol => {
                                        // No forzar selección por defecto aquí
                                        $rolSelect.append(new Option(rol.name, rol.id, false, false));
                                    });
                                } else {
                                    console.warn('[WARN] No se encontraron roles');
                                    // Dejar el select vacío y deshabilitado si no hay catálogo
                                    $rolSelect.prop('disabled', true);
                                }
                                
                                // Cargar maquiladoras
                                const $maquiladoraSelect = $('#editar_maquiladora');
                                $maquiladoraSelect.empty();
                                
                                // Agregar opción por defecto
                                $maquiladoraSelect.append(new Option('Seleccionar...', '', true, !usuario.maquiladoraIdFK));
                                
                                if (usuario.maquiladoras && Array.isArray(usuario.maquiladoras) && usuario.maquiladoras.length > 0) {
                                    console.log('[DEBUG] Cargando maquiladoras:', usuario.maquiladoras);
                                    usuario.maquiladoras.forEach(maq => {
                                        const nombreMaquiladora = maq.nombre || `Maquiladora ${maq.id}`;
                                        const selected = (maq.id == usuario.maquiladoraIdFK);
                                        $maquiladoraSelect.append(new Option(nombreMaquiladora, maq.id, false, selected));
                                    });
                                } else {
                                    console.warn('[WARN] No se encontraron maquiladoras, usando valor por defecto');
                                    $maquiladoraSelect.append(new Option('Maquiladora Principal', 1, true, true));
                                }
                                
                                // Establecer valores seleccionados
                                if (usuario.rol_id) { 
                                    $rolSelect.val(usuario.rol_id);
                                } else {
                                    // Si no hay rol asignado, seleccionar el primero disponible si existe
                                    if ($rolSelect[0].options.length > 0) {
                                        $rolSelect.prop('selectedIndex', 0);
                                    }
                                }
                                if (usuario.maquiladoraIdFK) $maquiladoraSelect.val(usuario.maquiladoraIdFK);
                                
                                // Estado activo/inactivo
                                if (usuario.activo !== undefined) {
                                    $('#editar_activo').val(usuario.activo ? '1' : '0');
                                }
                                
                                // Limpiar campo de contraseña
                                $('#editar_password').val('');
                                
                            } else {
                                throw new Error(response.message || 'Error desconocido al cargar los datos del usuario');
                            }
                        } catch (error) {
                            console.error('[ERROR] Error al procesar la respuesta:', error);
                            $modalBody.html(`
                                <div class="modal-body">
                                    <div class="alert alert-danger">
                                        Error: ${error.message || 'Error al cargar los datos del usuario'}
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-secondary" onclick="abrirModalEditar(${id})">
                                                Reintentar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('[ERROR] Error en la solicitud AJAX:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        
                        let errorMessage = 'Error al conectar con el servidor';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            console.error('[ERROR] No se pudo analizar la respuesta de error:', e);
                        }
                        
                        $modalBody.html(`
                            <div class="modal-body">
                                <div class="alert alert-danger">
                                    ${errorMessage}
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-secondary me-2" onclick="abrirModalEditar(${id})">
                                            <i class="bi bi-arrow-clockwise"></i> Reintentar
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="$('#editarUsuarioModal').modal('hide')">
                                            <i class="bi bi-x-lg"></i> Cerrar
                                        </button>
                                    </div>
                                    <div class="mt-3 small text-muted">
                                        ID de error: ${Date.now()}
                                    </div>
                                </div>
                            </div>
                        `);
                    }
                });
            } catch (error) {
                console.error('[ERROR CRÍTICO] Error en abrirModalEditar:', error);
                Swal.fire({ icon: 'error', title: 'Error crítico', text: 'No se pudo abrir el modal de edición.' });
            }
        }

        // Manejar clic en botones de editar
        $(document).on('click', '.btn-editar', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            abrirModalEditar(userId);
        });

        // Manejar envío del formulario de edición
        $(document).on('submit', '#formEditarUsuario', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnText = $submitBtn.html();
            
            // Validar ID de usuario
            const idVal = parseInt($('#editar_id').val(), 10) || 0;
            if (idVal <= 0) {
                Swal.fire({ icon: 'warning', title: 'ID inválido', text: 'Cierre y vuelva a abrir el modal.' });
                return false;
            }

            // Validar contraseña si se proporcionó
            const password = $('#editar_password').val();
            if (password && password.length < 8) {
                Swal.fire({ icon: 'warning', title: 'Contraseña corta', text: 'Debe tener al menos 8 caracteres.' });
                return false;
            }
            
            // Validar que se haya seleccionado un rol
            const rol = $('#editar_rol').val();
            if (!rol) {
                Swal.fire({ icon: 'warning', title: 'Rol requerido', text: 'Seleccione un rol para el usuario.' });
                return false;
            }
            
            // Deshabilitar el botón para evitar múltiples envíos
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
            
            // Preparar los datos del formulario
            const formData = new FormData();
            formData.append('id', String(idVal));
            formData.append('nombre', $('#editar_nombre').val().trim());
            formData.append('email', $('#editar_email').val().trim());
            formData.append('rol', rol);
            formData.append('idmaquiladora', $('#editar_maquiladora').val() || '');
            formData.append('activo', $('#editar_activo').val());
            if (password) {
                formData.append('password', password);
            }
            
            // Enviar los datos al servidor
            $.ajax({
                url: '<?= base_url('modulo11/actualizar_usuario') ?>',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        try { $('#editarUsuarioModal').modal('hide'); } catch(e){}
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: response.message || 'Usuario actualizado correctamente',
                            confirmButtonText: 'Aceptar'
                        }).then(() => { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No fue posible actualizar.' });
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al actualizar usuario:', error);
                    let errorMessage = 'Error al actualizar el usuario. Por favor, intente nuevamente.';
                    
                    // Intentar obtener el mensaje de error del servidor si está disponible
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error al procesar la respuesta de error:', e);
                    }
                    
                    Swal.fire({ icon: 'error', title: 'Error', text: errorMessage });
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Confirmación con SweetAlert2 para eliminar
        function confirmarEliminarUsuario(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminará al usuario "${nombre}" y no podrás revertirlo`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarUsuario(id, null, nombre);
                }
            });
        }

        function eliminarUsuario(id, modalInstance, nombre) {
            $.ajax({
                url: '<?= base_url('modulo11/eliminar_usuario') ?>',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(resp){
                    if (resp && resp.success) {
                        if (modalInstance) modalInstance.hide();
                        // Remover fila de la tabla (usando DataTable API)
                        const row = $("#tablaUsuarios tbody tr").filter(function(){
                            return $(this).find('td:first').text().trim() == String(id);
                        });
                        if (row.length && window.usuariosTable) {
                            window.usuariosTable.row(row).remove().draw(false);
                        }
                        Swal.fire({ icon: 'success', title: '¡Eliminado!', text: resp.message || `Usuario "${nombre}" eliminado.` });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo eliminar.' });
                    }
                },
                error: function(xhr){
                    let msg = 'Error al eliminar';
                    try { const j = JSON.parse(xhr.responseText); if (j.message) msg = j.message; } catch(e){}
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        }

        // Toast helper
        function ensureToastContainer() {
            if (!document.getElementById('toastContainer')) {
                const container = document.createElement('div');
                container.id = 'toastContainer';
                container.className = 'toast-container position-fixed top-50 start-50 translate-middle p-3';
                container.style.zIndex = '1080';
                document.body.appendChild(container);
            }
            return document.getElementById('toastContainer');
        }

        function showToast(message, type) {
            const container = ensureToastContainer();
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-bg-${type} border-0`;
            toastEl.role = 'alert';
            toastEl.ariaLive = 'assertive';
            toastEl.ariaAtomic = 'true';
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
            container.appendChild(toastEl);
            const bsToast = new bootstrap.Toast(toastEl, { delay: 2500 });
            bsToast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        function verDetalles(id) {
            // Pendiente: implementar modal con detalles del usuario
            Swal.fire({ icon: 'info', title: 'Información', text: 'Función de ver detalles pendiente de implementar.' });
        }
    </script>
<?= $this->endSection() ?>