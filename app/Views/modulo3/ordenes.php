<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Órdenes de Producción</h1>
    <span class="badge bg-secondary">Gestión</span>
</div>

<!-- Formulario (opcional) -->
<div class="card shadow-sm mb-4">
    <div class="card-header"><strong>Nueva Orden</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">OP</label>
                <input class="form-control" placeholder="OP-0004">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cliente</label>
                <input class="form-control" placeholder="Cliente">
            </div>
            <div class="col-md-4">
                <label class="form-label">Responsable</label>
                <input class="form-control" placeholder="Nombre">
            </div>
            <div class="col-md-6">
                <label class="form-label">Inicio</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Fin Estimado</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="button">Guardar</button>
                <a class="btn btn-outline-secondary" href="<?= base_url('modulo3/dashboard') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<!-- Listado -->
<div class="card shadow-sm">
    <div class="card-header"><strong>Listado de Órdenes</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-primary">
            <tr>
                <th>OP</th>
                <th>Cliente</th>
                <th>Responsable</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estatus</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (isset($ordenes) && is_array($ordenes) && count($ordenes)): ?>
                <?php foreach ($ordenes as $o): ?>
                    <tr>
                        <td><?= esc($o['op'] ?? '') ?></td>
                        <td><?= esc($o['cliente'] ?? '') ?></td>
                        <td><?= esc($o['responsable'] ?? '') ?></td>
                        <td><?= esc($o['ini'] ?? '') ?></td>
                        <td><?= esc($o['fin'] ?? '') ?></td>
                        <td>
                            <?php
                            $status = $o['estatus'] ?? '—';
                            $cls = 'bg-info text-dark';
                            if ($status === 'Planificada') $cls = 'bg-warning text-dark';
                            if ($status === 'En proceso')  $cls = 'bg-info text-dark';
                            if ($status === 'Completada')  $cls = 'bg-success';
                            ?>
                            <span class="badge <?= esc($cls, 'attr') ?>"><?= esc($status) ?></span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="#">Editar</a>
                            <button class="btn btn-sm btn-outline-danger" type="button">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay órdenes registradas.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
