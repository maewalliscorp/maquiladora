<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Órdenes de Producción</h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de Órdenes de Producción</strong>
    </div>
    <div class="card-body">
        <table id="tablaOrdenes" class="table table-striped table-bordered text-center align-middle">
            <thead class="table-light">
            <tr>
                <th>OP</th>
                <th>Cliente</th>
                <th>Responsable</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?= esc($orden['op']) ?></td>
                        <td><?= esc($orden['cliente']) ?></td>
                        <td><?= esc($orden['responsable']) ?></td>
                        <td><?= esc($orden['ini']) ?></td>
                        <td><?= esc($orden['fin']) ?></td>
                        <td>
                            <?php
                            $status = trim($orden['estatus'] ?? '');
                            $badge  = 'secondary';
                            if (strcasecmp($status,'En proceso') === 0)   $badge = 'warning';
                            elseif (strcasecmp($status,'Completada') === 0) $badge = 'success';
                            elseif (strcasecmp($status,'Planificada') === 0) $badge = 'info';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= esc($status ?: 'N/D') ?></span>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="#" class="btn btn-sm btn-outline-info">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-muted">No hay órdenes registradas</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#tablaOrdenes').DataTable({
            language: {
                sProcessing:   "Procesando...",
                sLengthMenu:   "Mostrar _MENU_ registros",
                sZeroRecords:  "No se encontraron resultados",
                sEmptyTable:   "Ningún dato disponible en esta tabla",
                sInfo:         "Mostrando _START_ a _END_ de _TOTAL_",
                sInfoEmpty:    "Mostrando 0 a 0 de 0",
                sInfoFiltered: "(filtrado de _MAX_ en total)",
                sSearch:       "Buscar:",
                oPaginate:     { sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
                oAria:         { sSortAscending:": Orden asc", sSortDescending:": Orden desc" }
            }
        });
    });
</script>
<?= $this->endSection() ?>
