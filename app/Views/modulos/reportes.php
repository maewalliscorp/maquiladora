<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .letterhead {
        border: 2px solid #2c3e50;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 25px;
        background-color: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .letterhead h3 {
        color: #2c3e50;
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 10px 0;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .letterhead .maquiladora-info {
        text-align: center;
        margin: 15px 0;
        font-size: 13px;
        line-height: 1.6;
        color: #495057;
    }

    .report-title {
        text-align: center;
        font-size: 20px;
        font-weight: 600;
        margin: 20px 0;
        text-transform: uppercase;
        color: #2c3e50;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 4px;
    }

    .report-details {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    #chartContainer {
        position: relative;
        height: 400px;
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Reportes</h1>
    <span class="badge bg-primary">Módulo 3</span>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Reportes de Producción</strong>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action" data-report="eficiencia">
                        <i class="bi bi-graph-up me-2"></i>
                        Reporte de Eficiencia
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-report="mensual">
                        <i class="bi bi-calendar me-2"></i>
                        Reporte Mensual
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Reportes de Calidad</strong>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action" data-report="calidad">
                        <i class="bi bi-check-circle me-2"></i>
                        Control de Calidad
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualización de reportes -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Vista Previa del Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reportContent">
                <!-- Encabezado del reporte -->
                <div class="letterhead">
                    <div class="text-center mb-3">
                        <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo"
                            style="max-height: 60px; margin-bottom: 10px;" onerror="this.style.display='none'">
                    </div>
                    <h3 id="maquiladoraNombre"><?= strtoupper(session()->get('maquiladora_nombre') ?? 'MAQUILADORA') ?>
                    </h3>
                    <div class="maquiladora-info">
                        <div id="maquiladoraDomicilio" class="mb-2">
                            <i
                                class="bi bi-geo-alt-fill me-2"></i><?= session()->get('maquiladora_domicilio') ?? 'Dirección no especificada' ?>
                        </div>
                        <div class="mb-2">
                            <?php if (session()->get('maquiladora_telefono')): ?>
                                <span id="maquiladoraTelefono" class="me-3">
                                    <i class="bi bi-telephone-fill me-1"></i> <?= session()->get('maquiladora_telefono') ?>
                                </span>
                            <?php endif; ?>
                            <?php if (session()->get('maquiladora_correo')): ?>
                                <span id="maquiladoraCorreo">
                                    <i class="bi bi-envelope-fill me-1"></i> <?= session()->get('maquiladora_correo') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (session()->get('maquiladora_dueno')): ?>
                            <div id="maquiladoraRepresentante" class="mt-2 fw-medium">
                                <i class="bi bi-person-badge-fill me-2"></i>Representante:
                                <?= session()->get('maquiladora_dueno') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="report-title" id="reportTitle">TÍTULO DEL REPORTE</div>
                    <div class="report-details d-flex justify-content-between align-items-center bg-light p-2 rounded">
                        <div class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= strtoupper(ucfirst(utf8_encode(strftime('%A %d de %B de %Y', strtotime(date('Y-m-d')))))) ?>
                        </div>
                        <div class="text-muted">
                            <i class="bi bi-person-fill me-1"></i> <?= session()->get('username') ?? 'Usuario' ?>
                        </div>
                    </div>
                </div>

                <!-- Cuerpo del reporte -->
                <div id="reportBody">
                    <div id="chartContainer">
                        <canvas id="reportChart"></canvas>
                    </div>
                    <div id="reportTableContainer" class="mt-4 table-responsive">
                        <!-- Tabla dinámica aquí -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnImprimirReporte">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
                <button type="button" class="btn btn-success" id="btnExportarCSV">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    let currentChart = null;
    let currentReportType = null;

    document.addEventListener('DOMContentLoaded', function () {
        const reportLinks = document.querySelectorAll('.list-group-item-action[data-report]');

        reportLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const reportType = this.getAttribute('data-report');
                const reportTitle = this.textContent.trim();

                currentReportType = reportType;
                document.getElementById('reportTitle').textContent = reportTitle;

                // Limpiar contenido previo
                document.getElementById('reportTableContainer').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
                if (currentChart) {
                    currentChart.destroy();
                    currentChart = null;
                }

                const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
                reportModal.show();

                // Fetch data
                fetch(`<?= base_url('modulo3/reportes/api') ?>/${reportType}`)
                    .then(response => response.json())
                    .then(data => {
                        renderReport(reportType, data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('reportTableContainer').innerHTML = '<div class="alert alert-danger">Error al cargar los datos del reporte.</div>';
                    });
            });
        });

        document.getElementById('btnImprimirReporte').addEventListener('click', function () {
            window.print();
        });

        document.getElementById('btnExportarCSV').addEventListener('click', function () {
            if (currentReportType) {
                window.location.href = `<?= base_url('modulo3/reportes/exportar') ?>/${currentReportType}`;
            }
        });
    });

    function renderReport(type, data) {
        const ctx = document.getElementById('reportChart').getContext('2d');
        let chartConfig = {};

        // Limpiar tabla
        const tableContainer = document.getElementById('reportTableContainer');
        tableContainer.innerHTML = '';

        if (type === 'eficiencia') {
            chartConfig = {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Producción Planeada vs Real' }
                    }
                }
            };
            generateTable(data.labels, data.datasets, tableContainer);
        } else if (type === 'mensual') {
            chartConfig = {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Producción Mensual' }
                    }
                }
            };
            generateTable(data.labels, data.datasets, tableContainer);
        } else if (type === 'calidad') {
            chartConfig = {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Control de Calidad' }
                    }
                }
            };
            generateTable(data.labels, data.datasets, tableContainer);
        }

        if (currentChart) currentChart.destroy();
        currentChart = new Chart(ctx, chartConfig);
    }

    function generateTable(labels, datasets, container) {
        let html = '<table class="table table-bordered table-striped table-sm">';
        html += '<thead class="table-dark"><tr><th>Concepto</th>';
        datasets.forEach(ds => {
            html += `<th>${ds.label || 'Valor'}</th>`;
        });
        html += '</tr></thead><tbody>';

        labels.forEach((label, index) => {
            html += `<tr><td>${label}</td>`;
            datasets.forEach(ds => {
                html += `<td>${ds.data[index]}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>