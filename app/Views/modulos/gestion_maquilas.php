<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Maquiladoras</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #2c5364 0%, #0f2027 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            color: white !important;
        }
        
        .container-main {
            padding: 30px 15px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .card-header {
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="bi bi-building me-2"></i>Sistema de Maquiladoras
        </a>
        <button class="btn btn-logout" onclick="cerrarSesion()">
            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
        </button>
    </div>
</nav>

<div class="container-main">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="mb-0"><i class="bi bi-building me-2"></i>Gestión de Maquiladoras</h1>
            <p class="text-muted mb-0">Administra todas las maquiladoras del sistema</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMaquiladora" onclick="limpiarFormulario()">
            <i class="bi bi-plus-circle me-2"></i>Nueva Maquiladora
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong><i class="bi bi-table me-2"></i>Lista de Maquiladoras</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaMaquiladoras" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dueño</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Domicilio</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Logo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Maquiladora -->
<div class="modal fade" id="modalMaquiladora" tabindex="-1" aria-labelledby="modalMaquiladoraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalMaquiladoraLabel">
                    <i class="bi bi-building me-2"></i><span id="tituloModal">Nueva Maquiladora</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formMaquiladora">
                    <input type="hidden" id="idmaquiladora" name="idmaquiladora">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Nombre_Maquila" class="form-label">
                                <i class="bi bi-building me-1"></i>Nombre de la Maquiladora *
                            </label>
                            <input type="text" class="form-control" id="Nombre_Maquila" name="Nombre_Maquila" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="Dueno" class="form-label">
                                <i class="bi bi-person me-1"></i>Dueño *
                            </label>
                            <input type="text" class="form-control" id="Dueno" name="Dueno" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="Telefono" class="form-label">
                                <i class="bi bi-telephone me-1"></i>Teléfono
                            </label>
                            <input type="tel" class="form-control" id="Telefono" name="Telefono">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="Correo" class="form-label">
                                <i class="bi bi-envelope me-1"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control" id="Correo" name="Correo">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="Domicilio" class="form-label">
                            <i class="bi bi-geo-alt me-1"></i>Domicilio
                        </label>
                        <textarea class="form-control" id="Domicilio" name="Domicilio" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">
                                <i class="bi bi-tag me-1"></i>Tipo
                            </label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Seleccionar...</option>
                                <option value="empresa">Empresa</option>
                                <option value="sucursal">Sucursal</option>
                                <option value="empresa externa">Empresa Externa</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">
                                <i class="bi bi-check-circle me-1"></i>Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">
                            <i class="bi bi-image me-1"></i>Logo de la Maquiladora
                        </label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previsualizarLogo(this)">
                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF (máx. 2MB)</small>
                        <div id="previewLogo" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="guardarMaquiladora()">
                    <i class="bi bi-save me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let tabla;

$(document).ready(function() {
    cargarMaquiladoras();
});

function cerrarSesion() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: "¿Estás seguro de que deseas salir?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2c5364',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Hacer petición AJAX para destruir la sesión
            $.ajax({
                url: '<?= base_url('logout') ?>',
                type: 'GET',
                complete: function() {
                    // Redirigir al login de maquiladoras después de cerrar sesión
                    window.location.href = '<?= base_url('login_maquiladoras') ?>';
                }
            });
        }
    });
}

function cargarMaquiladoras() {
    if (tabla) {
        tabla.destroy();
    }

    tabla = $('#tablaMaquiladoras').DataTable({
        ajax: {
            url: '<?= base_url('api/maquiladoras/listar') ?>',
            dataSrc: ''
        },
        columns: [
            { data: 'idmaquiladora' },
            { data: 'Nombre_Maquila' },
            { data: 'Dueno' },
            { data: 'Telefono' },
            { data: 'Correo' },
            { data: 'Domicilio' },
            { data: 'tipo' },
            { 
                data: 'status',
                render: function(data) {
                    if (data == 1 || data === '1') {
                        return '<span class="badge bg-success">Activo</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactivo</span>';
                    }
                }
            },
            { 
                data: 'logo',
                render: function(data) {
                    if (data) {
                        // Convertir el blob a base64 para mostrarlo
                        return `<img src="data:image/png;base64,${data}" alt="Logo" style="max-width: 50px; max-height: 50px; object-fit: contain;">`;
                    }
                    return '<span class="text-muted">Sin logo</span>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="editarMaquiladora(${row.idmaquiladora})" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarMaquiladora(${row.idmaquiladora})" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']]
    });
}

function limpiarFormulario() {
    $('#formMaquiladora')[0].reset();
    $('#idmaquiladora').val('');
    $('#tituloModal').text('Nueva Maquiladora');
    $('#previewLogo').html('');
}

function previsualizarLogo(input) {
    const preview = $('#previewLogo');
    preview.html('');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo muy grande',
                text: 'El logo no debe superar los 2MB'
            });
            input.value = '';
            return;
        }
        
        // Validar tipo
        if (!file.type.match('image.*')) {
            Swal.fire({
                icon: 'error',
                title: 'Formato inválido',
                text: 'Solo se permiten archivos de imagen'
            });
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.html(`
                <div class="text-center">
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    <p class="text-muted mt-2">Vista previa del logo</p>
                </div>
            `);
        };
        reader.readAsDataURL(file);
    }
}

function guardarMaquiladora() {
    const formData = new FormData();
    
    formData.append('idmaquiladora', $('#idmaquiladora').val());
    formData.append('Nombre_Maquila', $('#Nombre_Maquila').val());
    formData.append('Dueno', $('#Dueno').val());
    formData.append('Telefono', $('#Telefono').val());
    formData.append('Correo', $('#Correo').val());
    formData.append('Domicilio', $('#Domicilio').val());
    formData.append('tipo', $('#tipo').val());
    formData.append('status', $('#status').val());
    
    // Agregar el archivo de logo si existe
    const logoFile = $('#logo')[0].files[0];
    if (logoFile) {
        formData.append('logo', logoFile);
    }

    const id = $('#idmaquiladora').val();
    const url = id ? 
        '<?= base_url('api/maquiladoras/actualizar') ?>' : 
        '<?= base_url('api/maquiladoras/crear') ?>';

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: id ? 'Maquiladora actualizada correctamente' : 'Maquiladora creada correctamente',
                timer: 2000,
                showConfirmButton: false
            });
            $('#modalMaquiladora').modal('hide');
            cargarMaquiladoras();
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Ocurrió un error al guardar la maquiladora'
            });
        }
    });
}

function editarMaquiladora(id) {
    $.ajax({
        url: `<?= base_url('api/maquiladoras/obtener') ?>/${id}`,
        type: 'GET',
        success: function(data) {
            $('#idmaquiladora').val(data.idmaquiladora);
            $('#Nombre_Maquila').val(data.Nombre_Maquila);
            $('#Dueno').val(data.Dueno);
            $('#Telefono').val(data.Telefono);
            $('#Correo').val(data.Correo);
            $('#Domicilio').val(data.Domicilio);
            $('#tipo').val(data.tipo);
            $('#status').val(data.status);
            
            // Mostrar logo actual si existe
            if (data.logo) {
                $('#previewLogo').html(`
                    <div class="text-center">
                        <img src="data:image/png;base64,${data.logo}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        <p class="text-muted mt-2">Logo actual</p>
                    </div>
                `);
            }
            
            $('#tituloModal').text('Editar Maquiladora');
            $('#modalMaquiladora').modal('show');
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información de la maquiladora'
            });
        }
    });
}

function eliminarMaquiladora(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `<?= base_url('api/maquiladoras/eliminar') ?>/${id}`,
                type: 'DELETE',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'La maquiladora ha sido eliminada',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    cargarMaquiladoras();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar la maquiladora'
                    });
                }
            });
        }
    });
}
</script>

</body>
</html>
