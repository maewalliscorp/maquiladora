<?php
$embarque = $embarque ?? [];
$etiqueta = $etiqueta ?? [];

$folio       = $embarque['folio'] ?? ('EMB-'.$embarque['id'] ?? '');
$codigo      = $etiqueta['codigo'] ?? ('ETQ-'.$folio);
$shipNom     = $etiqueta['ship_to_nombre']    ?? ($embarque['clienteNombre'] ?? '');
$shipDir     = $etiqueta['ship_to_direccion'] ?? ($embarque['destino'] ?? ($embarque['Domicilio'] ?? $embarque['direccion'] ?? ''));
$shipCiudad  = $etiqueta['ship_to_ciudad']    ?? '';
$shipPais    = $etiqueta['ship_to_pais']      ?? 'México';
$referencia  = $etiqueta['referencia']        ?? '';
$pesoBruto   = $etiqueta['peso_bruto']        ?? '';
$pesoNeto    = $etiqueta['peso_neto']         ?? '';
$bultos      = $etiqueta['bultos']            ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Etiqueta <?= esc($folio) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
        }
        .etiqueta {
            border: 1px solid #000;
            padding: 8px 10px;
            width: 100%;
            height: 100%;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .titulo {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
        }
        .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #555;
        }
        .valor {
            font-size: 11px;
        }
        .right { text-align: right; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .border-top {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 4px;
        }
    </style>
</head>
<body>
<div class="etiqueta">
    <div class="row">
        <div class="titulo">Embarque</div>
        <div class="valor"><?= esc($folio) ?></div>
    </div>

    <div class="mt-1">
        <div class="label">Ship to</div>
        <div class="valor"><?= esc($shipNom) ?></div>
        <div class="valor"><?= esc($shipDir) ?></div>
        <?php if ($shipCiudad || $shipPais): ?>
            <div class="valor"><?= esc(trim($shipCiudad.' '.$shipPais)) ?></div>
        <?php endif; ?>
    </div>

    <div class="row mt-2">
        <div>
            <div class="label">Referencia</div>
            <div class="valor"><?= $referencia !== '' ? esc($referencia) : '—' ?></div>
        </div>
        <div class="right">
            <div class="label">Código etiqueta</div>
            <div class="valor"><?= esc($codigo) ?></div>
        </div>
    </div>

    <div class="row border-top">
        <div>
            <div class="label">Bultos</div>
            <div class="valor"><?= $bultos !== '' ? esc($bultos) : '—' ?></div>
        </div>
        <div class="right">
            <div class="label">Peso bruto (kg)</div>
            <div class="valor"><?= $pesoBruto !== '' ? esc($pesoBruto) : '—' ?></div>
        </div>
        <div class="right">
            <div class="label">Peso neto (kg)</div>
            <div class="valor"><?= $pesoNeto !== '' ? esc($pesoNeto) : '—' ?></div>
        </div>
    </div>
</div>
</body>
</html>
