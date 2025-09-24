<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">WIP</h1>
    <span class="badge bg-secondary">En Proceso</span>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><strong>Avance de Producción</strong></div>
    <div class="card-body table-responsive">
        <?php
        // Fallback seguro si $etapas no viene o no es arreglo
        $rows = (isset($etapas) && is_array($etapas) && count($etapas)) ? $etapas : [
                ['etapa'=>'Corte','resp'=>'Juan Pérez','ini'=>'2025-09-20','fin'=>'2025-09-22','prog'=>80],
                ['etapa'=>'Confección','resp'=>'María López','ini'=>'2025-09-22','fin'=>'2025-09-25','prog'=>45],
                ['etapa'=>'Acabado','resp'=>'Carlos Ruiz','ini'=>'2025-09-25','fin'=>'2025-09-27','prog'=>10],
        ];
        ?>
        <table class="table align-middle">
            <thead class="table-primary">
            <tr>
                <th>Etapa</th>
                <th>Responsable</th>
                <th>Inicio</th>
                <th>Fin Est.</th>
                <th style="width:240px">Progreso</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $e): ?>
                <?php
                $p = (int)($e['prog'] ?? 0);
                $bar = $p >= 70 ? 'bg-success' : ($p >= 40 ? 'bg-warning text-dark' : 'bg-secondary');
                ?>
                <tr>
                    <td><?= esc($e['etapa'] ?? '') ?></td>
                    <td><?= esc($e['resp'] ?? '') ?></td>
                    <td><?= esc($e['ini'] ?? '') ?></td>
                    <td><?= esc($e['fin'] ?? '') ?></td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar <?= esc($bar, 'attr') ?>" style="width: <?= $p ?>%">
                                <?= $p ?>%
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
