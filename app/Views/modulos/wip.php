<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Work in Progress (WIP)</h1>
    <span class="badge bg-primary">Módulo 3</span>
</div>

<!-- Menú del Módulo 3 -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-2">
                <a href="<?= base_url('modulo3') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/ordenes') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-clipboard-data me-1"></i>Órdenes
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/wip') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-gear me-1"></i>WIP
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/incidencias') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-exclamation-triangle me-1"></i>Incidencias
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/reportes') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>Reportes
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/notificaciones') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-bell me-1"></i>Notificaciones
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Etapas de Producción</strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>Etapa</th>
                    <th>Responsable</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Progreso</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($etapas as $etapa): ?>
                    <tr>
                        <td><?= esc($etapa['etapa']) ?></td>
                        <td><?= esc($etapa['resp']) ?></td>
                        <td><?= esc($etapa['ini']) ?></td>
                        <td><?= esc($etapa['fin']) ?></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $etapa['prog'] ?>%">
                                    <?= $etapa['prog'] ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary">Actualizar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>