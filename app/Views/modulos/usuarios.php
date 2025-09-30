<?= $this->extend('layouts/main') ?>

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
                    <th>MAQUILADORA</th>
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
                        <span class="badge bg-info text-dark">
                            <?= $usuario['idmaquiladora'] ? 'ID: ' . $usuario['idmaquiladora'] : 'Sin asignar' ?>
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