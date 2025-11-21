<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .orden-header {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: .5rem;
        margin-bottom: 1rem;
    }
    .orden-box {
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        background-color: #ffffff;
    }
    .orden-label {
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Para evitar errores si por alguna razón no viene $orden
$orden = $orden ?? [];
$idOc  = (int)($orden['id_proveedorOC'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-center orden-header">
    <div>
        <h1 class="h4 mb-0">Orden de pedido a proveedor</h1>
        <small class="text-muted">
            Folio:
            <strong><?= esc($orden['id_proveedorOC'] ?? '—') ?></strong>
        </small>
    </div>

    <div class="d-flex gap-2">
        <?php if ($idOc > 0): ?>
            <a href="<?= site_url('proveedores/orden/' . $idOc . '/pdf') ?>"
               class="btn btn-sm btn-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF
            </a>
        <?php endif; ?>
        <a href="<?= site_url('proveedores') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left-short me-1"></i> Volver a proveedores
        </a>
    </div>
</div>

<div class="row">
    <!-- Datos de la orden -->
    <div class="col-lg-6">
        <div class="orden-box">
            <h5 class="mb-3">
                <i class="bi bi-receipt-cutoff me-1"></i> Datos de la orden
            </h5>

            <p class="mb-1">
                <span class="orden-label">Folio:</span>
                <?= esc($orden['id_proveedorOC'] ?? '—') ?>
            </p>
            <p class="mb-1">
                <span class="orden-label">Fecha:</span>
                <?= esc($orden['fecha'] ?? '—') ?>
            </p>
            <p class="mb-1">
                <span class="orden-label">Prioridad:</span>
                <?= esc($orden['prioridad'] ?? 'Normal') ?>
            </p>
            <p class="mb-0">
                <span class="orden-label">Estatus:</span>
                <?= esc($orden['estatus'] ?? 'Pendiente') ?>
            </p>
        </div>
    </div>

    <!-- Datos del proveedor -->
    <div class="col-lg-6">
        <div class="orden-box">
            <h5 class="mb-3">
                <i class="bi bi-building me-1"></i> Proveedor
            </h5>

            <p class="mb-1">
                <span class="orden-label">Nombre:</span>
                <?= esc($orden['proveedor_nombre'] ?? '—') ?>
            </p>
            <p class="mb-1">
                <span class="orden-label">Código:</span>
                <?= esc($orden['proveedor_codigo'] ?? '—') ?>
            </p>
            <p class="mb-1">
                <span class="orden-label">Email:</span>
                <?= esc($orden['proveedor_email'] ?? '—') ?>
            </p>
            <p class="mb-1">
                <span class="orden-label">Teléfono:</span>
                <?= esc($orden['proveedor_telefono'] ?? '—') ?>
            </p>
            <p class="mb-0">
                <span class="orden-label">Dirección:</span>
                <?= esc($orden['proveedor_direccion'] ?? '—') ?>
            </p>
        </div>
    </div>
</div>

<div class="orden-box">
    <h5 class="mb-3">
        <i class="bi bi-list-ul me-1"></i> Detalle del pedido
    </h5>
    <p class="mb-0" style="white-space: pre-line;">
        <?= esc($orden['descripcion'] ?? 'Sin descripción registrada.') ?>
    </p>
</div>

<div class="orden-box">
    <h6 class="mb-2">Autorización</h6>
    <p class="mb-4">
        Con esta orden se solicita al proveedor el suministro de los materiales descritos
        conforme a las condiciones acordadas.
    </p>

    <div class="row">
        <div class="col-md-6 mb-4 mb-md-0">
            <p class="mb-5">
                _______________________________<br>
                <span class="orden-label">Firma de autorización</span>
            </p>
        </div>
        <div class="col-md-6">
            <p class="mb-5">
                _______________________________<br>
                <span class="orden-label">Fecha</span>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Aquí puedes agregar lógica JS específica si la necesitas más adelante
</script>
<?= $this->endSection() ?>
