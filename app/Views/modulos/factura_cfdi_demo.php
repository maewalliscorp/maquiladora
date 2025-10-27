<?php
// ===== Helpers mínimos =====
if (!function_exists('cur')) {
    function cur($n){ return number_format((float)$n, 2, '.', ','); }
}
$emisor    = $emisor    ?? [];
$receptor  = $receptor  ?? [];
$factura   = $factura   ?? [];
$timbre    = $timbre    ?? [];
$conceptos = $conceptos ?? [];
$totales   = $totales   ?? [];

// Fallbacks suaves
$factura += [
        'tipo'        => 'I - Ingreso',
        'serie'       => 'DEMO',
        'folio'       => rand(1000,9999),
        'fecha'       => date('Y-m-d H:i:s'),
        'moneda'      => 'MXN',
        'tipoCambio'  => '1.0000',
        'formaPago'   => '03 - Transferencia',
        'metodoPago'  => 'PUE - Pago en una sola exhibición',
        'condiciones' => 'Contado',
];
$emisor += [
        'nombre'          => 'Textiles XYZ S.A. de C.V.',
        'rfc'             => 'TXY123456789',
        'regimen'         => '601',
        'lugarExpedicion' => '00000',
        'noCertCSD'       => '000010000000403258748',
        'logo'            => null,
];
$receptor += [
        'nombre'    => 'Cliente Demo',
        'rfc'       => 'XAXX010101000',
        'usoCfdi'   => 'G03',
        'domicilio' => 'CP 00000',
];
$totales += [
        'subtotal'    => 0, 'descuento' => 0, 'trasladados' => 0, 'retenidos' => 0, 'total' => 0,
        'letra'       => 'CIENTO DIECISÉIS PESOS 00/100 M.N.',
];

// Cálculo rápido si hace falta
if (empty($totales['subtotal']) && $conceptos) {
    $sub=0; $desc=0; $iva=0; $ieps=0;
    foreach ($conceptos as $c) {
        $cant  = (float)($c['cantidad']      ?? 0);
        $vu    = (float)($c['valorUnitario'] ?? $c['precioUnitario'] ?? 0);
        $imp   = (float)($c['importe']       ?? ($cant*$vu));
        $dcto  = (float)($c['descuento']     ?? 0);
        $ivaC  = (float)($c['iva']           ?? 0);
        $iepsC = (float)($c['ieps']          ?? 0);
        $sub  += $imp; $desc += $dcto; $iva += $ivaC; $ieps += $iepsC;
    }
    $totales['subtotal']    = $sub;
    $totales['descuento']   = $desc;
    $totales['trasladados'] = $iva + $ieps;
    $totales['total']       = $sub - $desc + $totales['trasladados'] - ($totales['retenidos'] ?? 0);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura / CFDI 4.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{ --line:#e6e8ef; --muted:#6b7280; --chip:#eef4ff; --chip-text:#0b5ed7; }
        *{ box-sizing:border-box; }
        body{
            margin:0; background:#fff; color:#111827;
            font: 12px/1.35 system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif;
        }
        .wrap{ width:100%; max-width:790px; margin:8px auto; padding:6px 8px; }
        h1,h2,h3{ margin:0; }
        .small{ font-size:11px; }
        .muted{ color:var(--muted); }
        .box{
            border:1px solid var(--line); border-radius:8px; padding:8px 10px; background:#fff;
        }
        .grid2{ display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .grid3{ display:grid; grid-template-columns:1.1fr .9fr .9fr; gap:8px; }
        .right{ text-align:right; }
        .logo{
            width:120px; height:48px; border:1px dashed var(--line); border-radius:8px;
            display:flex; align-items:center; justify-content:center; color:#9aa3b2; font-weight:700;
        }
        .chip{ display:inline-block; background:var(--chip); color:var(--chip-text); padding:.1rem .45rem; border-radius:999px; font-weight:700; font-size:11px; }
        table{ width:100%; border-collapse:collapse; }
        th,td{ border-top:1px solid var(--line); padding:.28rem .35rem; vertical-align:top; }
        thead th{ background:#f7fafc; border-top:0; font-weight:700; }
        .totals td{ padding:.23rem .35rem; }
        .totals .lab{ color:#374151; }
        .totals .val{ text-align:right; font-weight:600; }
        .totals .grand{ border-top:1px solid var(--line); font-size:12px; font-weight:800; }
        .mono{ font-family:monospace; font-size:10px; word-break:break-all; }
        .qr{
            width:110px; height:110px; border:1px solid var(--line); border-radius:8px;
            display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:11px;
        }

        /* ===== Impresión: forzar 1 hoja Carta compacta ===== */
        @media print{
            @page{ size: Letter; margin:7mm 8mm; }
            body{ -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            .wrap{ max-width:100%; padding:0; }
            .grid2{ gap:6px; }
            .grid3{ gap:6px; grid-template-columns:1fr .85fr .85fr; }
            .logo{ width:100px; height:40px; }
            table th, table td{ padding:.22rem .28rem; font-size:10.5px; }
            .totals td{ font-size:10.5px; }
            .totals .grand{ font-size:11px; }
            .qr{ width:90px; height:90px; }
            .box, .grid2, .grid3, table, thead, tbody, tr, td, th { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="wrap">

    <!-- Encabezado compacto -->
    <div class="grid2">
        <div class="box" style="display:grid; grid-template-columns:110px 1fr; gap:10px;">
            <div class="logo">
                <?php if (!empty($emisor['logo'])): ?>
                    <img src="<?= esc($emisor['logo']) ?>" style="width:100%;height:100%;object-fit:contain" alt="Logo">
                <?php else: ?>LOGO<?php endif; ?>
            </div>
            <div class="small">
                <div><strong><?= esc($emisor['nombre']) ?></strong></div>
                <div>RFC: <?= esc($emisor['rfc']) ?></div>
                <div>Régimen: <?= esc($emisor['regimen']) ?></div>
                <div>Lugar exped.: <?= esc($emisor['lugarExpedicion']) ?></div>
            </div>
        </div>
        <div class="box right small">
            <div><span class="chip">Tipo: <?= esc($factura['tipo']) ?></span></div>
            <div>Serie: <strong><?= esc($factura['serie']) ?></strong> Folio: <strong><?= esc($factura['folio']) ?></strong></div>
            <div>Fecha: <strong><?= esc($factura['fecha']) ?></strong></div>
            <div>No. CSD: <strong><?= esc($emisor['noCertCSD']) ?></strong></div>
        </div>
    </div>

    <!-- Receptor / Pago -->
    <div class="grid2" style="margin-top:6px;">
        <div class="box small">
            <div style="font-weight:700; margin-bottom:4px;">Datos del Receptor</div>
            <div><strong>Nombre:</strong> <?= esc($receptor['nombre']) ?></div>
            <div><strong>RFC:</strong> <?= esc($receptor['rfc']) ?></div>
            <div><strong>Uso CFDI:</strong> <?= esc($receptor['usoCfdi']) ?></div>
            <div><strong>Domicilio:</strong> <?= esc($receptor['domicilio']) ?></div>
        </div>
        <div class="box small">
            <div style="font-weight:700; margin-bottom:4px;">Condiciones y Pago</div>
            <div>Moneda: <?= esc($factura['moneda']) ?> · TC: <?= esc($factura['tipoCambio']) ?></div>
            <div>Forma: <?= esc($factura['formaPago']) ?> · Método: <?= esc($factura['metodoPago']) ?></div>
            <div>Condiciones: <?= esc($factura['condiciones']) ?></div>
        </div>
    </div>

    <!-- Conceptos -->
    <div class="box" style="margin-top:6px;">
        <table>
            <thead>
            <tr>
                <th style="width:85px;">Clave ProdServ</th>
                <th style="width:60px;">Clave Unid.</th>
                <th style="width:60px; text-align:right;">Cantidad</th>
                <th style="width:60px;">Unidad</th>
                <th>Descripción</th>
                <th style="width:85px; text-align:right;">V. Unit.</th>
                <th style="width:80px; text-align:right;">Desc.</th>
                <th style="width:85px; text-align:right;">Importe</th>
                <th style="width:60px; text-align:right;">IVA</th>
                <th style="width:60px; text-align:right;">IEPS</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($conceptos as $c):
                $prodServ = $c['prodserv'] ?? $c['claveProdServ'] ?? '';
                $claveUni = $c['claveUnidad'] ?? $c['clave_unidad'] ?? '';
                $unidad   = $c['unidad'] ?? ($c['unidadDescripcion'] ?? '');
                $cant     = (float)($c['cantidad'] ?? 0);
                $vUnit    = (float)($c['valorUnitario'] ?? $c['precioUnitario'] ?? 0);
                $desc     = (float)($c['descuento'] ?? 0);
                $importe  = (float)($c['importe'] ?? ($cant*$vUnit));
                $iva      = (float)($c['iva'] ?? 0);
                $ieps     = (float)($c['ieps'] ?? 0);
                $descTxt  = $c['descripcion'] ?? '';
                ?>
                <tr>
                    <td><?= esc($prodServ) ?></td>
                    <td><?= esc($claveUni) ?></td>
                    <td style="text-align:right;"><?= cur($cant) ?></td>
                    <td><?= esc($unidad) ?></td>
                    <td><?= esc($descTxt) ?></td>
                    <td style="text-align:right;"><?= cur($vUnit) ?></td>
                    <td style="text-align:right;"><?= cur($desc) ?></td>
                    <td style="text-align:right;"><?= cur($importe) ?></td>
                    <td style="text-align:right;"><?= cur($iva) ?></td>
                    <td style="text-align:right;"><?= cur($ieps) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Cierre en 3 columnas para que todo quepa -->
    <div class="grid3" style="margin-top:6px;">
        <div class="box small">
            <div style="font-weight:700; margin-bottom:4px;">Observaciones</div>
            <div class="muted">Gracias por su preferencia.</div>
            <?php if (!empty($totales['letra'])): ?>
                <div class="muted" style="margin-top:8px;"><strong>Importe con letra:</strong><br><?= esc($totales['letra']) ?></div>
            <?php endif; ?>
        </div>

        <div class="box">
            <table class="totals small">
                <tr><td class="lab">Subtotal</td>             <td class="val">$ <?= cur($totales['subtotal']) ?></td></tr>
                <tr><td class="lab">Descuento</td>            <td class="val">$ <?= cur($totales['descuento']) ?></td></tr>
                <tr><td class="lab">Impuestos trasladados</td><td class="val">$ <?= cur($totales['trasladados']) ?></td></tr>
                <tr><td class="lab">Impuestos retenidos</td>  <td class="val">$ <?= cur($totales['retenidos']) ?></td></tr>
                <tr class="grand"><td class="lab">TOTAL</td>  <td class="val">$ <?= cur($totales['total']) ?></td></tr>
            </table>
        </div>

        <div class="box small" style="display:grid; grid-template-columns:1fr auto; gap:8px; align-items:start;">
            <div>
                <div style="font-weight:700; margin-bottom:4px;">Timbre fiscal digital</div>
                <div><strong>UUID:</strong> <?= esc($timbre['uuid'] ?? '00000000-0000-0000-0000-000000000000') ?></div>
                <div><strong>Fecha:</strong> <?= esc($timbre['fechaTimbrado'] ?? $factura['fecha']) ?></div>
                <div><strong>No. Cert. SAT:</strong> <?= esc($timbre['noCertCSD'] ?? $emisor['noCertCSD']) ?></div>
                <div style="margin-top:4px;"><strong>Sello CFDI:</strong></div>
                <div class="mono" style="border:1px solid var(--line); border-radius:6px; padding:6px;">
                    <?= esc(($timbre['selloCfdi'] ?? 'SELLO_CFDI_DE_EJEMPLO...')) ?>
                </div>
                <div style="margin-top:4px;"><strong>Sello SAT:</strong></div>
                <div class="mono" style="border:1px solid var(--line); border-radius:6px; padding:6px;">
                    <?= esc(($timbre['selloSat'] ?? 'SELLO_SAT_DE_EJEMPLO...')) ?>
                </div>
                <div class="muted" style="margin-top:6px;">Representación impresa de un CFDI.</div>
            </div>
            <div class="qr">
                <?php if (!empty($timbre['qr'])): ?>
                    <img src="<?= esc($timbre['qr']) ?>" alt="QR" style="width:100%;height:100%;object-fit:contain;">
                <?php else: ?>QR<?php endif; ?>
            </div>
        </div>
    </div>

</div>
</body>
</html>
