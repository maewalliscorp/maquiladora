<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Iniciar Producción de Pedidos</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>

    <!-- Secciones con pestañas: Pendientes / Finalizados -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="prodTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-pend" data-bs-toggle="tab" data-bs-target="#pane-pend" type="button" role="tab">
                        <i class="bi bi-list-ul me-1"></i> Pendientes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-done" data-bs-toggle="tab" data-bs-target="#pane-done" type="button" role="tab">
                        <i class="bi bi-check2-circle me-1"></i> Finalizados
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="prodTabsContent">
                <div class="tab-pane fade show active" id="pane-pend" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Todos los pedidos pendientes</h5>
                            <span class="badge bg-secondary" id="pendingCount">0 pendientes</span>
                        </div>
                        <div id="pendingOrdersList"></div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pane-done" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Pedidos finalizados</h5>
                            <span class="badge bg-success" id="completedCount">0</span>
                        </div>
                        <div id="completedOrdersList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div id="searchResults" class="mt-4"></div>

    
</div>

<!-- Modal para confirmar inicio de producción -->
<div class="modal fade" id="startProductionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Iniciar producción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas iniciar la producción del pedido <strong id="modalOrderCode"></strong>?</p>
                <div class="mb-3">
                    <label for="productionNotes" class="form-label">Notas iniciales (opcional)</label>
                    <textarea class="form-control" id="productionNotes" rows="2" placeholder="Agregar notas sobre el inicio de producción..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmStartProduction">
                    <i class="bi bi-play-circle me-1"></i> Iniciar producción
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function(){
        const empId = <?= json_encode($empleadoId ?? null) ?>;
        const base = '<?= base_url('modulo1/produccion/tareas') ?>';
        const url = base + (empId ? ('?empleadoId=' + encodeURIComponent(empId)) : '') + (empId ? '&' : '?') + 't=' + Date.now();
        const $pendList = document.getElementById('pendingOrdersList');
        const $pendCount = document.getElementById('pendingCount');
        const $doneList = document.getElementById('completedOrdersList');
        const $doneCount = document.getElementById('completedCount');
        if ($pendList) { $pendList.innerHTML = '<div class="text-muted">Cargando pendientes...</div>'; }
        fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const items = Array.isArray(data.items) ? data.items : [];
                const renderCard = (it) => {
                    const folio = it.folio || '-';
                    const status = it.status || '-';
                    const desde = it.asignadoDesde || '-';
                    const hasta = it.asignadoHasta || '-';
                    return (
                        '<div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-center">'
                        + '<div>'
                        + '<div class="fw-semibold">OP ' + folio + '</div>'
                        + '<div class="text-muted small">Desde: ' + desde + ' · Hasta: ' + hasta + '</div>'
                        + '</div>'
                        + '<span class="badge bg-info text-dark">' + status + '</span>'
                        + '</div>'
                    );
                };

                if ($pendList && $pendCount) {
                    const pending = items.filter(it => String(it.status || '').toLowerCase() !== 'completada');
                    $pendCount.textContent = pending.length + ' pendientes';
                    $pendList.innerHTML = pending.length ? pending.map(renderCard).join('') : '<div class="text-muted">No hay pedidos pendientes.</div>';
                }

                if ($doneList && $doneCount) {
                    const done = items.filter(it => String(it.status || '').toLowerCase() === 'completada');
                    $doneCount.textContent = done.length;
                    $doneList.innerHTML = done.length ? done.map(renderCard).join('') : '<div class="text-muted">No hay pedidos finalizados.</div>';
                }
            })
            .catch(() => {
                if ($pendList) $pendList.innerHTML = '<div class="text-danger">No se pudieron cargar los pendientes.</div>';
            });
    })();
    </script>
<?= $this->endSection() ?>
