<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido - Maquiladora</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #63677c;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }
        .navbar-custom {
            background-color: #5ca0d3;
        }
        .card {
            background-color: #847c84;
            border-radius: 10px;
            margin-bottom: 15px;
            border: none;
        }
        .card-header {
            background-color: #5ca0d3;
            color: #000;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
            padding: 12px 20px;
            font-size: 1.1rem;
        }
        .card-body {
            padding: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        .info-item {
            margin-bottom: 12px;
        }
        .info-label {
            font-weight: bold;
            color: #000000;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }
        .info-value {
            color: #221f1f;
            font-size: 0.95rem;
            padding-left: 5px;
        }
        .modelo-img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            border: 2px solid #5ca0d3;
        }
        .btn-volver {
            background-color: #5ca0d3;
            border: none;
            font-weight: bold;
            padding: 8px 20px;
        }
        .btn-volver:hover {
            background-color: #4a8ab3;
        }
        .progress {
            height: 20px;
            margin: 10px 0;
        }
        .progress-bar {
            background-color: #5ca0d3;
            font-size: 0.8rem;
        }
        .section-title {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .compact-row {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <img src="<?= base_url('img/maquiladora.png') ?>" alt="Logo" width="60">
    <a class="navbar-brand text-dark fw-bold" href="#">Sistema de Maquiladora</a>
    <div class="ms-auto">
        <a href="<?= base_url('perfilempleado') ?>" class="btn btn-link text-dark">Mi perfil</a>
        <a href="<?= base_url('pedidos') ?>" class="btn btn-link text-dark">Pedidos</a>
        <a href="#" class="btn btn-link text-dark">Órdenes de pedidos</a>
        <a href="<?= base_url('logout') ?>" class="btn btn-dark">Cerrar sesión</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 section-title">📋 Detalle del Pedido #<?= $pedido['id'] ?? '1' ?></h2>
        <a href="<?= base_url('pedidos') ?>" class="btn btn-volver">← Volver</a>
    </div>

    <!-- Información del Cliente Compacta -->
    <div class="card">
        <div class="card-header">
            📊 INFORMACIÓN DEL CLIENTE
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <div class="info-label">Empresa:</div>
                        <div class="info-value"><?= $pedido['empresa'] ?? 'Moda Joven SA de CV' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contacto:</div>
                        <div class="info-value"><?= $pedido['contacto'] ?? 'Juan Pérez López' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Teléfono:</div>
                        <div class="info-value"><?= $pedido['telefono'] ?? '81-1234-5678' ?></div>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?= $pedido['email'] ?? 'juan.perez@modajoven.com.mx' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">RFC:</div>
                        <div class="info-value"><?= $pedido['rfc'] ?? 'MOJ123456AB1' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value"><?= $pedido['direccion'] ?? 'Av. Revolución 123, Col. Centro, Monterrey, N.L.' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Descripción y Especificaciones en una sola fila -->
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    📝 DESCRIPCIÓN
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Producto:</div>
                        <div class="info-value"><?= $pedido['descripcion'] ?? 'Camiseta de piqué de algodón 100%, cuello redondo' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Cantidad:</div>
                        <div class="info-value"><?= $pedido['cantidad'] ?? '36,000' ?> unidades</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Entrega:</div>
                        <div class="info-value"><?= $pedido['fecha_entrega'] ?? '15 de Noviembre de 2025' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    ⚙️ ESPECIFICACIONES
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Tallas:</div>
                        <div class="info-value"><?= $pedido['tallas'] ?? 'S, M, L, XL (Ratio: 1:2:2:1)' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Material:</div>
                        <div class="info-value"><?= $pedido['materiales'] ?? 'Piqué 160 grs. (95% algodón, 5% elastano)' ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Color:</div>
                        <div class="info-value"><?= $pedido['color'] ?? 'Blanco Oxford' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progreso y Modelo en una sola fila -->
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    📈 PROGRESO
                </div>
                <div class="card-body text-center">
                    <div class="info-item">
                        <div class="info-label">Estado actual:</div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar"
                                 style="width: <?= $pedido['progreso'] ?? 50 ?>%;"
                                 aria-valuenow="<?= $pedido['progreso'] ?? 50 ?>"
                                 aria-valuemin="0" aria-valuemax="100">
                                <?= $pedido['progreso'] ?? 50 ?>%
                            </div>
                        </div>
                    </div>
                    <div class="info-item mt-3">
                        <div class="info-label">Modelo:</div>
                        <div class="info-value"><?= $pedido['modelo'] ?? 'MODELO 1' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    🖼️ IMAGEN DEL MODELO
                </div>
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/500x300/5ca0d3/ffffff?text=Modelo+<?= urlencode($pedido['modelo'] ?? 'Camiseta') ?>"
                         alt="Modelo <?= $pedido['modelo'] ?? 'Camiseta' ?>" class="modelo-img">
                    <p class="mt-3 info-value"><?= $pedido['descripcion'] ?? 'Camiseta de piqué, cuello redondo' ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>