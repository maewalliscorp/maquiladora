<?= $this->extend('layouts/main') ?>

<?php // --- Estilos específicos del dashboard --- ?>
<?= $this->section('head') ?>
    <style>
        body {
            background: linear-gradient(135deg, #96beefff 0%, #ffffff 100%);
        }

        .kpi-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        }

        .chart-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        }

        .chart-wrap {
            position: relative;
            height: 280px;
        }

        .sticky-col {
            position: sticky;
            top: 1rem;
        }

        /* ----- ENCABEZADO DEL DASHBOARD ----- */
        .dashboard-header {
            text-align: center;
            margin-top: 1rem;
        }

        .dashboard-title {
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-weight: 600;
            font-size: 2.5rem;
            letter-spacing: 1px;
            color: #1a237e;
            /* Azul elegante */
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: .3rem;
            transition: all 0.3s ease;
        }

        .dashboard-title:hover {
            color: #0d47a1;
            transform: scale(1.02);
        }

        .dashboard-subtitle {
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-size: 1rem;
            color: #607d8b;
            letter-spacing: .5px;
        }

        /* --- Tarjetas de notificaciones --- */
        .notif-card {
            border-radius: 1rem;
            padding: 1rem;
            color: #212529;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .notif-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notif-title {
            font-size: 1rem;
            font-weight: 600;
        }

        .notif-badge {
            display: inline-block;
            padding: .25rem .6rem;
            border-radius: 0.5rem;
            color: #fff;
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .notif-sub {
            opacity: 0.9;
        }

        /* === Estilo Moderno de Tarjetas KPI === */
        .kpi-card {
            border: none;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.12);
        }

        .icon-wrapper {
            background: linear-gradient(135deg, #4dabf7, #228be6);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 0.5rem;
        }

        .icon-wrapper svg {
            width: 30px;
            height: 30px;
            fill: white;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <!-- TITULO -->
    <div class="dashboard-header text-center mb-4">
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Producción · Inventarios · Calidad · Logística</p>
    </div>


<?php
// Fallback de KPIs por si la API no responde. El JS los reemplaza con datos reales.
$cards = $kpis ?? [
        ['label' => 'Órdenes activas', 'value' => 18, 'icon' => 'bi-clipboard2-check', 'muted' => '+2 hoy', 'mutedClass' => 'text-success'],
        ['label' => 'WIP (pzs)', 'value' => 362, 'icon' => 'bi-gear-wide-connected', 'muted' => 'En proceso', 'mutedClass' => 'text-secondary'],
        ['label' => 'Defectos (%)', 'value' => '1.8%', 'icon' => 'bi-activity', 'muted' => '-0.4% vs. ayer', 'mutedClass' => 'text-success'],
        ['label' => 'Stock crítico', 'value' => 5, 'icon' => 'bi-box-seam', 'muted' => 'Por debajo de mínimo', 'mutedClass' => 'text-warning'],
];

// Notificaciones (fallback)
$notifCount = $notifCount ?? 3;
$recentNotifs = $recentNotifs ?? [
        ['nivel' => 'Crítica', 'color' => '#e03131', 'titulo' => 'Actualizar avance WIP en OP-2025-014', 'sub' => 'Atrasado 1 día • Confección (WIP)'],
        ['nivel' => 'Alta', 'color' => '#ffd43b', 'titulo' => 'Revisar muestra M-0045 del cliente A', 'sub' => 'Vence hoy • Prototipos'],
        ['nivel' => 'Media', 'color' => '#4dabf7', 'titulo' => 'OC #1045 recibida', 'sub' => 'Almacén PT • Entrada parcial'],
];
?>

    <div class="row g-4">
        <!-- Columna principal -->
        <div class="col-12 col-xxl-9">
            <!-- KPIs -->
            <div class="row g-3 mb-1 justify-content-center">
                <?php foreach ($cards as $i => $c):
                    $dataLabel = strtolower(preg_replace('/\s+/', '-', $c['label']));
                    ?>
                    <div class="col-6 col-md-3 col-xl-3">
                        <div class="card kpi-card p-3 text-center shadow-sm border-0">
                            <div class="icon-wrapper mx-auto mb-2">
                                <!-- Ícono SVG actual  -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="url(#grad1)"
                                     viewBox="0 0 16 16">
                                    <defs>
                                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#4dabf7"/>
                                            <stop offset="100%" stop-color="#228be6"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 0h1v15h15v1H0z"/>
                                    <path d="M2 13l4-5 3 3 5-7 1 1-6 8-3-3-3 4z"/>
                                </svg>
                            </div>

                            <div class="text-secondary small"><?= esc($c['label']) ?></div>

                            <!--  Color de UMEROOO-->
                            <div class="fs-3 fw-bold kpi-value" data-label="<?= esc($dataLabel, 'attr') ?>" style="
                                    color: <?=
                            strpos($c['label'], 'Activas') !== false ? '#1c7ed6' :
                                    (strpos($c['label'], 'WIP') !== false ? '#2f9e44' :
                                            (strpos($c['label'], 'Completadas') !== false ? '#0ca678' :
                                                    (strpos($c['label'], 'Incidencias') !== false ? '#e03131' : '#212529')))
                            ?>;
                                    ">
                                <?= esc($c['value']) ?>
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

            <script>
                const ctxLogistica = document.getElementById('chLogistica').getContext('2d');

                const chLogistica = new Chart(ctxLogistica, {
                    type: 'bar',
                    data: {
                        labels: ['Pendiente', 'En tránsito', 'Entregado', 'Cancelado'],
                        datasets: [{
                            label: 'Órdenes',
                            data: [12, 19, 8, 3],
                            backgroundColor: [
                                '#4dabf7',
                                '#ffd43b',
                                '#69db7c',
                                '#ffa8a8'
                            ],
                            borderRadius: 8,
                            borderWidth: 0,
                            hoverBackgroundColor: [
                                '#228be6',
                                '#fab005',
                                '#37b24d',
                                '#e03131'
                            ],
                            hoverBorderColor: '#fff',
                            hoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    color: '#495057',
                                    font: {size: 12, family: 'Poppins, sans-serif'}
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.7)',
                                titleFont: {size: 13, weight: 'bold'},
                                bodyFont: {size: 12},
                                padding: 10,
                                borderWidth: 1,
                                borderColor: '#dee2e6'
                            }
                        },
                        scales: {
                            x: {
                                ticks: {color: '#495057', font: {size: 12}},
                                grid: {display: false}
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {color: '#495057', font: {size: 12}},
                                grid: {color: 'rgba(0,0,0,0.05)'}
                            }
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: true,
                            onHover: (event, chartElement) => {
                                event.native.target.style.cursor = chartElement.length ? 'pointer' : 'default';
                            }
                        },
                        animation: {
                            duration: 400,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            </script>


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
                                <div class="notif-card mb-2"
                                     style="background: <?= esc($n['color'], 'attr') ?>20; border-left: 6px solid <?= esc($n['color'], 'attr') ?>;">
                                    <div class="notif-header d-flex justify-content-between align-items-center mb-1">
                                        <span class="notif-title fw-semibold text-dark"><?= esc($n['titulo']) ?></span>
                                        <span class="notif-badge"
                                              style="background: <?= esc($n['color'], 'attr') ?>;"><?= esc($n['nivel']) ?></span>
                                    </div>
                                    <div class="notif-sub text-dark small mb-2"><?= esc($n['sub']) ?></div>
                                    <div class="d-flex gap-2">
                                        <a href="#" class="btn btn-sm btn-light fw-semibold">Ver detalle</a>
                                        <a href="#" class="btn btn-sm btn-light fw-semibold">Completar</a>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </div>
                        <a href="<?= base_url('modulo3/notificaciones') ?>"
                           class="btn btn-sm btn-outline-primary w-100 mt-3">Ver todas</a>
                    </div>
                </div>

                <!-- Filtros compactos -->
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

<?php // --- Scripts: SIN datos de demo. Todo se carga desde la API. ?>
<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const API_URL = "<?= base_url('api/dashboard') ?>";
            const selRango = document.getElementById('selRango');
            const btnAplicar = document.getElementById('btnAplicarFiltros');

            let chProduccion = null, chInventario = null, chCalidad = null, chLogistica = null;

            function makeKey(label) {
                return (label || '').toLowerCase().replace(/\s+/g, '-');
            }

            function createOrUpdateChart(ref, ctx, cfg) {
                if (ref && typeof ref.destroy === 'function') ref.destroy();
                return new Chart(ctx, cfg);
            }

            function pintarKpis(kpis = []) {
                kpis.forEach(k => {
                    const key = makeKey(k.label);
                    document.querySelectorAll(`.kpi-value[data-label="${key}"]`).forEach(el => {
                        // si el label tiene '%' lo formateamos como porcentaje
                        const isPct = /%|\bdefecto/.test((k.label || '').toLowerCase());
                        el.textContent = isPct ? (Number(k.value).toFixed(1) + '%') : k.value;
                    });
                });
            }

            async function cargarDashboard(rangeDays = 30) {
                try {
                    const res = await fetch(`${API_URL}?range=${rangeDays}`, {headers: {'Accept': 'application/json'}});
                    if (!res.ok) {
                        console.error('API dashboard error:', res.status, await res.text());
                        return;
                    }
                    const d = await res.json();

                    // ===== KPIs =====
                    if (Array.isArray(d.kpis)) pintarKpis(d.kpis);

                    // ===== PRODUCCIÓN =====
                    chProduccion = createOrUpdateChart(
                        chProduccion,
                        document.getElementById('chProduccion'),
                        {
                            type: 'bar',
                            data: {
                                labels: d.produccion.labels,
                                datasets: [
                                    {label: 'Activas', data: d.produccion.activas, borderWidth: 1},
                                    {label: 'Completadas', data: d.produccion.completadas, borderWidth: 1}
                                ]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                scales: {
                                    x: {stacked: true},
                                    y: {stacked: true, beginAtZero: true, ticks: {precision: 0}}
                                },
                                plugins: {legend: {position: 'top'}, tooltip: {mode: 'index', intersect: false}}
                            }
                        }
                    );

                    // ===== INVENTARIO =====
                    chInventario = createOrUpdateChart(
                        chInventario,
                        document.getElementById('chInventario'),
                        {
                            data: {
                                labels: d.inventario.labels,
                                datasets: [
                                    {type: 'bar', label: 'Stock actual', data: d.inventario.actual, borderWidth: 1},
                                    {
                                        type: 'line',
                                        label: 'Stock mínimo',
                                        data: d.inventario.min,
                                        borderWidth: 2,
                                        fill: false
                                    },
                                    {
                                        type: 'line',
                                        label: 'Stock máximo',
                                        data: d.inventario.max,
                                        borderWidth: 2,
                                        fill: false
                                    }
                                ]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                scales: {y: {beginAtZero: true}},
                                plugins: {legend: {position: 'top'}}
                            }
                        }
                    );

                    // ===== CALIDAD =====
                    chCalidad = createOrUpdateChart(
                        chCalidad,
                        document.getElementById('chCalidad'),
                        {
                            type: 'line',
                            data: {
                                labels: d.calidad.labels,
                                datasets: [{
                                    label: '% Defectos',
                                    data: d.calidad.tasa,
                                    tension: .3,
                                    borderWidth: 2,
                                    pointRadius: 0
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                scales: {y: {beginAtZero: true}},
                                plugins: {
                                    legend: {position: 'top'},
                                    tooltip: {callbacks: {label: ctx => ` ${ctx.parsed.y}%`}}
                                }
                            }
                        }
                    );

                    // ===== LOGÍSTICA =====
                    chLogistica = createOrUpdateChart(
                        chLogistica,
                        document.getElementById('chLogistica'),
                        {
                            type: 'doughnut',
                            data: {labels: d.logistica.labels, datasets: [{data: d.logistica.data}]},
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {legend: {position: 'right'}},
                                cutout: '55%'
                            }
                        }
                    );

                } catch (err) {
                    console.error('Error cargando dashboard:', err);
                }
            }

            btnAplicar?.addEventListener('click', () => {
                const rango = parseInt(selRango.value || '30', 10);
                cargarDashboard(rango);
            });

            // Primera carga
            cargarDashboard(parseInt(selRango?.value || '30', 10));
        });
    </script>
<?= $this->endSection() ?>