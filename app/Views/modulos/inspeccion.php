<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inspección</h1>
    <span class="badge bg-info">Calidad</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header"><strong>Pedidos para inspección</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-primary">
            <tr>
                <th style="width:80px">No.</th>
                <th>Empresa</th>
                <th>Descripción</th>
                <th>Estatus</th>
                <th class="text-end" style="width:140px">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($lista) && is_array($lista)): ?>
                <?php foreach ($lista as $r): ?>
                    <tr>
                        <td><?= esc($r['num']) ?></td>
                        <td><?= esc($r['empresa']) ?></td>
                        <td><?= esc($r['descripcion']) ?></td>
                        <td><?= esc($r['estatus']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-info"
                               href="<?= base_url('modulo3/inspeccion/evaluar/'.($r['id'] ?? 0)) ?>">
                                Evaluar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Sin pedidos para inspección.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
