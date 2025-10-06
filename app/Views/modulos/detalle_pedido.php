<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Detalle del Pedido <?= isset($pedido['id']) ? '#'.esc($pedido['id']) : '' ?></h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="<?= base_url('modulo1') ?>" class="btn btn-volver">← Volver</a>
</div>

<?php if (empty($pedido)): ?>
    <div class="alert alert-warning">
        No se encontraron datos para este pedido. Verifica que exista en la base de datos y que el ID sea correcto.
    </div>
<?php endif; ?>

<?php if (!empty($_GET['debug'])): ?>
    <div class="alert alert-info" style="white-space:pre-wrap">
        <strong>DEBUG $pedido:</strong>
        <pre><?php print_r($pedido ?? null); ?></pre>
    </div>
<?php endif; ?>

<!-- Datos básicos del pedido -->
<div class="card shadow-sm mb-3">
    <div class="card-header">
        <strong>📄 DATOS DEL PEDIDO</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="info-item">
                    <div class="info-label">Folio:</div>
                    <div class="info-value"><?= esc($pedido['folio'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <div class="info-label">Fecha:</div>
                    <div class="info-value"><?= esc($pedido['fecha'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="info-item">
                    <div class="info-label">Estatus:</div>
                    <div class="info-value"><?= esc($pedido['estatus'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="info-item">
                    <div class="info-label">Moneda:</div>
                    <div class="info-value"><?= esc($pedido['moneda'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="info-item">
                    <div class="info-label">Total:</div>
                    <div class="info-value"><?= esc($pedido['total'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
    
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
                    <div class="info-value"><?= esc($pedido['empresa'] ?? ($pedido['cliente']['nombre'] ?? '')) ?></div>
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

<!-- Diseño asignado al cliente / a la OP -->
<div class="card shadow-sm mt-3">
    <div class="card-header">
        <strong>🎨 DISEÑO ASIGNADO</strong>
    </div>
    <div class="card-body">
        <?php if (!empty($pedido['diseno'])): ?>
            <?php $d = $pedido['diseno']; $ver = is_array($d['version'] ?? null) ? $d['version'] : null; ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-label">Código:</div>
                        <div class="info-value"><?= esc($d['codigo'] ?? '') ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-label">Nombre:</div>
                        <div class="info-value"><?= esc($d['nombre'] ?? '') ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-label">Versión:</div>
                        <div class="info-value"><?= esc($ver['version'] ?? '') ?></div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-8">
                    <div class="info-item">
                        <div class="info-label">Descripción:</div>
                        <div class="info-value"><?= esc($d['descripcion'] ?? '') ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item">
                        <div class="info-label">Fecha versión:</div>
                        <div class="info-value"><?= esc($ver['fecha'] ?? '') ?></div>
                    </div>
                </div>
            </div>
            <?php if (!empty($ver['archivoCadUrl']) || !empty($ver['archivoPatronUrl'])): ?>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">CAD:</div>
                        <div class="info-value">
                            <?php if (!empty($ver['archivoCadUrl'])): ?>
                                <a href="<?= base_url($ver['archivoCadUrl']) ?>" target="_blank">Descargar CAD</a>
                            <?php else: ?>
                                <span class="text-muted">No disponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Patrón:</div>
                        <div class="info-value">
                            <?php if (!empty($ver['archivoPatronUrl'])): ?>
                                <a href="<?= base_url($ver['archivoPatronUrl']) ?>" target="_blank">Descargar Patrón</a>
                            <?php else: ?>
                                <span class="text-muted">No disponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning mb-0">
                No hay un diseño ligado a este pedido. <?= !empty($pedido['disenos']) ? 'El cliente tiene '.count($pedido['disenos']).' diseño(s) registrados.' : 'El cliente no tiene diseños registrados.' ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Orden de Producción -->
<?php if (!empty($pedido['op_id']) || !empty($pedido['op_folio'])): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header">
        <strong>🏭 ORDEN DE PRODUCCIÓN</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="info-item">
                    <div class="info-label">OP ID:</div>
                    <div class="info-value"><?= esc($pedido['op_id'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <div class="info-label">Folio OP:</div>
                    <div class="info-value"><?= esc($pedido['op_folio'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <div class="info-label">Cantidad plan:</div>
                    <div class="info-value"><?= esc($pedido['op_cantidadPlan'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-item">
                    <div class="info-label">Estatus OP:</div>
                    <div class="info-value"><?= esc($pedido['op_status'] ?? '') ?></div>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Inicio plan:</div>
                    <div class="info-value"><?= esc($pedido['op_fechaInicioPlan'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Fin plan:</div>
                    <div class="info-value"><?= esc($pedido['op_fechaFinPlan'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Versión diseño:</div>
                    <div class="info-value"><?= esc($pedido['op_disenoVersionId'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tarjeta de OP -->
<div class="card shadow-sm mt-3">
    <div class="card-header">
        <strong>📝 TARJETA DE OP</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">OP ID:</div>
                    <div class="info-value"><?= esc($pedido['op_id'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Folio OP:</div>
                    <div class="info-value"><?= esc($pedido['op_folio'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-item">
                    <div class="info-label">Estatus OP:</div>
                    <div class="info-value"><?= esc($pedido['op_status'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>