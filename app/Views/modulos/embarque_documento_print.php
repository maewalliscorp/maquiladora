<?php
/** @var array $embarque */
/** @var array $items */

// Helpers de formato
$fmtFlex = function ($n, $maxDecimals = 2) {
    $n = (float)$n;
    if (floor($n) == $n) return number_format($n, 0, '.', ',');  // sin .00
    $s = number_format($n, $maxDecimals, '.', ',');
    $s = rtrim(rtrim($s, '0'), '.');
    return $s === '' ? '0' : $s;
};
$moneyFlex = function ($n) use ($fmtFlex) {
    return '$' . $fmtFlex($n, 2);  // quita .00 si es entero
};

// Totales
$totalCant = 0; $totalPeso = 0; $totalImporte = 0;
foreach ($items as $it) {
    $c = (float)($it['cantidad'] ?? 0);
    $pu = (float)($it['peso'] ?? $it['pesoUnit'] ?? 0);    // se usa para calcular, ya no se muestra
    $vu = (float)($it['valor'] ?? $it['valorUnit'] ?? 0);
    $totalCant    += $c;
    $totalPeso    += $c * $pu;
    $totalImporte += $c * $vu;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documento de Embarque</title>
    <style>
        /* Carta (US Letter) con márgenes “normales” */
        @page { size: Letter; margin: 25mm 30mm; } /* 2.5 cm top/bottom, 3 cm left/right */

        html, body { background:#fff; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
            color:#1f2937; line-height:1.25; font-size:12px; margin:0; /* importante: sin márgenes del body */
        }

        /* ¡Ya no fijamos 190mm! Dejamos que ocupe todo el ancho disponible entre márgenes */
        .sheet { width:auto; max-width:100%; margin:0; }

        .muted { color:#6b7280; }
        .chip { display:inline-block; padding:.2rem .6rem; border-radius:999px; background:#eef2ff; color:#3730a3; font-weight:700; }

        .row { display:flex; flex-wrap:wrap; gap:8px; }
        .col-6 { flex: 0 0 calc(50% - 4px); }
        .box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; }
        .title { font-size:16px; font-weight:800; }
        .kv { margin-top:2px; }

        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        th, td { border:1px solid #e5e7eb; padding:6px 8px; vertical-align:top; }
        thead th { background:#f3f4f6; font-weight:700; }
        tfoot th, tfoot td { background:#f3f4f6; font-weight:700; }

        .col-sku  { width:80px; }
        .col-desc { width:auto; word-break:break-word; hyphens:auto; }
        .col-cant { width:70px; text-align:right; }
        .col-um   { width:60px; }
        .col-vu   { width:100px; text-align:right; }
        .col-peso { width:90px; text-align:right; }
        .col-imp  { width:110px; text-align:right; }

        tr { page-break-inside:avoid; }

        .signatures { margin-top:24px; display:flex; gap:24px; }
        .sig { flex:1 1 0; text-align:center; }
        .sig-line { border-top:1px solid #cfd8e3; height:1px; margin:0 0 6px 0; }
        .sig-label { color:#475569; }
        .avoid-break { page-break-inside:avoid; }
    </style>

</head>
<body>
<div class="sheet">

    <!-- Encabezado -->
    <div class="row" style="align-items:flex-start; justify-content:space-between; margin-bottom:8px;">
        <div>
            <div class="title">Documento de Embarque</div>
            <div class="muted">Folio: <strong><?= esc($embarque['folio'] ?? '') ?></strong> &nbsp;|&nbsp;
                Fecha: <strong><?= esc($embarque['fecha'] ?? '') ?></strong></div>
        </div>
        <div style="text-align:right">
            <div class="chip"><?= esc($embarque['folio'] ?? '') ?></div>
        </div>
    </div>

    <!-- Info Rem/Dest -->
    <div class="row" style="margin-bottom:8px;">
        <div class="col-6">
            <div class="box">
                <div class="muted" style="font-weight:700; letter-spacing:.02em;">REMITENTE</div>
                <div class="kv"><strong><?= esc($embarque['remitente'] ?? '') ?></strong></div>
                <div class="kv">RFC: <?= esc($embarque['rfcRemitente'] ?? '') ?></div>
                <div class="kv"><?= esc($embarque['domicilioRemitente'] ?? '') ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="box">
                <div class="muted" style="font-weight:700; letter-spacing:.02em;">DESTINATARIO</div>
                <div class="kv"><strong><?= esc($embarque['destinatario'] ?? '') ?></strong></div>
                <div class="kv">RFC: <?= esc($embarque['rfcDestinatario'] ?? '') ?></div>
                <div class="kv"><?= esc($embarque['domicilioDestinatario'] ?? '') ?></div>
            </div>
        </div>

        <div class="col-6">
            <div class="box">
                <div class="muted" style="font-weight:700; letter-spacing:.02em;">ORIGEN</div>
                <div class="kv"><?= esc($embarque['origen'] ?? '') ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="box">
                <div class="muted" style="font-weight:700; letter-spacing:.02em;">DESTINO</div>
                <div class="kv"><?= esc($embarque['destino'] ?? '') ?></div>
            </div>
        </div>

        <div class="col-6">
            <div class="box">
                <div class="muted" style="font-weight:700; letter-spacing:.02em;">TRANSPORTE</div>
                <div class="kv"><strong><?= esc($embarque['tipoTransporte'] ?? '') ?></strong></div>
                <div class="kv">Transportista: <?= esc($embarque['transportista'] ?? '') ?></div>
                <div class="kv">Operador: <?= esc($embarque['operador'] ?? '') ?> · Placas: <?= esc($embarque['placas'] ?? '') ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="box">
                <div class="muted" style="font-weight:700; letter-spacing:.02em;">REFERENCIA</div>
                <div class="kv"><?= esc($embarque['referencia'] ?? '') ?></div>
            </div>
        </div>
    </div>

    <!-- Tabla (sin Peso Unit.) -->
    <div class="avoid-break">
        <table>
            <thead>
            <tr>
                <th class="col-sku">SKU</th>
                <th class="col-desc">Descripción</th>
                <th class="col-cant">Cant.</th>
                <th class="col-um">UM</th>
                <th class="col-vu">Valor Unit.</th>
                <th class="col-peso">Peso</th>
                <th class="col-imp">Importe</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it):
                $c  = (float)($it['cantidad'] ?? 0);
                $um = (string)($it['um'] ?? '');
                $pu = (float)($it['peso'] ?? $it['pesoUnit'] ?? 0);  // se usa solo para el cálculo
                $vu = (float)($it['valor'] ?? $it['valorUnit'] ?? 0);
                $peso = $c * $pu;
                $imp  = $c * $vu;
                ?>
                <tr>
                    <td class="col-sku"><?= esc($it['sku'] ?? '') ?></td>
                    <td class="col-desc"><?= esc($it['descripcion'] ?? '') ?></td>
                    <td class="col-cant"><?= $fmtFlex($c) ?></td>
                    <td class="col-um"><?= esc($um) ?></td>
                    <td class="col-vu"><?= $moneyFlex($vu) ?></td>
                    <td class="col-peso"><?= $fmtFlex($peso) ?></td>
                    <td class="col-imp"><?= $moneyFlex($imp) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2" style="text-align:right;">Totales</th>
                <th class="col-cant"><?= $fmtFlex($totalCant) ?></th>
                <th></th>
                <th></th>
                <th class="col-peso"><?= $fmtFlex($totalPeso) ?></th>
                <th class="col-imp"><?= $moneyFlex($totalImporte) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <!-- Notas -->
    <div class="box" style="margin-top:10px;">
        <div class="muted" style="font-weight:700; letter-spacing:.02em;">NOTAS</div>
        <div><?= nl2br(esc($embarque['notas'] ?? '')) ?></div>
    </div>

    <!-- Firmas -->
    <div class="signatures avoid-break">
        <div class="sig">
            <div class="sig-line"></div>
            <div class="sig-label">Entrega — Nombre y firma</div>
        </div>
        <div class="sig">
            <div class="sig-line"></div>
            <div class="sig-label">Recibo — Nombre y firma</div>
        </div>
    </div>

</div>
</body>
</html>
