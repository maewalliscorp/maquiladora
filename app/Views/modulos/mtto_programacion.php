<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Programación de Mantenimiento</h2>
        <a href="<?= site_url('mtto/calendario') ?>" class="btn btn-outline-light">
            <i class="bi bi-calendar3"></i> Ver Calendario
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Próximos trabajos</strong>
        </div>
        <div class="card-body">
            <table id="tablaProgramacion" class="table table-striped table-bordered align-middle text-center">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Máquina</th>
                    <th>Tarea</th>
                    <th>Fecha</th>
                    <th>Responsable</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($items ?? []) as $i): ?>
                    <tr>
                        <td><?= esc($i['id']) ?></td>
                        <td><?= esc($i['maquina']) ?></td>
                        <td><?= esc($i['tarea']) ?></td>
                        <td><?= esc($i['fecha']) ?></td>
                        <td><?= esc($i['responsable']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function(){
        $('#tablaProgramacion').DataTable({
            language: {
                sProcessing:"Procesando...", sLengthMenu:"Mostrar _MENU_", sZeroRecords:"No se encontraron resultados",
                sEmptyTable:"Sin datos", sInfo:"Mostrando _START_–_END_ de _TOTAL_", sInfoEmpty:"Mostrando 0–0 de 0",
                sInfoFiltered:"(filtrado de _MAX_)", sSearch:"Buscar:",
                oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" }
            }
        });
    });
</script>
<?= $this->endSection() ?>
