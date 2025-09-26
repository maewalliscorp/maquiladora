<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Módulo 3 - Producción</h1>
    <span class="badge bg-primary">Maquetación</span>
</div>

<!-- Card: Órdenes de Producción -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Órdenes de Producción</h5>
    </div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Orden de Producción</label>
                <input type="text" class="form-control" placeholder="OP-0001">
            </div>
            <div class="col-md-6">
                <label class="form-label">Responsable</label>
                <input type="text" class="form-control" placeholder="Nombre del encargado">
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha de Inicio</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha Estimada de Fin</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
            </div>
        </form>
    </div>
</div>

<!-- Card: Avance de Producción -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Avance de Producción (WIP)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-primary">
                <tr>
                    <th>Etapa</th>
                    <th>Responsable</th>
                    <th>Inicio</th>
                    <th>Fin Estimado</th>
                    <th>Progreso</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Corte</td>
                    <td>Juan Pérez</td>
                    <td>2025-09-20</td>
                    <td>2025-09-22</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 80%">80%</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Confección</td>
                    <td>María López</td>
                    <td>2025-09-22</td>
                    <td>2025-09-25</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 45%">45%</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Acabado</td>
                    <td>Carlos Ruiz</td>
                    <td>2025-09-25</td>
                    <td>2025-09-27</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar bg-secondary" style="width: 10%">10%</div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Card: Reportes de Incidencias -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">Reportes de Incidencias</h5>
    </div>
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Tipo de Incidencia</label>
                <select class="form-select">
                    <option>Paro de máquina</option>
                    <option>Falta de material</option>
                    <option>Calidad</option>
                    <option>Otro</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" rows="3" placeholder="Describe la incidencia..."></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-danger">Reportar</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
