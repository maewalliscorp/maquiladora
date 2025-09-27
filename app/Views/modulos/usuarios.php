<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .estatus {
            font-weight: bold;
            color: #000;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .estatus-activo { background-color: #4caf50; color: white; }
        .estatus-inactivo { background-color: #f44336; color: white; }
        .estatus-bajadelaempresa { background-color: #9e9e9e; color: white; }
        .estatus-enespera { background-color: #ff9800; color: white; }
        .btn-accion {
            margin: 0 5px;
        }

        /* Estilos específicos para el buscador */
        .dataTables_wrapper .dataTables_filter {
            text-align: center;
            margin-bottom: 15px;
        }
        .dataTables_wrapper .dataTables_filter label {
            color: var(--color-text);
            font-size: 1.1rem;
            font-weight: bold;
        }
        .dataTables_wrapper .dataTables_filter input {
            background-color: #F3F8FE !important;
            color: #333 !important;
            border: 1px solid #d7e3ef !important;
            border-radius: 5px !important;
            padding: 8px 12px !important;
            font-size: 1rem !important;
            width: 300px !important;
            margin-left: 10px !important;
        }

        .rol-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .rol-administrador { background-color: #dc3545; color: white; }
        .rol-supervisor { background-color: #fd7e14; color: white; }
        .rol-operador { background-color: #6c757d; color: white; }
        .rol-diseñador { background-color: #20c997; color: white; }
        .rol-jefedeproducción { background-color: #6f42c1; color: white; }
        .rol-coordinador { background-color: #0dcaf0; color: white; }
        .rol-analista { background-color: #198754; color: white; }
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
                    <th>APELLIDO</th>
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
                    <td><?= $usuario['apellido'] ?></td>
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
                        <a href="<?= base_url('modulo11/editar/' . $usuario['id']) ?>" 
                           class="btn btn-sm btn-outline-primary btn-accion" title="Editar Usuario">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button onclick="eliminarUsuario(<?= $usuario['id'] ?>)" 
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- JS Bootstrap + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaUsuarios').DataTable({
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
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true
            });
        });

        function eliminarUsuario(id) {
            if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
                // Pendiente: implementar con backend cuando la BD esté lista
                alert("Función de eliminación pendiente de implementar con backend.");
            }
        }

        function verDetalles(id) {
            // Pendiente: implementar modal con detalles del usuario
            alert("Función de ver detalles pendiente de implementar.");
        }
    </script>
<?= $this->endSection() ?>