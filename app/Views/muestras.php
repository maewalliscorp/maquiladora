<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Aprobación de Muestras</h1>
        <span class="badge bg-primary">Módulo Muestras</span>
    </div>
    <div class="mb-3 text-end">
        <a href="<?= base_url('muestras/crear') ?>" class="btn btn-primary">➕ Nueva Muestra</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Lista de Muestras</strong>
        </div>
        <div class="card-body">
            <table id="tablaMuestras" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Prototipo</th>
                    <th>Solicitado Por</th>
                    <th>Fecha Solicitud</th>
                    <th>Estado Muestra</th>
                    <th>Estado Control Calidad</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <!-- Datos de ejemplo -->
                <tr>
                    <td>M-001</td>
                    <td>PROTO-001</td>
                    <td>Juan Pérez</td>
                    <td>2024-01-15</td>
                    <td><span class="estado estado-pendiente">Pendiente</span></td>
                    <td><span class="estado estado-aprobado">Aprobado</span></td>
                    <td>
                        <a href="<?= base_url('muestras/evaluar/1') ?>" class="btn btn-info btn-sm btn-accion">Evaluar</a>
                        <a href="<?= base_url('muestras/detalle/1') ?>" class="btn btn-secondary btn-sm btn-accion">Ver</a>
                    </td>
                </tr>
                <tr>
                    <td>M-002</td>
                    <td>PROTO-002</td>
                    <td>María García</td>
                    <td>2024-01-18</td>
                    <td><span class="estado estado-aprobado">Aprobado</span></td>
                    <td><span class="estado estado-en-proceso">En Proceso</span></td>
                    <td>
                        <a href="<?= base_url('muestras/evaluar/2') ?>" class="btn btn-info btn-sm btn-accion">Evaluar</a>
                        <a href="<?= base_url('muestras/detalle/2') ?>" class="btn btn-secondary btn-sm btn-accion">Ver</a>
                    </td>
                </tr>
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
            $('#tablaMuestras').DataTable({
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
                }
            });
        });
    </script>
<?= $this->endSection() ?>