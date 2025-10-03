<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="me-3">CATÁLOGO DE DISEÑOS</h1>
        <a href="<?= base_url('modulo2/agregardiseno') ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> NUEVO DISEÑO
        </a>
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
                    <th>DETALLES</th>
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
                                <a href="<?= base_url('modulo2/editardiseno/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                            <td>
                                <a href="<?= base_url('modulo2/editardiseno/' . $d['id']) ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminarDiseno(<?= (int)$d['id'] ?>)">
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- JS Bootstrap + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
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
        });
    </script>
<?= $this->endSection() ?>