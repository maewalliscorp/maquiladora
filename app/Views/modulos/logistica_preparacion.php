<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Preparación de Envíos</h1>
    <span class="badge bg-primary">Packing</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Generar lista de empaque / etiquetas</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-6"><label class="form-label">Pedido</label><input class="form-control" placeholder="PED-2025-0045"></div>
            <div class="col-md-6"><label class="form-label">Destino</label><input class="form-control" placeholder="Cliente A, CDMX"></div>
            <div class="col-md-4"><label class="form-label">Cajas</label><input class="form-control" placeholder="5"></div>
            <div class="col-md-4"><label class="form-label">Peso total (kg)</label><input class="form-control" placeholder="48"></div>
            <div class="col-md-4"><label class="form-label">Volumen (m³)</label><input class="form-control" placeholder="0.9"></div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="button">Generar Packing List</button>
                <button class="btn btn-outline-primary" type="button">Etiquetas</button>
            </div>
        </form>
    </div>
</div>

<?php $cons = $cons ?? [
    ['pedido'=>'PED-0041','op'=>'OP-0011','cajas'=>3,'peso'=>25,'dest'=>'Cliente B'],
    ['pedido'=>'PED-0042','op'=>'OP-0012','cajas'=>6,'peso'=>54,'dest'=>'Cliente C'],
]; ?>
<div class="card shadow-sm">
    <div class="card-header"><strong>Consolidación de pedidos</strong></div>
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead class="table-primary"><tr><th>Pedido</th><th>OP</th><th>Cajas</th><th>Peso</th><th>Destino</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php foreach($cons as $c): ?>
                <tr>
                    <td><?= esc($c['pedido']) ?></td><td><?= esc($c['op']) ?></td><td><?= esc($c['cajas']) ?></td><td><?= esc($c['peso']) ?> kg</td><td><?= esc($c['dest']) ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="#">Agregar al envío</a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
