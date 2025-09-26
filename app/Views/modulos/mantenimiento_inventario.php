<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inventario de Maquinaria</h1>
    <span class="badge bg-secondary">Mantenimiento</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Registro de máquina</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4"><label class="form-label">Código</label><input class="form-control" placeholder="MC-0007"></div>
            <div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control" placeholder="Juki DDL-8700"></div>
            <div class="col-md-4"><label class="form-label">Fecha de compra</label><input type="date" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Ubicación</label><input class="form-control" placeholder="Línea 2"></div>
            <div class="col-md-4"><label class="form-label">Estado</label><select class="form-select"><option>Operativa</option><option>En reparación</option></select></div>
            <div class="col-12"><button class="btn btn-primary" type="button">Guardar</button></div>
        </form>
    </div>
</div>

<?php $maq = $maq ?? [
    ['cod'=>'MC-0001','modelo'=>'Juki DDL-8700','compra'=>'2022-01-10','ubic'=>'Línea 1','estado'=>'Operativa'],
    ['cod'=>'MC-0002','modelo'=>'Brother 8450','compra'=>'2021-07-05','ubic'=>'Línea 3','estado'=>'En reparación'],
]; ?>
<div class="card shadow-sm">
    <div class="card-header"><strong>Listado</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-primary"><tr><th>Código</th><th>Modelo</th><th>Compra</th><th>Ubicación</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php foreach($maq as $m): ?>
                <tr>
                    <td><?= esc($m['cod']) ?></td><td><?= esc($m['modelo']) ?></td><td><?= esc($m['compra']) ?></td><td><?= esc($m['ubic']) ?></td>
                    <td><span class="badge <?= esc($m['estado']=='Operativa'?'bg-success':'bg-warning text-dark','attr') ?>"><?= esc($m['estado']) ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="#">Editar</a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
