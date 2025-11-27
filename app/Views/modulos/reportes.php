<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .report-title {
        text-align: center;
        font-size: 20px;
        font-weight: 600;
        margin: 0 0 20px 0;
        text-transform: uppercase;
        color: #2c3e50;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 4px;
    }

    #chartContainer {
        position: relative;
        height: 400px;
        width: 100%;
    }
    
    /* Estilos para los botones de la lista de reportes */
    .list-group-item-action {
        font-size: 1.25rem; /* Letra más grande */
        color: #000 !important; /* Texto negro */
        padding: 1.2rem 1.5rem; /* Botones más grandes */
        font-weight: 500;
    }

    .list-group-item-action i {
        font-size: 1.4rem;
        margin-right: 10px;
    }

    .list-group-item-action.active {
        background-color: #667eea;
        border-color: #667eea;
        color: #000 !important; /* Texto negro al estar activo */
        font-weight: 600;
    }

    @media print {
        /* Ocultar todo lo que no sea el contenido principal */
        body * {
            visibility: hidden;
        }
        
        /* Hacer visible solo el área del reporte y sus hijos */
        #reportContent, #reportContent * {
            visibility: visible;
        }

        /* Posicionar el reporte al inicio de la página */
        #reportContent {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Ajustes para gráficas en impresión */
        #chartContainer {
            width: 60% !important;
            margin: 0 auto !important;
            height: auto !important;
        }
        canvas {
            max-width: 100% !important;
            height: auto !important;
            max-height: none !important;
        }

        /* Forzar visualización del membrete */
        .d-print-block {
            display: block !important;
        }
        
        /* Ocultar elementos de UI explícitamente */
        .d-print-none {
            display: none !important;
        }
        
        /* Quitar bordes y sombras de tarjetas */
        .card, .shadow-sm {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4 d-print-none">
    <h1 class="me-3">Reportes</h1>
    <span class="badge bg-primary">Módulo 3</span>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Opciones de Reportes (Oculto al imprimir) -->
    <div class="col-md-4 d-print-none">
        <!-- Reportes de Producción -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-dark">
                <strong><i class="bi bi-graph-up me-2"></i>Reportes de Producción</strong>
            </div>
            <div class="card-body p-0">
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

        <!-- Reportes de Calidad -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-dark">
                <strong><i class="bi bi-check-circle me-2"></i>Reportes de Calidad</strong>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action" data-report="calidad">
                        <i class="bi bi-check-circle me-2"></i>
                        Control de Calidad
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Preview del Reporte -->
    <div class="col-md-8 print-w-100">
        <div class="card shadow-sm h-100 border-0-print">
            <div class="card-header bg-light d-flex justify-content-between align-items-center d-print-none">
                <strong><i class="bi bi-file-earmark-text me-2"></i>Vista Previa del Reporte</strong>
                <div>
                    <button type="button" class="btn btn-sm btn-primary" id="btnImprimirReporte" disabled>
                        <i class="bi bi-printer me-1"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="btnExportarCSV" disabled>
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
                    </button>
                </div>
            </div>
            <div class="card-body" id="reportContent">
                <!-- Estado inicial: Sin reporte seleccionado (Oculto al imprimir) -->
                <div id="emptyState" class="text-center py-5 d-print-none">
                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">Selecciona un reporte</h5>
                    <p class="text-muted">Elige una opción del menú de la izquierda para ver el reporte</p>
                </div>

                <!-- Contenido del reporte (oculto inicialmente) -->
                <div id="reportPreview" style="display: none;">
                    
                    <!-- Membrete SOLO para impresión -->
                    <div class="d-none d-print-block mb-4 text-center">
                        <?php if (!empty($maquiladora['logo_base64'])): ?>
                            <img src="data:image/jpeg;base64,<?= $maquiladora['logo_base64'] ?>" alt="Logo" style="max-height: 80px; margin-bottom: 15px;">
                        <?php endif; ?>
                        <h2 class="fw-bold mb-1"><?= strtoupper($maquiladora['Nombre_Maquila'] ?? 'MAQUILADORA') ?></h2>
                        <p class="mb-0 text-muted"><?= $maquiladora['Domicilio'] ?? 'Dirección no especificada' ?></p>
                        <div class="small text-muted mb-3">
                            <?php if (!empty($maquiladora['Telefono'])): ?>
                                <span class="me-3"><i class="bi bi-telephone-fill"></i> <?= $maquiladora['Telefono'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($maquiladora['Correo'])): ?>
                                <span><i class="bi bi-envelope-fill"></i> <?= $maquiladora['Correo'] ?></span>
                            <?php endif; ?>
                        </div>
                        <hr>
                    </div>

                    <!-- Título del reporte -->
                    <div class="report-title" id="reportTitle">TÍTULO DEL REPORTE</div>
                    
                    <!-- Fecha y Usuario (Visible en impresión para contexto) -->
                    <div class="d-flex justify-content-between text-muted small mb-3 d-none d-print-flex">
                        <span><?= date('d/m/Y H:i') ?></span>
                        <span>Generado por: <?= session()->get('username') ?></span>
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
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let currentChart = null;
    let currentReportType = null;

    document.addEventListener('DOMContentLoaded', function () {
        const reportLinks = document.querySelectorAll('.list-group-item-action[data-report]');
        const emptyState = document.getElementById('emptyState');
        const reportPreview = document.getElementById('reportPreview');
        const btnImprimir = document.getElementById('btnImprimirReporte');
        const btnExportar = document.getElementById('btnExportarCSV');

        reportLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                
                // Remover clase active de todos los links
                reportLinks.forEach(l => l.classList.remove('active'));
                // Agregar clase active al link clickeado
                this.classList.add('active');
                
                const reportType = this.getAttribute('data-report');
                const reportTitle = this.textContent.trim();

                currentReportType = reportType;
                document.getElementById('reportTitle').textContent = reportTitle;

                // Mostrar preview y ocultar empty state
                emptyState.style.display = 'none';
                reportPreview.style.display = 'block';
                
                // Habilitar botones
                btnImprimir.disabled = false;
                btnExportar.disabled = false;

                // Limpiar contenido previo
                document.getElementById('reportTableContainer').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
                if (currentChart) {
                    currentChart.destroy();
                    currentChart = null;
                }

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

        btnImprimir.addEventListener('click', function () {
            window.print();
        });

        btnExportar.addEventListener('click', function () {
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