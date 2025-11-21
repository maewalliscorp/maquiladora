<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de pedido a proveedor</title>
    <style>
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            margin: 2px 0;
        }
        .box {
            border: 1px solid #555;
            padding: 8px;
            margin-bottom: 10px;
        }
        .box-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 13px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .col {
            width: 48%;
        }
        .label {
            font-weight: bold;
        }
        .detalle {
            border: 1px solid #555;
            padding: 8px;
            min-height: 80px;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
        }
    </style>
</head>
<body>

<?php
/** @var array $orden */
$folio      = $orden['id_proveedorOC'] ?? '';
$fecha      = $orden['fecha'] ?? '';
$prioridad  = $orden['prioridad'] ?? 'Normal';
$estatus    = $orden['estatus'] ?? 'Pendiente';
$desc       = $orden['descripcion'] ?? '';
$provNom    = $orden['proveedor_nombre'] ?? '';
$provCod    = $orden['proveedor_codigo'] ?? '';
$provEmail  = $orden['proveedor_email'] ?? '';
$provTel    = $orden['proveedor_telefono'] ?? '';
$provDir    = $orden['proveedor_direccion'] ?? '';
?>

<div class="header">
    <h1>Orden de pedido a proveedor</h1>
    <p>Sistema de Maquiladora</p>
</div>

<div class="box">
    <div class="box-title">Datos de la orden</div>
    <div class="row">
        <div class="col">
            <span class="label">Folio:</span> <?= htmlspecialchars('OP-' . $folio) ?><br>
            <span class="label">Fecha de pedido:</span> <?= htmlspecialchars($fecha) ?><br>
            <span class="label">Prioridad:</span> <?= htmlspecialchars($prioridad) ?>
        </div>
        <div class="col">
            <span class="label">Estatus:</span> <?= htmlspecialchars($estatus) ?><br>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-title">Proveedor</div>
    <div><span class="label">Código:</span> <?= htmlspecialchars($provCod) ?></div>
    <div><span class="label">Nombre / Empresa:</span> <?= htmlspecialchars($provNom) ?></div>
    <div><span class="label">Email:</span> <?= htmlspecialchars($provEmail) ?></div>
    <div><span class="label">Teléfono:</span> <?= htmlspecialchars($provTel) ?></div>
    <div><span class="label">Dirección:</span> <?= htmlspecialchars($provDir) ?></div>
</div>

<div class="box">
    <div class="box-title">Materiales / Detalle del pedido</div>
    <div class="detalle">
        <?= nl2br(htmlspecialchars($desc)) ?>
    </div>
</div>

<div class="footer">
    <p>______________________________<br>
        Firma de autorización</p>
</div>

</body>
</html>
