<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra <?= esc($pedido['folio'] ?? 'N/A') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            margin: 1.5cm 1.5cm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.6;
            background: #fff;
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-logo-cell {
            width: 15%;
            vertical-align: middle;
            padding-right: 15px;
        }
        .header-logo-cell img {
            max-height: 80px;
            max-width: 120px;
        }
        .header-info-cell {
            width: 55%;
            vertical-align: middle;
            text-align: left;
        }
        .header-meta-cell {
            width: 30%;
            vertical-align: middle;
            text-align: right;
        }
        .header-org-name {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .header-org-info {
            font-size: 10px;
            color: #555;
            line-height: 1.4;
        }
        .document-title-row {
            margin-top: 15px;
            text-align: right;
        }
        .document-title-text {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
            border-bottom: 1px solid #ccc;
            display: inline-block;
            padding-bottom: 3px;
        }
        .document-meta {
            font-size: 10px;
            margin-top: 5px;
            color: #666;
        }
        .header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 20px;
            margin-top: 20px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header .folio {
            font-size: 11px;
            color: #555;
            font-weight: 600;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h2 {
            background-color: #f8f9fa;
            border-left: 4px solid #2c3e50;
            padding: 8px 10px;
            margin-bottom: 15px;
            font-size: 12px;
            color: #2c3e50;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: 600;
            padding: 8px 10px;
            width: 30%;
            vertical-align: top;
            color: #555;
            background-color: #fff;
            border-bottom: 1px solid #eee;
        }
        .info-value {
            display: table-cell;
            padding: 8px 10px;
            width: 70%;
            color: #000;
            border-bottom: 1px solid #eee;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .table th {
            background-color: #2c3e50;
            color: #fff;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid #2c3e50;
        }
        .table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 11px;
            color: #333;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-box {
            display: inline-block;
            border: 2px solid #2c3e50;
            padding: 15px 30px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .total-label {
            font-size: 11px;
            font-weight: 700;
            color: #555;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .total-value {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Membrete de la maquiladora -->
        <table class="header-table">
            <tr>
                <td class="header-logo-cell">
                    <?php if (!empty($maquiladora['logo_base64'])): ?>
                        <img src="data:<?= $maquiladora['logo_mime'] ?? 'image/jpeg' ?>;base64,<?= $maquiladora['logo_base64'] ?>" alt="Logo">
                    <?php endif; ?>
                </td>
                <td class="header-info-cell">
                    <div class="header-org-name"><?= esc($maquiladora['nombre'] ?? 'SIN NOMBRE') ?></div>
                    <div class="header-org-info">
                        <?php if (!empty($maquiladora['domicilio'])): ?>
                            <?= esc($maquiladora['domicilio']) ?><br>
                        <?php endif; ?>
                        <?php 
                            $contactParts = [];
                            if (!empty($maquiladora['telefono'])) $contactParts[] = 'Tel: ' . esc($maquiladora['telefono']);
                            if (!empty($maquiladora['correo'])) $contactParts[] = 'Email: ' . esc($maquiladora['correo']);
                            echo implode(' | ', $contactParts);
                        ?>
                        <?php if (!empty($maquiladora['dueno'])): ?>
                            <br>Representante: <?= esc($maquiladora['dueno']) ?>
                        <?php endif; ?>
                    </div>
                <td class="header-meta-cell">
                    <div class="document-title-text">ORDEN DE COMPRA</div>
                    <div class="document-meta">
                        <strong>Folio:</strong> <?= esc($pedido['folio'] ?? 'N/A') ?><br>
                        <strong>Fecha:</strong> <?= date('d/m/Y') ?>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Contenido principal -->
        <div class="header">
            <h1>DETALLE DE LA ORDEN</h1>
            <div class="folio">Folio: <?= esc($pedido['folio'] ?? 'N/A') ?> | Fecha: <?= date('d/m/Y') ?></div>
        </div>

        <!-- Información del Pedido -->
        <div class="info-section">
            <h2>Información General</h2>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Folio:</div>
                    <div class="info-value"><?= esc($pedido['folio'] ?? 'N/A') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Fecha:</div>
                    <div class="info-value"><?= esc($pedido['fecha'] ?? 'N/A') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Estatus:</div>
                    <div class="info-value"><?= esc($pedido['estatus'] ?? 'N/A') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Moneda:</div>
                    <div class="info-value"><?= esc($pedido['moneda'] ?? 'MXN') ?></div>
                </div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="info-section">
            <h2>Datos del Cliente</h2>
            <div class="info-grid">
                <?php if (!empty($cliente['nombre'])): ?>
                <div class="info-row">
                    <div class="info-label">Nombre:</div>
                    <div class="info-value"><?= esc($cliente['nombre']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($cliente['email'])): ?>
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= esc($cliente['email']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($cliente['telefono'])): ?>
                <div class="info-row">
                    <div class="info-label">Teléfono:</div>
                    <div class="info-value"><?= esc($cliente['telefono']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($cliente['direccion_detalle'])): ?>
                    <?php $dir = $cliente['direccion_detalle']; ?>
                    <?php if (!empty($dir['calle'])): ?>
                    <div class="info-row">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">
                            <?= esc($dir['calle'] ?? '') ?>
                            <?= !empty($dir['numExt']) ? ' #' . esc($dir['numExt']) : '' ?>
                            <?= !empty($dir['numInt']) ? ' Int. ' . esc($dir['numInt']) : '' ?>
                            <?= !empty($dir['ciudad']) ? ', ' . esc($dir['ciudad']) : '' ?>
                            <?= !empty($dir['estado']) ? ', ' . esc($dir['estado']) : '' ?>
                            <?= !empty($dir['cp']) ? ' CP ' . esc($dir['cp']) : '' ?>
                            <?= !empty($dir['pais']) ? ', ' . esc($dir['pais']) : '' ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información del Diseño/Modelo -->
        <?php if (!empty($diseno)): ?>
        <div class="info-section">
            <h2>Diseño / Modelo</h2>
            <div class="info-grid">
                <?php if (!empty($diseno['codigo'])): ?>
                <div class="info-row">
                    <div class="info-label">Código:</div>
                    <div class="info-value"><?= esc($diseno['codigo']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($diseno['nombre'])): ?>
                <div class="info-row">
                    <div class="info-label">Nombre:</div>
                    <div class="info-value"><?= esc($diseno['nombre']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($diseno['descripcion'])): ?>
                <div class="info-row">
                    <div class="info-label">Descripción:</div>
                    <div class="info-value"><?= esc($diseno['descripcion']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($diseno['precio_unidad'])): ?>
                <div class="info-row">
                    <div class="info-label">Precio Unitario:</div>
                    <div class="info-value"><?= esc($pedido['moneda'] ?? 'MXN') ?> <?= number_format((float)$diseno['precio_unidad'], 2) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($diseno['version'])): ?>
                    <?php $ver = is_array($diseno['version']) ? $diseno['version'] : ['version' => $diseno['version']]; ?>
                    <?php if (!empty($ver['version'])): ?>
                    <div class="info-row">
                        <div class="info-label">Versión:</div>
                        <div class="info-value"><?= esc($ver['version']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($ver['fecha'])): ?>
                    <div class="info-row">
                        <div class="info-label">Fecha Versión:</div>
                        <div class="info-value"><?= esc(date('d/m/Y', strtotime($ver['fecha']))) ?></div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Detalles de Producción -->
        <div class="info-section">
            <h2>Plan</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($op_cantidadPlan)): ?>
                    <tr>
                        <td><strong>Cantidad Plan</strong></td>
                        <td><?= number_format((float)$op_cantidadPlan, 0) ?> unidades</td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($diseno['precio_unidad']) && !empty($op_cantidadPlan)): ?>
                    <tr>
                        <td><strong>Precio Unitario</strong></td>
                        <td><?= esc($pedido['moneda'] ?? 'MXN') ?> <?= number_format((float)$diseno['precio_unidad'], 2) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Subtotal</strong></td>
                        <td><?= esc($pedido['moneda'] ?? 'MXN') ?> <?= number_format((float)$diseno['precio_unidad'] * (float)$op_cantidadPlan, 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($op_fechaInicioPlan)): ?>
                    <tr>
                        <td><strong>Fecha Inicio Plan</strong></td>
                        <td><?= esc($op_fechaInicioPlan) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($op_fechaFinPlan)): ?>
                    <tr>
                        <td><strong>Fecha Fin Plan</strong></td>
                        <td><?= esc($op_fechaFinPlan) ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="total-section">
            <div class="total-box">
                <div class="total-label">TOTAL</div>
                <div class="total-value"><?= esc($pedido['moneda'] ?? 'MXN') ?> <?= esc($pedido['total'] ?? '0.00') ?></div>
            </div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p>Documento generado el <?= date('d/m/Y H:i:s') ?></p>
            <p>© <?= date('Y') ?> Maquiladora - Sistema de Gestión</p>
        </div>
    </div>
</body>
</html>
