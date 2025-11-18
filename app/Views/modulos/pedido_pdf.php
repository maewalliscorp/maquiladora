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
        .letterhead {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .letterhead h1 {
            color: #000;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
            text-align: center;
            text-transform: uppercase;
        }
        .letterhead .maquiladora-info {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10px;
            line-height: 1.4;
        }
        .letterhead .document-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 10px;
            text-transform: uppercase;
        }
        .letterhead .document-info {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 15px;
        }
        .header {
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #000;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header .folio {
            font-size: 11px;
            color: #000;
            font-weight: 600;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h2 {
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
            font-size: 13px;
            color: #000;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: 600;
            padding: 6px 15px 6px 0;
            width: 35%;
            vertical-align: top;
            color: #000;
        }
        .info-value {
            display: table-cell;
            padding: 6px 0;
            width: 65%;
            color: #000;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background-color: #000;
            color: #fff;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #000;
        }
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #000;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            font-size: 11px;
            color: #000;
        }
        .table tr:first-child td {
            border-top: 1px solid #000;
        }
        .table tr:last-child td {
            border-bottom: 2px solid #000;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-box {
            display: inline-block;
            border: 2px solid #000;
            padding: 18px 35px;
            background-color: #fff;
        }
        .total-label {
            font-size: 12px;
            font-weight: 700;
            color: #000;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .total-value {
            font-size: 24px;
            font-weight: 700;
            color: #000;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9px;
            color: #000;
        }
        .text-muted {
            color: #000;
        }
        .section-divider {
            height: 1px;
            background: #000;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Membrete de la maquiladora -->
        <div class="letterhead">
            <?php if (!empty($maquiladora)): ?>
                <h1><?= esc($maquiladora['nombre'] ?? 'SIN NOMBRE') ?></h1>
                <div class="maquiladora-info">
                    <?php if (!empty($maquiladora['domicilio'])): ?>
                        <?= esc($maquiladora['domicilio']) ?><br>
                    <?php endif; ?>
                    <?php if (!empty($maquiladora['telefono'])): ?>
                        Tel: <?= esc($maquiladora['telefono']) ?>
                    <?php endif; ?>
                    <?php if (!empty($maquiladora['correo'])): ?>
                        | Email: <?= esc($maquiladora['correo']) ?>
                    <?php endif; ?>
                    <?php if (!empty($maquiladora['dueno'])): ?>
                        <br>Representante: <?= esc($maquiladora['dueno']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="document-title">ORDEN DE COMPRA</div>
            
            <div class="document-info">
                <div><strong>Folio:</strong> <?= esc($pedido['folio'] ?? 'N/A') ?></div>
                <div><strong>Fecha:</strong> <?= date('d/m/Y') ?></div>
            </div>
        </div>
        
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
