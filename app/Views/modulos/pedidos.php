<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Pedidos</h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>
<div class="mb-3 text-end">
    <a href="<?= base_url('modulo1/agregar') ?>" class="btn btn-primary">➕ Nuevo Pedido</a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Pedidos</strong>
    </div>
    <div class="card-body">
        <table id="tablaPedidos" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>Empresa</th>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Estatus</th>
                <th>Moneda</th>
                <th>Total</th>
                <th>Ver</th>
                <th>Editar</th>
                <th>Documento</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($pedidos)): ?>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= esc($p['id']) ?></td>
                        <td><?= esc($p['empresa'] ?? '-') ?></td>
                        <td><?= esc($p['folio'] ?? '-') ?></td>
                        <td><?= isset($p['fecha']) ? esc(date('Y-m-d', strtotime($p['fecha']))) : '-' ?></td>
                        <td><?= esc($p['estatus'] ?? '-') ?></td>
                        <td><?= esc($p['moneda'] ?? '-') ?></td>
                        <td><?= isset($p['total']) ? number_format((float)$p['total'], 2) : '0.00' ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-info" href="<?= base_url('modulo1/detalles/' . (int)$p['id']) ?>">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= base_url('modulo1/editar/' . (int)$p['id']) ?>">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                        <td>
                            <?php $docUrl = $p['documento_url'] ?? null; ?>
                            <?php if ($docUrl): ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= esc($docUrl) ?>">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
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
        $('#tablaPedidos').DataTable({
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
        // Nota: Los datos se cargarán desde la BD cuando esté disponible
    });

    function eliminarPedido(id) {
        // Pendiente: implementar con backend cuando la BD esté lista
        alert("Función no disponible hasta conectar la BD.");
    }
</script>
<?= $this->endSection() ?>