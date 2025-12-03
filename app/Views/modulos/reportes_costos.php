<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= base_url('modulo3/reportes') ?>" class="btn btn-outline-secondary me-3">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <h1 class="me-3 mb-0">Gestor de Hojas de Costos</h1>
    <span class="badge bg-warning text-dark">Reportes</span>
    <div class="ms-auto">
        <a href="<?= base_url('modulo3/control-bultos/plantillas/nueva') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nueva Hoja de Costos
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaCostos" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Plantilla</th>
                        <th>Tipo Prenda</th>
                        <th>Operaciones</th>
                        <th>Última Actualización</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plantillas as $p): ?>
                        <?php
                        $ops = is_string($p['operaciones']) ? json_decode($p['operaciones'], true) : $p['operaciones'];
                        $count = is_array($ops) ? count($ops) : 0;
                        ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td class="fw-bold"><?= esc($p['nombre_plantilla']) ?></td>
                            <td><?= esc($p['tipo_prenda']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= $count ?> operaciones</span></td>
                            <td><?= $p['updated_at'] ?? $p['created_at'] ?></td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="<?= base_url('modulo3/reportes/costos/ver/' . $p['id']) ?>"
                                        class="btn btn-sm btn-info text-white" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('modulo3/reportes/costos/descargar/' . $p['id']) ?>"
                                        class="btn btn-sm btn-success" title="Descargar / Imprimir" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="<?= base_url('modulo3/control-bultos/plantillas/editor/' . $p['id']) ?>"
                                        class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#tablaCostos').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[0, 'desc']]
        });
    });
</script>
<?= $this->endSection() ?>