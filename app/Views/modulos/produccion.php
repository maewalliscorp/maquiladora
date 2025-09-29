<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Iniciar Producción de Pedidos</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>

    <!-- Sección de búsqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="mb-3">Buscar pedidos pendientes</h4>

            <ul class="nav nav-tabs mb-3" id="searchTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="code-tab" data-bs-toggle="tab" data-bs-target="#code" type="button" role="tab">
                        <i class="bi bi-upc-scan me-1"></i> Buscar por código
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="camera-tab" data-bs-toggle="tab" data-bs-target="#camera" type="button" role="tab">
                        <i class="bi bi-camera me-1"></i> Escanear código QR
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">
                        <i class="bi bi-list-ul me-1"></i> Ver todos los pendientes
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="searchTabsContent">
                <!-- Búsqueda por código -->
                <div class="tab-pane fade show active" id="code" role="tabpanel">
                    <form id="searchByCodeForm" class="p-3 border rounded bg-light">
                        <div class="mb-3">
                            <label for="orderCode" class="form-label">Código del pedido</label>
                            <input type="text" class="form-control" id="orderCode" placeholder="Ingresa el código del pedido">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Buscar pedido
                        </button>
                    </form>
                </div>

                <!-- Escaneo por cámara -->
                <div class="tab-pane fade" id="camera" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="camera-preview border p-4 rounded text-center mb-3" id="cameraPreview">
                            <i class="bi bi-camera" style="font-size: 2rem;"></i>
                            <p class="mt-2">Haz clic para activar la cámara</p>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <button class="btn btn-outline-secondary" id="toggleCamera">
                                <i class="bi bi-camera-video"></i> Activar/Desactivar
                            </button>
                            <button class="btn btn-outline-primary" id="captureImage">
                                <i class="bi bi-camera-fill"></i> Capturar
                            </button>
                        </div>
                        <div>
                            <p class="text-muted small">O sube una imagen del código QR</p>
                            <input type="file" class="form-control" id="uploadQR" accept="image/*">
                        </div>
                    </div>
                </div>

                <!-- Lista de todos los pendientes -->
                <div class="tab-pane fade" id="list" role="tabpanel">
                    <div class="p-3 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Todos los pedidos pendientes</h5>
                            <span class="badge bg-secondary" id="pendingCount">0 pendientes</span>
                        </div>
                        <div id="pendingOrdersList">
                            <!-- Los pedidos se cargarán aquí dinámicamente -->
                        </div>
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
