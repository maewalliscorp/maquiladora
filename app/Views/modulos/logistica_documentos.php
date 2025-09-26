<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Documentos de Embarque</h1>
    <span class="badge bg-secondary">Docs</span>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header"><strong>Generación de documentos</strong></div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary" type="button">Factura de envío</button>
            <button class="btn btn-outline-primary" type="button">Lista de empaque</button>
            <button class="btn btn-outline-primary" type="button">Etiqueta</button>
            <button class="btn btn-outline-primary" type="button">Aduanas</button>
        </div>
    </div>
</div>

<?php $docs = $docs ?? [
    ['tipo'=>'Factura','num'=>'FAC-2025-001','fecha'=>'2025-09-21','estado'=>'Emitida'],
    ['tipo'=>'Lista de empaque','num'=>'PL-2025-009','fecha'=>'2025-09-21','estado'=>'Emitida'],
]; ?>
<div class="card shadow-sm">
    <div class="card-header"><strong>Documentos generados</strong></div>
    <div class="card-body table-responsive">
        <table class="table align-middle">
            <thead class="table-primary"><tr><th>Tipo</th><th>Número</th><th>Fecha</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php foreach($docs as $d): ?>
                <tr>
                    <td><?= esc($d['tipo']) ?></td><td><?= esc($d['num']) ?></td><td><?= esc($d['fecha']) ?></td>
                    <td><span class="badge bg-success"><?= esc($d['estado']) ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="#">Descargar PDF</a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
