<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Evaluación de inspección</h1>
    <span class="badge bg-info">Calidad</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Resumen</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Empresa:</strong> <?= esc($i['empresa'] ?? '') ?></div>
            <div class="col-md-4"><strong>Orden:</strong> #<?= esc($i['ordenProduccionId'] ?? '') ?></div>
            <div class="col-md-4"><strong>Punto:</strong> <?= esc($i['punto'] ?? '—') ?></div>
            <div class="col-md-4"><strong>Inspector:</strong> <?= esc($i['inspector'] ?? '—') ?></div>
            <div class="col-md-8"><strong>Descripción OP:</strong> <?= esc($i['ordenDescripcion'] ?? '') ?></div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><strong>Registrar evaluación</strong></div>
    <div class="card-body">
        <form class="row g-3" method="post"
              action="<?= base_url('modulo3/inspeccion/evaluar/'.($i['id'] ?? 0)) ?>">
            <?= csrf_field() ?>

            <div class="col-md-4">
                <label class="form-label">Resultado</label>
                <select name="resultado" class="form-select" required>
                    <?php $res = old('resultado', $i['resultado'] ?? ''); ?>
                    <option value="" disabled <?= $res===''?'selected':'' ?>>Selecciona…</option>
                    <option value="Aprobado"   <?= $res==='Aprobado'?'selected':'' ?>>Aprobado</option>
                    <option value="Rechazado"  <?= $res==='Rechazado'?'selected':'' ?>>Rechazado</option>
                    <option value="Pendiente"  <?= $res==='Pendiente'?'selected':'' ?>>Pendiente</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control"
                       value="<?= esc(old('fecha', $i['fecha'] ?? date('Y-m-d'))) ?>">
            </div>

            <div class="col-12">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="4"
                          placeholder="Notas del inspector…"><?= esc(old('observaciones', $i['observaciones'] ?? '')) ?></textarea>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">Guardar evaluación</button>
                <a href="<?= base_url('modulo3/inspeccion') ?>" class="btn btn-outline-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
