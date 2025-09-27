<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inicio</h1>
    <span class="badge bg-primary">Dashboard</span>
</div>

<!-- KPIs -->
<div class="row g-3">
    <?php
    // Fuente: $kpis del controlador, con fallback local
    $cards = $kpis ?? [
        ['label'=>'Órdenes Activas',     'value'=>8,  'color'=>'primary'],
        ['label'=>'WIP (%)',             'value'=>62, 'color'=>'info'],
        ['label'=>'Incidencias Hoy',     'value'=>3,  'color'=>'danger'],
        ['label'=>'Órdenes Completadas', 'value'=>21, 'color'=>'success'],
    ];

    // Valor WIP robusto (no dependemos del índice 1)
    $wipValue = 62;
    foreach ($cards as $tmp) {
        if (isset($tmp['label']) && stripos($tmp['label'], 'wip') !== false) {
            $wipValue = (int)($tmp['value'] ?? 62);
            break;
        }
    }

    foreach ($cards as $c): ?>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="section-title">
                        <span class="dot"></span><span><?= esc($c['label']) ?></span>
                    </div>
                    <div class="display-6 fw-semibold <?= 'text-' . esc($c['color'] ?? 'primary', 'attr') ?>">
                        <?= esc($c['value'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mt-1">
    <!-- IZQUIERDA -->
    <div class="col-lg-8">

        <!-- Accesos rápidos -->
        <div class="card shadow-sm mb-3">
            <div class="card-header"><strong>Accesos rápidos</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Módulo 1 -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-primary" href="<?= base_url('modulo1/pedidos') ?>">Pedidos</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-primary" href="<?= base_url('modulo1/ordenes') ?>">Ordenes</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-info" href="<?= base_url('modulo3/ordenesclientes') ?>">Órdenes Clientes</a>
                    </div>
                    <!-- Módulo 2 -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-primary" href="<?= base_url('modulo2/catalogodisenos') ?>">Catalogo de Diseños</a>
                    </div>
                    
                    <!-- Módulo 3 - Operaciones -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-primary" href="<?= base_url('modulo3/wip') ?>">WIP</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-primary" href="<?= base_url('modulo3/incidencias') ?>">Incidencias</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-primary" href="<?= base_url('modulo3/reportes') ?>">Reportes</a>
                    </div>
                    
                    <!-- Módulo 3 - Planificación -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-success" href="<?= base_url('modulo3/mrp') ?>">MRP</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-danger" href="<?= base_url('modulo3/desperdicios') ?>">Desperdicios</a>
                    </div>
                    
                    <!-- Módulo 3 - Mantenimiento -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-secondary" href="<?= base_url('modulo3/mantenimiento_inventario') ?>">Inventario Maq.</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-warning" href="<?= base_url('modulo3/mantenimiento_correctivo') ?>">Mant. Correctivo</a>
                    </div>
                    
                    <!-- Módulo 3 - Logística -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-info" href="<?= base_url('modulo3/logistica_preparacion') ?>">Prep. Envíos</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-info" href="<?= base_url('modulo3/logistica_gestion') ?>">Gestión Envíos</a>
                    </div>
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-info" href="<?= base_url('modulo3/logistica_documentos') ?>">Docs. Embarque</a>
                    </div>
                    
                    <!-- Módulo 11 - Gestión de Usuarios -->
                    <div class="col-md-3">
                        <a class="btn w-100 btn-outline-dark" href="<?= base_url('modulo11/usuarios') ?>">Gestión Usuarios</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="card shadow-sm">
            <div class="card-header"><strong>Resumen de producción</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Órdenes en proceso
                                <span class="badge bg-primary rounded-pill"><?= esc($ordersInProcess ?? 3) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Tareas vencidas
                                <span class="badge bg-danger rounded-pill"><?= esc($overdue ?? 1) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Incidencias abiertas
                                <span class="badge bg-warning rounded-pill"><?= esc($openIncidents ?? 2) ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-2">Área reservada para gráfica/KPIs detallados.</p>
                        <div class="progress mb-2">
                            <div class="progress-bar" style="width: <?= $wipValue ?>%"><?= $wipValue ?>%</div>
                        </div>
                        <small class="text-muted">Avance promedio WIP</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- DERECHA: Notificaciones preview -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong>Notificaciones</strong>
                <span class="badge bg-danger rounded-pill"><?= esc($notifCount ?? 3) ?></span>
            </div>
            <div class="card-body">
                <?php
                $recent = $recentNotifs ?? [
                    ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Módulo: Confección (WIP)'],
                    ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Módulo: Prototipos'],
                    ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Módulo: Prototipos'],
                ];
                foreach ($recent as $n): ?>
                    <div class="p-3 mb-3 rounded" style="background:#a7c7e7; position:relative;">
                        <span class="position-absolute" style="left:10px;top:14px;width:12px;height:12px;border-radius:50%;background:<?= esc($n['color'], 'attr') ?>"></span>
                        <div class="ms-3">
                            <div class="d-flex">
                                <div class="fw-semibold flex-grow-1"><?= esc($n['titulo']) ?></div>
                                <small class="fw-bold" style="color:<?= esc($n['color'], 'attr') ?>"><?= esc($n['nivel']) ?></small>
                            </div>
                            <div class="text-muted small"><?= esc($n['sub']) ?></div>
                            <div class="mt-2">
                                <a href="#" class="btn btn-sm btn-dark">Ver detalle</a>
                                <a href="#" class="btn btn-sm btn-outline-dark">Completar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="<?= base_url('modulo3/notificaciones') ?>" class="btn btn-sm btn-outline-primary w-100">Ver todas</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
