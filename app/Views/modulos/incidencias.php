<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Incidencias</h1>
    <span class="badge bg-danger">Reportes</span>
</div>

<?php if (session()->getFlashdata('ok')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('ok')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<!-- Card: Historial (Tabla) -->
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong>Historial de Incidencias</strong>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#incidenciaModal">
            <i class="bi bi-plus-lg me-1"></i> Agregar
        </button>
    </div>

    <div class="card-body table-responsive">
        <?php
        // $lista viene del controlador con alias: Ide, OP, Tipo, Fecha, Descripcion
        $rows    = (isset($lista) && is_array($lista)) ? $lista : [];
        $columns = ['Fecha','OP','Tipo','Descripción','Acciones'];
        $tableId = 'tablaIncidencias';
        ?>
        <table id="<?= esc($tableId) ?>" class="table table-striped table-bordered align-middle">
            <thead class="table-primary text-center">
            <tr>
                <?php foreach ($columns as $c): ?>
                    <th><?= esc($c) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $i): ?>
                    <tr>
                        <td class="text-center"><?= esc($i['Fecha'] ?? '') ?></td>
                        <td class="text-center"><?= esc($i['OP'] ?? '') ?></td>
                        <td class="text-center"><?= esc($i['Tipo'] ?? '') ?></td>
                        <td class="text-start"><?= esc($i['Descripcion'] ?? '') ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" type="button" disabled>
                                <i class="bi bi-eye"></i> Ver
                            </button>
                            <a class="btn btn-sm btn-outline-danger"
                               href="<?= site_url('modulo3/incidencias/eliminar/' . (int)($i['Ide'] ?? 0)) ?>"
                               onclick="return confirm('¿Eliminar la incidencia?');">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay incidencias registradas.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Agregar Incidencia -->
<div class="modal fade" id="incidenciaModal" tabindex="-1" aria-labelledby="incidenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" action="<?= site_url('modulo3/incidencias/crear') ?>" method="post">
            <?= csrf_field() ?>

            <div class="modal-header">
                <h5 class="modal-title" id="incidenciaModalLabel">
                    <i class="bi bi-clipboard-plus me-2"></i>Nuevo Reporte de Incidencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">OP</label>
                        <input name="op" class="form-control" placeholder="OP-0001" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">-- Selecciona --</option>
                            <option>Paro de máquina</option>
                            <option>Falta de material</option>
                            <option>Calidad</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe la incidencia..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger" type="submit">
                    <i class="bi bi-send me-1"></i> Reportar
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Inicialización DataTables (usa tu helper global si existe) -->
<script>
    (function() {
        const tableSel = '#<?= esc($tableId) ?>';
        if (typeof initDataTableEs === 'function') {
            // La columna de acciones (índice 4) no es ordenable
            initDataTableEs(tableSel, [4]);
        } else if (window.jQuery && $.fn.dataTable) {
            // Fallback simple si no tienes el helper centralizado
            $(tableSel).DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                columnDefs: [
                    { targets: [4], orderable: false }
                ],
                pageLength: 10,
                order: [[0, 'desc']]
            });
        }
    })();
</script>

<?= $this->endSection() ?>
