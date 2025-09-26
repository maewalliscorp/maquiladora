<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Desperdicios & Reprocesos</h1>
    <span class="badge bg-danger">Calidad</span>
</div>

<div class="row g-3">
    <!-- Registro de material descartado -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Registro de material descartado/defectuoso</strong></div>
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">OP</label>
                        <input class="form-control" placeholder="OP-0012">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Material / Motivo</label>
                        <input class="form-control" placeholder="Tela manchada / corte incorrecto">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cantidad</label>
                        <input class="form-control" placeholder="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unidad</label>
                        <select class="form-select"><option>m</option><option>kg</option><option>pz</option></select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-danger" type="button">Registrar</button>
                    </div>
                </form>
                <hr>
                <?php $desp = $desp ?? [
                    ['fecha'=>'2025-09-20','op'=>'OP-0012','mat'=>'Tela','cant'=>'15 m','motivo'=>'Manchas'],
                    ['fecha'=>'2025-09-21','op'=>'OP-0010','mat'=>'Piezas','cant'=>'8 pz','motivo'=>'Corte chueco'],
                ]; ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-primary"><tr><th>Fecha</th><th>OP</th><th>Material</th><th>Cant.</th><th>Motivo</th></tr></thead>
                        <tbody>
                        <?php foreach($desp as $x): ?>
                            <tr>
                                <td><?= esc($x['fecha']) ?></td><td><?= esc($x['op']) ?></td>
                                <td><?= esc($x['mat']) ?></td><td><?= esc($x['cant']) ?></td><td><?= esc($x['motivo']) ?></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reprocesos -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Control de unidades en reproceso</strong></div>
            <div class="card-body">
                <?php $rep = $rep ?? [
                    ['op'=>'OP-0014','tarea'=>'Costura lateral','pend'=>25,'resp'=>'María','eta'=>'2025-09-24'],
                    ['op'=>'OP-0011','tarea'=>'Rebasteado','pend'=>10,'resp'=>'Luis','eta'=>'2025-09-23'],
                ]; ?>
                <table class="table align-middle">
                    <thead class="table-primary"><tr><th>OP</th><th>Tarea</th><th>Pend.</th><th>Resp.</th><th>ETA</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach($rep as $r): ?>
                        <tr>
                            <td><?= esc($r['op']) ?></td><td><?= esc($r['tarea']) ?></td><td><?= esc($r['pend']) ?></td>
                            <td><?= esc($r['resp']) ?></td><td><?= esc($r['eta']) ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="#">Detalle</a></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
