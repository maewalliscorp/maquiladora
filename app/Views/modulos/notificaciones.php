<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Notificaciones</h1>
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
        <strong>Lista de Notificaciones</strong>
    </div>
    <div class="card-body">
        <?php foreach ($items as $item): ?>
            <div class="p-3 mb-3 rounded" style="background:#a7c7e7; position:relative;">
                <span class="position-absolute" style="left:10px;top:14px;width:12px;height:12px;border-radius:50%;background:<?= esc($item['color'], 'attr') ?>"></span>
                <div class="ms-3">
                    <div class="d-flex">
                        <div class="fw-semibold flex-grow-1"><?= esc($item['titulo']) ?></div>
                        <small class="fw-bold" style="color:<?= esc($item['color'], 'attr') ?>"><?= esc($item['nivel']) ?></small>
                    </div>
                    <div class="text-muted small"><?= esc($item['sub']) ?></div>
                    <div class="mt-2">
                        <a href="#" class="btn btn-sm btn-dark">Ver detalle</a>
                        <a href="#" class="btn btn-sm btn-outline-dark">Completar</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>