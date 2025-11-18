<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .letterhead {
        border: 2px solid #2c3e50;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 25px;
        background-color: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up me-2"></i>
                        Reporte de Eficiencia
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-calendar me-2"></i>
                        Reporte Mensual
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock me-2"></i>
                        Tiempos de Producción
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
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-check-circle me-2"></i>
                        Control de Calidad
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Defectos y Rechazos
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-award me-2"></i>
                        Certificaciones
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Reportes Financieros</strong>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-currency-dollar me-2"></i>
                        Costos de Producción
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-pie-chart me-2"></i>
                        Análisis de Rentabilidad
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Exportar Datos</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-excel me-2"></i>
                        Exportar a Excel
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Generar PDF
                    </button>
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
                <!-- Aquí se cargará el contenido del reporte -->
                <div class="letterhead">
                    <div class="text-center mb-3">
                        <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" style="max-height: 60px; margin-bottom: 10px;" onerror="this.style.display='none'">
                    </div>
                    <h3 id="maquiladoraNombre"><?= strtoupper(session()->get('maquiladora_nombre') ?? 'MAQUILADORA') ?></h3>
                    <div class="maquiladora-info">
                        <div id="maquiladoraDomicilio" class="mb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i><?= session()->get('maquiladora_domicilio') ?? 'Dirección no especificada' ?>
                        </div>
                        <div class="mb-2">
                            <?php if(session()->get('maquiladora_telefono')): ?>
                                <span id="maquiladoraTelefono" class="me-3">
                                    <i class="bi bi-telephone-fill me-1"></i> <?= session()->get('maquiladora_telefono') ?>
                                </span>
                            <?php endif; ?>
                            <?php if(session()->get('maquiladora_correo')): ?>
                                <span id="maquiladoraCorreo">
                                    <i class="bi bi-envelope-fill me-1"></i> <?= session()->get('maquiladora_correo') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if(session()->get('maquiladora_dueno')): ?>
                            <div id="maquiladoraRepresentante" class="mt-2 fw-medium">
                                <i class="bi bi-person-badge-fill me-2"></i>Representante: <?= session()->get('maquiladora_dueno') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="report-title" id="reportTitle">TÍTULO DEL REPORTE</div>
                    <div class="report-details d-flex justify-content-between align-items-center bg-light p-2 rounded">
                        <div class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i> <?= strtoupper(ucfirst(utf8_encode(strftime('%A %d de %B de %Y', strtotime(date('Y-m-d')))))) ?>
                        </div>
                        <div class="text-muted">
                            <i class="bi bi-person-fill me-1"></i> <?= session()->get('username') ?? 'Usuario' ?>
                        </div>
                    </div>
                </div>
                <div id="reportBody">
                    <!-- Contenido específico del reporte se cargará aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnImprimirReporte">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
                <button type="button" class="btn btn-success" id="btnDescargarPDF">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener todos los enlaces de reportes
        const reportLinks = document.querySelectorAll('.list-group-item-action');
        
        // Agregar manejador de eventos a cada enlace
        reportLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const reportTitle = this.textContent.trim();
                const reportType = this.closest('.card')?.querySelector('.card-header strong')?.textContent || 'Reporte';
                
                // Actualizar el título del reporte en el modal
                document.getElementById('reportTitle').textContent = reportTitle;
                
                // Aquí podrías cargar contenido específico según el tipo de reporte
                const reportBody = document.getElementById('reportBody');
                reportBody.innerHTML = `
                    <div class="alert alert-info">
                        <h5>${reportType}: ${reportTitle}</h5>
                        <p>Este es un ejemplo del contenido del reporte. Aquí se mostrarían los datos específicos.</p>
                        <p>Fecha de generación: ${new Date().toLocaleString()}</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Dato de Ejemplo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Total de registros</td>
                                    <td>125</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Promedio</td>
                                    <td>85.5%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                
                // Mostrar el modal
                const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
                reportModal.show();
            });
        });
        
        // Manejar el botón de impresión
        document.getElementById('btnImprimirReporte').addEventListener('click', function() {
            window.print();
        });
        
        // Manejar el botón de descarga PDF (ejemplo básico)
        document.getElementById('btnDescargarPDF').addEventListener('click', function() {
            alert('Funcionalidad de descarga de PDF será implementada próximamente.');
            // Aquí iría la lógica para generar el PDF usando una librería como jsPDF
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>