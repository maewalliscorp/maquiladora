<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --warning-color: #f72585;
        --bg-color: #f8f9fa;
        --card-bg: #ffffff;
    }

    body {
        background-color: var(--bg-color);
        font-family: 'Inter', sans-serif;
    }

    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-weight: 700;
        color: #2b2d42;
    }

    .kpi-card {
        background: var(--card-bg);
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
        overflow: hidden;
        height: 100%;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2b2d42;
        margin-bottom: 0.25rem;
    }

    .kpi-label {
        color: #8d99ae;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-card {
        background: var(--card-bg);
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2b2d42;
        margin-bottom: 1.5rem;
    }

    .notif-item {
        padding: 1rem;
        border-bottom: 1px solid #edf2f4;
        display: flex;
        align-items: start;
        gap: 1rem;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="dashboard-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="dashboard-title h3">Dashboard General</h1>
            <p class="text-muted mb-0">Bienvenido de nuevo, <?= esc($userName) ?></p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Reporte
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-4 mb-4">
        <!-- Órdenes Activas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Órdenes Activas</div>
                        <div class="kpi-value"><?= esc($kpis['ordenes_activas']) ?></div>
                        <div class="text-success small"><i class="bi bi-arrow-up-short"></i> En proceso</div>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- WIP -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">WIP (Piezas)</div>
                        <div class="kpi-value"><?= number_format($kpis['wip_cantidad']) ?></div>
                        <div class="text-muted small">En planta</div>
                    </div>
                    <div class="kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-gear"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calidad -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Tasa Defectos</div>
                        <div class="kpi-value"><?= esc($kpis['tasa_defectos']) ?>%</div>
                        <div class="text-<?= $kpis['tasa_defectos'] > 5 ? 'danger' : 'success' ?> small">
                            <?= $kpis['tasa_defectos'] > 5 ? 'Atención requerida' : 'Bajo control' ?>
                        </div>
                    </div>
                    <div
                        class="kpi-icon bg-<?= $kpis['tasa_defectos'] > 5 ? 'danger' : 'success' ?> bg-opacity-10 text-<?= $kpis['tasa_defectos'] > 5 ? 'danger' : 'success' ?>">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Crítico -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Stock Crítico</div>
                        <div class="kpi-value"><?= esc($kpis['stock_critico']) ?></div>
                        <div class="text-<?= $kpis['stock_critico'] > 0 ? 'warning' : 'muted' ?> small">Artículos</div>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="chart-card">
                <h5 class="chart-title">Producción Semanal (Plan vs Real)</h5>
                <div style="height: 300px;">
                    <canvas id="productionChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="chart-card">
                <h5 class="chart-title">Notificaciones Recientes</h5>
                <div class="vstack gap-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-4 text-muted">No hay notificaciones nuevas</div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notif-item">
                                <div class="notif-icon"
                                    style="background-color: <?= esc($notif['color']) ?>20; color: <?= esc($notif['color']) ?>">
                                    <i class="bi bi-bell"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small"><?= esc($notif['titulo']) ?></div>
                                    <div class="text-muted small"><?= esc($notif['sub'] ?: $notif['mensaje']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('d M H:i', strtotime($notif['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <h5 class="chart-title">Tendencia de Calidad (% Defectos)</h5>
                <div style="height: 250px;">
                    <canvas id="qualityChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <h5 class="chart-title">Inventario (Top 5 Stock)</h5>
                <div style="height: 250px;">
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos inyectados desde el controlador
    const productionData = <?= json_encode($charts['produccion']) ?>;
    const qualityData = <?= json_encode($charts['calidad']) ?>;
    const inventoryData = <?= json_encode($charts['inventario']) ?>;

    // Configuración común
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';

    // Gráfico de Producción
    new Chart(document.getElementById('productionChart'), {
        type: 'bar',
        data: productionData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Gráfico de Calidad
    new Chart(document.getElementById('qualityChart'), {
        type: 'line',
        data: qualityData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Gráfico de Inventario
    new Chart(document.getElementById('inventoryChart'), {
        type: 'bar', // Cambiado a bar vertical para mejor ajuste o horizontal
        indexAxis: 'y',
        data: inventoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                y: { grid: { display: false } }
            }
        }
    });
</script>
<?= $this->endSection() ?>