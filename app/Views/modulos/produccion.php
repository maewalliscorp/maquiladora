<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <style>
        .search-section {
            background-color: var(--color-primary);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .search-tabs .nav-link {
            color: var(--color-text);
            font-weight: 500;
        }
        .search-tabs .nav-link.active {
            background-color: var(--color-primary-700);
            color: white;
            border: none;
        }
        .search-tabs .nav-link:not(.active):hover {
            background-color: var(--color-primary-600);
            color: white;
        }
        .search-box {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-primary-custom {
            background-color: var(--color-primary-700);
            border: none;
            font-weight: bold;
        }
        .btn-primary-custom:hover {
            background-color: var(--color-primary-600);
        }
        .btn-success-custom {
            background-color: #28a745;
            border: none;
            font-weight: bold;
        }
        .btn-success-custom:hover {
            background-color: #218838;
        }
        .order-card {
            border-left: 4px solid var(--color-primary-700);
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .order-status {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 12px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-in-progress {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .camera-preview {
            width: 100%;
            height: 200px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .camera-preview img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 6px;
        }
        .qr-scanner-container {
            position: relative;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
        .qr-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 2px solid var(--color-primary-700);
            border-radius: 8px;
            pointer-events: none;
        }
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .no-orders i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Iniciar Producción de Pedidos</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>

    <!-- Sección de búsqueda -->
    <div class="search-section">
        <h4 class="mb-3">Buscar pedidos pendientes</h4>

        <ul class="nav nav-tabs search-tabs mb-3" id="searchTabs" role="tablist">
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
                <div class="search-box">
                    <form id="searchByCodeForm">
                        <div class="mb-3">
                            <label for="orderCode" class="form-label">Código del pedido</label>
                            <input type="text" class="form-control" id="orderCode" placeholder="Ingresa el código del pedido">
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-search me-1"></i> Buscar pedido
                        </button>
                    </form>
                </div>
            </div>

            <!-- Escaneo por cámara -->
            <div class="tab-pane fade" id="camera" role="tabpanel">
                <div class="search-box">
                    <div class="camera-preview" id="cameraPreview">
                        <div class="text-center">
                            <i class="bi bi-camera" style="font-size: 2rem;"></i>
                            <p class="mt-2">Haz clic para activar la cámara</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" id="toggleCamera">
                            <i class="bi bi-camera-video"></i> Activar/Desactivar
                        </button>
                        <button class="btn btn-outline-primary" id="captureImage">
                            <i class="bi bi-camera-fill"></i> Capturar
                        </button>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted small">O sube una imagen del código QR</p>
                        <input type="file" class="form-control" id="uploadQR" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- Lista de todos los pendientes -->
            <div class="tab-pane fade" id="list" role="tabpanel">
                <div class="search-box">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Todos los pedidos pendientes</h5>
                        <span class="badge bg-secondary" id="pendingCount">0 pendientes</span>
                    </div>
                    <div id="pendingOrdersList">
                        <!-- Los pedidos se cargarán aquí dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div id="searchResults" class="mt-4">
        <!-- Los resultados se mostrarán aquí -->
    </div>

    <!-- Modal para confirmar inicio de producción -->
    <div class="modal fade" id="startProductionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Iniciar producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas iniciar la producción del pedido <strong id="modalOrderCode"></strong>?</p>
                    <div class="mb-3">
                        <label for="productionNotes" class="form-label">Notas iniciales (opcional)</label>
                        <textarea class="form-control" id="productionNotes" rows="2" placeholder="Agregar notas sobre el inicio de producción..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success-custom" id="confirmStartProduction">Iniciar producción</button>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del DOM
            const searchByCodeForm = document.getElementById('searchByCodeForm');
            const orderCodeInput = document.getElementById('orderCode');
            const searchResults = document.getElementById('searchResults');
            const pendingOrdersList = document.getElementById('pendingOrdersList');
            const pendingCount = document.getElementById('pendingCount');
            const cameraPreview = document.getElementById('cameraPreview');
            const toggleCameraBtn = document.getElementById('toggleCamera');
            const captureImageBtn = document.getElementById('captureImage');
            const uploadQR = document.getElementById('uploadQR');
            const startProductionModal = new bootstrap.Modal(document.getElementById('startProductionModal'));
            const modalOrderCode = document.getElementById('modalOrderCode');
            const confirmStartProduction = document.getElementById('confirmStartProduction');

            let currentStream = null;
            let selectedOrder = null;

            // Cargar pedidos pendientes al abrir la pestaña
            document.getElementById('list-tab').addEventListener('click', loadPendingOrders);

            // Buscar por código
            searchByCodeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const code = orderCodeInput.value.trim();

                if (code) {
                    searchOrder(code);
                } else {
                    showAlert('Por favor, ingresa un código de pedido.', 'warning');
                }
            });

            // Cámara - activar/desactivar
            toggleCameraBtn.addEventListener('click', toggleCamera);

            // Capturar imagen de la cámara
            captureImageBtn.addEventListener('click', function() {
                if (currentStream) {
                    // En una implementación real, aquí procesaríamos la imagen
                    // Para este ejemplo, simulamos la búsqueda de un pedido
                    simulateQRScan();
                } else {
                    showAlert('Primero activa la cámara.', 'warning');
                }
            });

            // Subir imagen de código QR
            uploadQR.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        // En una implementación real, aquí procesaríamos la imagen
                        // Para este ejemplo, simulamos la búsqueda
                        simulateQRScan();
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            // Confirmar inicio de producción
            confirmStartProduction.addEventListener('click', function() {
                if (selectedOrder) {
                    startProduction(selectedOrder.id);
                }
            });

            // Funciones
            function searchOrder(code) {
                // Simulación de búsqueda - en una implementación real harías una petición AJAX
                showLoading('Buscando pedido...');

                setTimeout(() => {
                    // Simulamos encontrar un pedido
                    const order = {
                        id: code,
                        code: code,
                        empresa: 'Empresa Ejemplo S.A.',
                        contacto: 'Juan Pérez',
                        descripcion: 'Producción de 100 unidades del modelo X',
                        cantidad: 100,
                        fecha: '2023-10-15',
                        estado: 'pendiente'
                    };

                    displaySearchResults([order]);
                }, 1000);
            }

            function loadPendingOrders() {
                // Simulación de carga de pedidos pendientes
                showLoading('Cargando pedidos pendientes...');

                setTimeout(() => {
                    // Datos de ejemplo
                    const pendingOrders = [
                        {
                            id: 'PED-001',
                            code: 'PED-001',
                            empresa: 'Empresa A',
                            contacto: 'Ana García',
                            descripcion: 'Fabricación de componentes metálicos',
                            cantidad: 50,
                            fecha: '2023-10-10',
                            estado: 'pendiente'
                        },
                        {
                            id: 'PED-002',
                            code: 'PED-002',
                            empresa: 'Empresa B',
                            contacto: 'Carlos López',
                            descripcion: 'Ensamblaje de unidades modelo Z',
                            cantidad: 200,
                            fecha: '2023-10-12',
                            estado: 'pendiente'
                        },
                        {
                            id: 'PED-003',
                            code: 'PED-003',
                            empresa: 'Empresa C',
                            contacto: 'María Rodríguez',
                            descripcion: 'Producción de piezas especiales',
                            cantidad: 75,
                            fecha: '2023-10-14',
                            estado: 'pendiente'
                        }
                    ];

                    displayPendingOrders(pendingOrders);
                }, 1500);
            }

            function displaySearchResults(orders) {
                if (orders.length === 0) {
                    searchResults.innerHTML = `
                        <div class="no-orders">
                            <i class="bi bi-search"></i>
                            <h4>No se encontraron pedidos</h4>
                            <p>No hay pedidos que coincidan con tu búsqueda.</p>
                        </div>
                    `;
                    return;
                }

                let html = '<h4 class="mb-3">Resultados de búsqueda</h4>';

                orders.forEach(order => {
                    html += `
                        <div class="card order-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title">${order.empresa}</h5>
                                        <p class="card-text mb-1"><strong>Contacto:</strong> ${order.contacto}</p>
                                        <p class="card-text mb-1"><strong>Descripción:</strong> ${order.descripcion}</p>
                                        <p class="card-text mb-1"><strong>Cantidad:</strong> ${order.cantidad} unidades</p>
                                        <p class="card-text"><strong>Fecha:</strong> ${order.fecha}</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="order-status status-pending">Pendiente</span>
                                        <div class="mt-2">
                                            <button class="btn btn-success-custom btn-sm start-production-btn" data-order-id="${order.id}" data-order-code="${order.code}">
                                                <i class="bi bi-play-circle me-1"></i> Iniciar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                searchResults.innerHTML = html;

                // Agregar event listeners a los botones de iniciar producción
                document.querySelectorAll('.start-production-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        const orderCode = this.getAttribute('data-order-code');
                        openStartProductionModal(orderId, orderCode);
                    });
                });
            }

            function displayPendingOrders(orders) {
                if (orders.length === 0) {
                    pendingOrdersList.innerHTML = `
                        <div class="no-orders">
                            <i class="bi bi-check-circle"></i>
                            <h4>No hay pedidos pendientes</h4>
                            <p>Todos los pedidos están en producción o completados.</p>
                        </div>
                    `;
                    pendingCount.textContent = '0 pendientes';
                    return;
                }

                let html = '';

                orders.forEach(order => {
                    html += `
                        <div class="card order-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title">${order.empresa} <span class="text-muted">(${order.code})</span></h5>
                                        <p class="card-text mb-1"><strong>Contacto:</strong> ${order.contacto}</p>
                                        <p class="card-text mb-1"><strong>Descripción:</strong> ${order.descripcion}</p>
                                        <p class="card-text mb-1"><strong>Cantidad:</strong> ${order.cantidad} unidades</p>
                                        <p class="card-text"><strong>Fecha:</strong> ${order.fecha}</p>
                                    </div>
                                    <div class="text-end">
                                        <span class="order-status status-pending">Pendiente</span>
                                        <div class="mt-2">
                                            <button class="btn btn-success-custom btn-sm start-production-btn" data-order-id="${order.id}" data-order-code="${order.code}">
                                                <i class="bi bi-play-circle me-1"></i> Iniciar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                pendingOrdersList.innerHTML = html;
                pendingCount.textContent = `${orders.length} pendiente${orders.length !== 1 ? 's' : ''}`;

                // Agregar event listeners a los botones de iniciar producción
                document.querySelectorAll('.start-production-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        const orderCode = this.getAttribute('data-order-code');
                        openStartProductionModal(orderId, orderCode);
                    });
                });
            }

            function openStartProductionModal(orderId, orderCode) {
                selectedOrder = { id: orderId, code: orderCode };
                modalOrderCode.textContent = orderCode;
                startProductionModal.show();
            }

            function startProduction(orderId) {
                // Simulación de inicio de producción - en una implementación real harías una petición AJAX
                showLoading('Iniciando producción...');

                setTimeout(() => {
                    startProductionModal.hide();
                    showAlert('Producción iniciada correctamente.', 'success');

                    // Actualizar la lista de pedidos pendientes
                    if (document.getElementById('list-tab').classList.contains('active')) {
                        loadPendingOrders();
                    }

                    // Limpiar resultados de búsqueda
                    searchResults.innerHTML = '';
                    orderCodeInput.value = '';

                    selectedOrder = null;
                }, 1500);
            }

            function toggleCamera() {
                if (currentStream) {
                    // Detener cámara
                    currentStream.getTracks().forEach(track => track.stop());
                    currentStream = null;
                    cameraPreview.innerHTML = `
                        <div class="text-center">
                            <i class="bi bi-camera" style="font-size: 2rem;"></i>
                            <p class="mt-2">Haz clic para activar la cámara</p>
                        </div>
                    `;
                    toggleCameraBtn.innerHTML = '<i class="bi bi-camera-video"></i> Activar';
                } else {
                    // Activar cámara (simulación)
                    // En una implementación real usarías la API de getUserMedia
                    cameraPreview.innerHTML = `
                        <div class="qr-scanner-container">
                            <div class="bg-dark text-white text-center p-4 rounded">
                                <i class="bi bi-qr-code-scan" style="font-size: 3rem;"></i>
                                <p class="mt-2">Simulación de escáner QR</p>
                                <p class="small">En una implementación real, aquí se mostraría la cámara</p>
                            </div>
                            <div class="qr-overlay"></div>
                        </div>
                    `;
                    toggleCameraBtn.innerHTML = '<i class="bi bi-camera-video-off"></i> Desactivar';
                    currentStream = true; // Marcador para indicar que la cámara está "activada"
                }
            }

            function simulateQRScan() {
                // Simulación de escaneo de QR
                showLoading('Procesando código QR...');

                setTimeout(() => {
                    // Simulamos encontrar un pedido con código aleatorio
                    const randomCode = 'PED-' + Math.floor(100 + Math.random() * 900);
                    searchOrder(randomCode);
                }, 1000);
            }

            function showLoading(message) {
                searchResults.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">${message}</p>
                    </div>
                `;
            }

            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-warning';

                const alertHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;

                // Insertar alerta al principio del contenido
                const content = document.querySelector('.container');
                content.insertAdjacentHTML('afterbegin', alertHTML);

                // Auto-eliminar después de 5 segundos
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }
        });
    </script>
<?= $this->endSection() ?>