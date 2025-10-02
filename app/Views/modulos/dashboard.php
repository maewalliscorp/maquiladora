<?= $this->extend('layouts/main') ?>

<?php // --- Estilos específicos del dashboard --- ?>
<?= $this->section('head') ?>
<style>
    body { background: #f7f9fc; }
    .kpi-card { border: 0; border-radius: 1rem; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .chart-card { border: 0; border-radius: 1rem; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .chart-wrap { position: relative; height: 280px; }
    .sticky-col { position: sticky; top: 1rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-3">
    <h1 class="me-3 mb-0">Dashboard</h1>
    <span class="badge bg-primary">Producción · Inventarios · Calidad · Logística</span>
</div>

<?php
// Fuente: $kpis del controlador, con fallback local
$cards = $kpis ?? [
        ['label'=>'Órdenes activas', 'value'=>18, 'icon'=>'bi-clipboard2-check', 'muted'=>'+2 hoy', 'mutedClass'=>'text-success'],
        ['label'=>'WIP (pzs)',       'value'=>362,'icon'=>'bi-gear-wide-connected','muted'=>'En proceso', 'mutedClass'=>'text-secondary'],
        ['label'=>'Defectos (%)',    'value'=>'1.8%','icon'=>'bi-activity','muted'=>'-0.4% vs. ayer','mutedClass'=>'text-success'],
        ['label'=>'Stock crítico',   'value'=>5,  'icon'=>'bi-box-seam','muted'=>'Por debajo de mínimo','mutedClass'=>'text-warning'],
];

// Notificaciones (fallback)
$notifCount = $notifCount ?? 3;
$recentNotifs = $recentNotifs ?? [
        ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Confección (WIP)'],
        ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Prototipos'],
        ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'OC #1045 recibida','sub'=>'Almacén PT • Entrada parcial'],
];

// Valor WIP robusto si lo necesitas para una barra de progreso
$wipValue = 62;
foreach ($cards as $tmp) {
    if (stripos($tmp['label'], 'wip') !== false) {
        $wipValue = (int) filter_var($tmp['value'], FILTER_SANITIZE_NUMBER_INT);
        break;
    }
}
?>

<div class="row g-4">
    <!-- Columna principal -->
    <div class="col-12 col-xxl-9">
        <!-- KPIs -->
        <div class="row g-3 mb-1">
            <?php foreach ($cards as $c): ?>
                <div class="col-6 col-lg-3">
                    <div class="card kpi-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-secondary small"><?= esc($c['label']) ?></div>
                                <div class="fs-3 fw-bold"><?= esc($c['value']) ?></div>
                            </div>
                            <i class="bi <?= esc($c['icon'] ?? 'bi-graph-up') ?> fs-2"></i>
                        </div>
                        <?php if (!empty($c['muted'])): ?>
                            <div class="small mt-2 <?= esc($c['mutedClass'] ?? 'text-secondary') ?>">
                                <?= esc($c['muted']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- GRÁFICAS -->
        <div class="row g-4">
            <!-- Producción -->
            <div class="col-12">
                <div class="card chart-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Producción · Órdenes por semana</h5>
                            <span class="text-secondary small">Últimas 6 semanas</span>
                        </div>
                        <div class="chart-wrap">
                            <canvas id="chProduccion"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventario -->
            <div class="col-12 col-lg-6">
                <div class="card chart-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Inventario · Stock vs Min/Max</h5>
                            <span class="text-secondary small">Top 6 insumos</span>
                        </div>
                        <div class="chart-wrap">
                            <canvas id="chInventario"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calidad -->
            <div class="col-12 col-lg-6">
                <div class="card chart-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Calidad · Tasa de defectos</h5>
                            <span class="text-secondary small">Últimos 30 días</span>
                        </div>
                        <div class="chart-wrap">
                            <canvas id="chCalidad"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logística -->
            <div class="col-12">
                <div class="card chart-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Logística · Órdenes de compra por estado</h5>
                            <span class="text-secondary small">Hoy</span>
                        </div>
                        <div class="chart-wrap">
                            <canvas id="chLogistica"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /row gráficas -->
    </div>

    <!-- Columna Notificaciones -->
    <div class="col-12 col-xxl-3">
        <div class="sticky-col">
            <div class="card chart-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Notificaciones</h5>
                        <span class="badge text-bg-secondary"><?= esc($notifCount) ?></span>
                    </div>
                    <hr>
                    <div class="vstack gap-3" id="panelNotificaciones">
                        <?php foreach ($recentNotifs as $n): ?>
                            <div class="p-3 rounded" style="background:#eef5ff; position:relative;">
                                <span class="position-absolute" style="left:10px;top:14px;width:12px;height:12px;border-radius:50%;background:<?= esc($n['color'], 'attr') ?>"></span>
                                <div class="ms-3">
                                    <div class="d-flex">
                                        <div class="fw-semibold flex-grow-1"><?= esc($n['titulo']) ?></div>
                                        <small class="fw-bold" style="color:<?= esc($n['color'], 'attr') ?>"><?= esc($n['nivel']) ?></small>
                                    </div>
                                    <div class="text-muted small"><?= esc($n['sub']) ?></div>
                                    <div class="mt-2 d-flex gap-2">
                                        <a href="#" class="btn btn-sm btn-dark">Ver detalle</a>
                                        <a href="#" class="btn btn-sm btn-outline-dark">Completar</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= base_url('modulo3/notificaciones') ?>" class="btn btn-sm btn-outline-primary w-100 mt-3">Ver todas</a>
                </div>
            </div>

            <!-- Filtros compactos (opcional) -->
            <div class="card chart-card">
                <div class="card-body">
                    <h6 class="text-secondary mb-2">Filtros rápidos</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <select class="form-select form-select-sm" id="selRango">
                                <option value="7">Últimos 7 días</option>
                                <option value="30" selected>Últimos 30 días</option>
                                <option value="90">Últimos 90 días</option>
                            </select>
                        </div>
                        <div class="col-12 d-grid">
                            <button class="btn btn-sm btn-primary" id="btnAplicarFiltros">
                                <i class="bi bi-funnel"></i> Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /sticky -->
    </div>
</div>

<?= $this->endSection() ?>

<?php // --- Scripts (Chart.js y demo de datos). Si ya cargas Chart.js en el layout, quita el <script> CDN. ?>
<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    // ===== Demo de datos (reemplaza por fetch a tus endpoints) =====
    const semanas = ['S-34','S-35','S-36','S-37','S-38','S-39'];
    const ordenesActivas = [10,14,12,15,18,17];
    const ordenesCompletadas = [8,9,11,12,14,16];

    const insumos = ['Tela Denim','Hilo 40/2','Botón #18','Cierre 20cm','Etiqueta','Forro'];
    const stockActual = [520,1200,2600,800,4500,1100];
    const stockMin =    [600,1000,2000,900,3000,1200];
    const stockMax =    [2000,2000,3500,1400,6000,1800];

    const dias = Array.from({length: 30}, (_,i)=>`D-${i+1}`);
    const tasaDefectos = dias.map((_,i)=> (Math.sin(i/5)*0.6 + 2).toFixed(2));

    const estados = ['Pendiente','Parcial','Recibida','Cancelada'];
    const ocEstado = [8,3,12,1];

    // ===== Producción (barras apiladas) =====
    new Chart(document.getElementById('chProduccion'), {
        type: 'bar',
        data: {
            labels: semanas,
            datasets: [
                { label: 'Activas', data: ordenesActivas, borderWidth: 1 },
                { label: 'Completadas', data: ordenesCompletadas, borderWidth: 1 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision:0 } } },
            plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } }
        }
    });

    // ===== Inventario (mixto) =====
    new Chart(document.getElementById('chInventario'), {
        data: {
            labels: insumos,
            datasets: [
                { type: 'bar',  label: 'Stock actual', data: stockActual, borderWidth: 1 },
                { type: 'line', label: 'Stock mínimo', data: stockMin,    borderWidth: 2, fill: false },
                { type: 'line', label: 'Stock máximo', data: stockMax,    borderWidth: 2, fill: false }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top' } }
        }
    });

    // ===== Calidad (línea) =====
    new Chart(document.getElementById('chCalidad'), {
        type: 'line',
        data: { labels: dias, datasets: [{ label: '% Defectos', data: tasaDefectos, tension: .3, borderWidth: 2, pointRadius: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y}%` } } }
        }
    });

    // ===== Logística (dona) =====
    new Chart(document.getElementById('chLogistica'), {
        type: 'doughnut',
        data: { labels: estados, datasets: [{ data: ocEstado }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, cutout: '55%' }
    });

    // ===== Hook filtros demo =====
    document.getElementById('btnAplicarFiltros')?.addEventListener('click', () => {
        const rango = document.getElementById('selRango').value;
        // Ejemplo de integración:
        // fetch(`<?= base_url('api/dashboard') ?>?range=${rango}`)
        //   .then(r=>r.json()).then(data => { /* actualizar datasets y chart.update() */ });
        alert(`(Demo) Filtros aplicados: últimos ${rango} días`);
    });
</script>
<?= $this->endSection() ?>
