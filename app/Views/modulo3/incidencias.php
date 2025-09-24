<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Incidencias</h1>
    <span class="badge bg-danger">Reportes</span>
</div>

<!-- Nueva incidencia -->
<div class="card shadow-sm mb-4">
    <div class="card-header"><strong>Nuevo Reporte</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">OP</label>
                <input class="form-control" placeholder="OP-0001">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select class="form-select">
                    <option>Paro de máquina</option>
                    <option>Falta de material</option>
                    <option>Calidad</option>
                    <option>Otro</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" rows="3" placeholder="Describe la incidencia..."></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-danger" type="button">Reportar</button>
                <a class="btn btn-outline-secondary" href="<?= base_url('modulo3/dashboard') ?>">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<!-- Historial -->
<div class="card shadow-sm">
    <div class="card-header"><strong>Historial</strong></div>
    <div class="card-body table-responsive">
        <?php
        // Fallback si $lista no viene o no es array
        $rows = (isset($lista) && is_array($lista) && count($lista)) ? $lista : [
                ['fecha'=>'2025-09-21','op'=>'OP-0001','tipo'=>'Paro de máquina','desc'=>'Mantenimiento no programado'],
                ['fecha'=>'2025-09-22','op'=>'OP-0003','tipo'=>'Falta de material','desc'=>'Faltan rollos de tela'],
        ];
        ?>
        <table class="table table-striped align-middle">
            <thead class="table-primary">
            <tr>
                <th>Fecha</th>
                <th>OP</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($rows)): ?>
                <?php foreach ($rows as $i): ?>
                    <tr>
                        <td><?= esc($i['fecha'] ?? '') ?></td>
                        <td><?= esc($i['op'] ?? '') ?></td>
                        <td><?= esc($i['tipo'] ?? '') ?></td>
                        <td><?= esc($i['desc'] ?? '') ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" type="button">Ver</button>
                            <button class="btn btn-sm btn-outline-danger"  type="button">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay incidencias registradas.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
