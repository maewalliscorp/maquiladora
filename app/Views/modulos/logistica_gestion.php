<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Gestión de Envíos</h1>
    <span class="badge bg-info text-dark">Tracking</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Registrar envío</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4"><label class="form-label">Empresa transporte</label><input class="form-control" placeholder="DHL"></div>
            <div class="col-md-4"><label class="form-label">Guía</label><input class="form-control" placeholder="JD014..."></div>
            <div class="col-md-4"><label class="form-label">Fecha salida</label><input type="date" class="form-control"></div>
            <div class="col-12"><button class="btn btn-primary" type="button">Guardar</button></div>
        </form>
    </div>
</div>

<?php $env = $env ?? [
    ['fecha'=>'2025-09-21','empresa'=>'DHL','guia'=>'JD0148899001','estado'=>'En tránsito'],
    ['fecha'=>'2025-09-22','empresa'=>'FedEx','guia'=>'FE99223311','estado'=>'Entregado'],
]; ?>
<div class="card shadow-sm">
    <div class="card-header"><strong>Envíos</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-primary"><tr><th>Fecha</th><th>Empresa</th><th>Guía</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php foreach($env as $e): ?>
                <tr>
                    <td><?= esc($e['fecha']) ?></td><td><?= esc($e['empresa']) ?></td><td><?= esc($e['guia']) ?></td>
                    <td><span class="badge <?= esc($e['estado']=='Entregado'?'bg-success':'bg-info text-dark','attr') ?>"><?= esc($e['estado']) ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="#">Ver tracking</a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
