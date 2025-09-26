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
    .estatus-verde { background-color: #4caf50; }
    .estatus-amarillo { background-color: #ffeb3b; }
    .estatus-rojo { background-color: #f44336; }
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

    /* Estilos para los iconos de documentos */
    .doc-icon {
        font-size: 1.2rem;
        margin: 0 5px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .doc-icon:hover {
        transform: scale(1.1);
    }
</style>
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
            <thead>
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
            <?php if (isset($ordenes) && !empty($ordenes)): ?>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td><?= esc($orden['op']) ?></td>
                        <td><?= esc($orden['cliente']) ?></td>
                        <td><?= esc($orden['responsable']) ?></td>
                        <td><?= esc($orden['ini']) ?></td>
                        <td><?= esc($orden['fin']) ?></td>
                        <td>
                            <span class="badge bg-<?= $orden['estatus'] === 'En proceso' ? 'warning' : ($orden['estatus'] === 'Completada' ? 'success' : 'info') ?>">
                                <?= esc($orden['estatus']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary">Editar</a>
                            <a href="#" class="btn btn-sm btn-outline-info">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay órdenes registradas</td>
                </tr>
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
        $('#tablaOrdenes').DataTable({
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
