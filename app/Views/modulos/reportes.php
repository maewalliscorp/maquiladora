<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Reportes</h1>
    <span class="badge bg-primary">Módulo 3</span>
</div>

<!-- Menú del Módulo 3 -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-2">
                <a href="<?= base_url('modulo3') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/ordenes') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-clipboard-data me-1"></i>Órdenes
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/wip') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-gear me-1"></i>WIP
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/incidencias') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-exclamation-triangle me-1"></i>Incidencias
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/reportes') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-graph-up me-1"></i>Reportes
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('modulo3/notificaciones') ?>" class="btn w-100 btn-outline-primary">
                    <i class="bi bi-bell me-1"></i>Notificaciones
                </a>
            </div>
        </div>
    </div>
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
<?= $this->endSection() ?>