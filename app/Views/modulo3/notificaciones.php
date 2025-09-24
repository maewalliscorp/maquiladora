<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-3">
    <h1 class="me-3">Notificaciones</h1>
    <div class="ms-auto small">
        <span class="me-3"><span class="me-1" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e03131"></span>Crítica</span>
        <span class="me-3"><span class="me-1" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#ffd43b"></span>Alta</span>
        <span class="me-3"><span class="me-1" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#4dabf7"></span>Media</span>
        <span class=""><span class="me-1" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#37b24d"></span>Baja</span>
    </div>
</div>

<?php foreach($items as $n): ?>
    <div class="card shadow-sm mb-3" style="background:#a7c7e7;border-radius:14px;">
        <div class="card-body position-relative">
            <!-- punto de color a la izquierda -->
            <span class="position-absolute" style="left:14px;top:24px;display:inline-block;width:14px;height:14px;border-radius:50%;background:<?= esc($n['color']) ?>"></span>
            <div class="ms-4">
                <div class="d-flex">
                    <h5 class="mb-1 flex-grow-1"><?= esc($n['titulo']) ?></h5>
                    <strong class="text-end" style="color:<?= esc($n['color']) ?>"><?= esc($n['nivel']) ?></strong>
                </div>
                <div class="text-muted mb-2"><strong><?= esc($n['sub']) ?></strong></div>
                <div class="">
                    <a href="#" class="btn btn-sm btn-dark">Ver detalle</a>
                    <a href="#" class="btn btn-sm btn-outline-dark">Completar</a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?= $this->endSection() ?>
