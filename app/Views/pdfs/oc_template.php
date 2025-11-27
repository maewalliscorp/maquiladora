<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra #<?= $oc['id'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #0066cc;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 11px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
            color: #0066cc;
        }

        .info-value {
            flex: 1;
        }

        .table-container {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #0066cc;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #0066cc;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0066cc;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #0066cc;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>ORDEN DE COMPRA</h1>
        <p>Sistema de Gestión de Maquiladora</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">No. de OC:</span>
            <span class="info-value"><strong>#<?= str_pad($oc['id'], 6, '0', STR_PAD_LEFT) ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de emisión:</span>
            <span class="info-value"><?= date('d/m/Y H:i', strtotime($oc['created_at'] ?? 'now')) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Proveedor:</span>
            <span class="info-value"><strong><?= esc($oc['prov']) ?></strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha estimada (ETA):</span>
            <span class="info-value" class="highlight"><?= date('d/m/Y', strtotime($oc['eta'])) ?></span>
        </div>
    </div>

    <div class="section-title">DETALLE DE MATERIALES</div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th style="text-align: center;">Cantidad</th>
                    <th style="text-align: center;">Unidad</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= esc($oc['mat']) ?></td>
                    <td style="text-align: center;"><strong><?= number_format($oc['cant'], 2) ?></strong></td>
                    <td style="text-align: center;"><?= esc($oc['u']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section-title">OBSERVACIONES</div>
    <div style="padding: 10px; background-color: #f9f9f9; border-left: 4px solid #0066cc; margin-bottom: 20px;">
        <p>Esta orden de compra ha sido generada automáticamente por el sistema MRP.</p>
        <p>Por favor, confirmar disponibilidad y fecha de entrega.</p>
    </div>

    <div class="footer">
        <p><strong>Sistema de Gestión de Maquiladora</strong></p>
        <p>Documento generado el <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>

</html>