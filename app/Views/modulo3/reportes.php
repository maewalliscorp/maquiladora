<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Reportes</h1>
    <span class="badge bg-secondary">Exportación</span>
</div>

<div class="card shadow-sm">
    <div class="card-header"><strong>Generar Reporte</strong></div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select class="form-select">
                    <option>Órdenes</option>
                    <option>WIP</option>
                    <option>Incidencias</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Generar PDF</button>
                <button class="btn btn-outline-primary">Exportar Excel</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
