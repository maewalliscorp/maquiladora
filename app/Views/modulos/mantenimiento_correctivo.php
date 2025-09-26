<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Mantenimiento Correctivo</h1>
    <span class="badge bg-danger">Averías</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Registrar avería/reparación</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-3"><label class="form-label">Fecha</label><input type="date" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Máquina</label><input class="form-control" placeholder="MC-0002"></div>
            <div class="col-md-3"><label class="form-label">Falla</label><input class="form-control" placeholder="Motor detenido"></div>
            <div class="col-md-3"><label class="form-label">Estado</label><select class="form-select"><option>Abierta</option><option>En reparación</option><option>Cerrada</option></select></div>
            <div class="col-12"><label class="form-label">Acción tomada</label><input class="form-control" placeholder="Cambio de polea"></div>
            <div class="col-12"><button class="btn btn-danger" type="button">Guardar</button></div>
        </form>
    </div>
</div>

<?php $hist = $hist ?? [
    ['fecha'=>'2025-09-20','maq'=>'MC-0002','falla'=>'Correa rota','accion'=>'Reemplazo','estado'=>'Cerrada'],
    ['fecha'=>'2025-09-22','maq'=>'MC-0003','falla'=>'Vibración','accion'=>'Ajuste base','estado'=>'En reparación'],
]; ?>
<div class="card shadow-sm">
    <div class="card-header"><strong>Historial por máquina</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-primary"><tr><th>Fecha</th><th>Máquina</th><th>Falla</th><th>Acción</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach($hist as $h): ?>
                <tr>
                    <td><?= esc($h['fecha']) ?></td><td><?= esc($h['maq']) ?></td><td><?= esc($h['falla']) ?></td><td><?= esc($h['accion']) ?></td>
                    <td><span class="badge <?= esc($h['estado']=='Cerrada'?'bg-success':($h['estado']=='En reparación'?'bg-warning text-dark':'bg-danger'),'attr') ?>"><?= esc($h['estado']) ?></span></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
