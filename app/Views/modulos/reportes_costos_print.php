<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Costos - <?= esc($plantilla['nombre_plantilla']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 12px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.25rem;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4 border-bottom pb-3">
            <div class="col-12 text-center">
                <h3 class="header-title">Hoja de Costos y Balanceo</h3>
                <p class="mb-0"><strong>Plantilla:</strong> <?= esc($plantilla['nombre_plantilla']) ?> |
                    <strong>Tipo:</strong> <?= esc($plantilla['tipo_prenda']) ?></p>
                <p class="text-muted small">Generado el: <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <!-- Operations Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 50px;">No.</th>
                        <th>Operación</th>
                        <th style="width: 80px;">Tiempo (Seg)</th>
                        <th style="width: 80px;">Cuota Diaria</th>
                        <th style="width: 80px;">Cuota Bihoraria</th>
                        <th style="width: 80px;">Precio ($)</th>
                        <th style="width: 100px;">Sección</th>
                        <th style="width: 100px;">Depto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalSegundos = 0;
                    $totalCosto = 0;
                    $ops = is_string($plantilla['operaciones']) ? json_decode($plantilla['operaciones'], true) : $plantilla['operaciones'];

                    if (!empty($ops) && is_array($ops)):
                        foreach ($ops as $index => $op):
                            $tiempo = floatval($op['tiempo_segundos'] ?? 0);
                            $precio = floatval($op['precio_operacion'] ?? 0);
                            $totalSegundos += $tiempo;
                            $totalCosto += $precio;

                            // Cálculos estimados (mismos que en JS)
                            $cuotaDiaria = $tiempo > 0 ? floor(34200 / $tiempo) : 0; // 34200 seg jornada
                            $cuotaBi = floor($cuotaDiaria / 4.5);
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= esc($op['nombre']) ?></td>
                                <td class="text-center"><?= $tiempo ?></td>
                                <td class="text-end"><?= $cuotaDiaria ?></td>
                                <td class="text-end"><?= $cuotaBi ?></td>
                                <td class="text-end">$<?= number_format($precio, 3) ?></td>
                                <td><?= esc($op['seccion'] ?? '') ?></td>
                                <td><?= esc($op['departamento'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Totales:</td>
                        <td class="text-center"><?= $totalSegundos ?></td>
                        <td colspan="2"></td>
                        <td class="text-end">$<?= number_format($totalCosto, 3) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Summary -->
        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <table class="table table-bordered table-sm">
                    <tr>
                        <td class="bg-light fw-bold">Total Minutos:</td>
                        <td class="text-end"><?= number_format($totalSegundos / 60, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="bg-light fw-bold">Costo Total Mano de Obra:</td>
                        <td class="text-end">$<?= number_format($totalCosto, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="text-center mt-5 no-print">
            <button class="btn btn-secondary" onclick="window.close()">Cerrar</button>
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
        </div>
    </div>

</body>

</html>