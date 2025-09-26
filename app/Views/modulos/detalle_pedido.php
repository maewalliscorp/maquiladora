<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
    }
    .info-item {
        margin-bottom: 12px;
    }
    .info-label {
        font-weight: bold;
        color: var(--color-text);
        font-size: 0.9rem;
        margin-bottom: 3px;
    }
    .info-value {
        color: var(--color-text);
        font-size: 0.95rem;
        padding-left: 5px;
    }
    .modelo-img {
        max-width: 100%;
        max-height: 250px;
        border-radius: 8px;
        border: 2px solid var(--color-primary-700);
    }
    .btn-volver {
        background-color: var(--color-primary-700);
        border: none;
        font-weight: bold;
        padding: 8px 20px;
    }
    .btn-volver:hover {
        background-color: var(--color-primary-600);
    }
    .progress {
        height: 20px;
        margin: 10px 0;
    }
    .progress-bar {
        background-color: var(--color-primary-700);
        font-size: 0.8rem;
    }
    .section-title {
        font-size: 1.4rem;
        margin-bottom: 20px;
        color: var(--color-text-on-bg);
    }
    .compact-row {
        margin-bottom: 8px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Detalle del Pedido <?= isset($pedido['id']) ? '#'.esc($pedido['id']) : '' ?></h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="<?= base_url('modulo1') ?>" class="btn btn-volver">← Volver</a>
</div>

<!-- Información del Cliente Compacta -->
<div class="card shadow-sm">
    <div class="card-header">
        <strong>📊 INFORMACIÓN DEL CLIENTE</strong>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <div class="info-label">Empresa:</div>
                    <div class="info-value"><?= esc($pedido['empresa'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contacto:</div>
                    <div class="info-value"><?= esc($pedido['contacto'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Teléfono:</div>
                    <div class="info-value"><?= esc($pedido['telefono'] ?? '') ?></div>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= esc($pedido['email'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">RFC:</div>
                    <div class="info-value"><?= esc($pedido['rfc'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Dirección:</div>
                    <div class="info-value"><?= esc($pedido['direccion'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Descripción y Especificaciones en una sola fila -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <strong>📝 DESCRIPCIÓN</strong>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <div class="info-label">Producto:</div>
                    <div class="info-value"><?= esc($pedido['descripcion'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Cantidad:</div>
                    <div class="info-value"><?= esc($pedido['cantidad'] ?? '') ?><?= isset($pedido['cantidad']) ? ' unidades' : '' ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Entrega:</div>
                    <div class="info-value"><?= esc($pedido['fecha_entrega'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <strong>⚙️ ESPECIFICACIONES</strong>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <div class="info-label">Tallas:</div>
                    <div class="info-value"><?= esc($pedido['tallas'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Material:</div>
                    <div class="info-value"><?= esc($pedido['materiales'] ?? '') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Color:</div>
                    <div class="info-value"><?= esc($pedido['color'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progreso y Modelo en una sola fila -->
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <strong>📈 PROGRESO</strong>
            </div>
            <div class="card-body text-center">
                <div class="info-item">
                    <div class="info-label">Estado actual:</div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                             style="width: <?= isset($pedido['progreso']) ? (int)$pedido['progreso'] : 0 ?>%;"
                             aria-valuenow="<?= isset($pedido['progreso']) ? (int)$pedido['progreso'] : 0 ?>"
                             aria-valuemin="0" aria-valuemax="100">
                            <?= isset($pedido['progreso']) ? (int)$pedido['progreso'] : 0 ?>%
                        </div>
                    </div>
                </div>
                <div class="info-item mt-3">
                    <div class="info-label">Modelo:</div>
                    <div class="info-value"><?= esc($pedido['modelo'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <strong>🖼️ IMAGEN DEL MODELO</strong>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($pedido['modelo'])): ?>
                <img src="https://via.placeholder.com/500x300/3E8FCC/ffffff?text=Modelo+<?= urlencode($pedido['modelo']) ?>"
                     alt="Modelo <?= esc($pedido['modelo']) ?>" class="modelo-img">
                <?php endif; ?>
                <p class="mt-3 info-value"><?= esc($pedido['descripcion'] ?? '') ?></p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>