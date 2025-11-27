<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class Api extends BaseController
{
    /**
     * Obtiene la lista de maquiladoras activas para el formulario de registro
     */
    public function maquiladoras()
    {
        try {
            $db = \Config\Database::connect();
            
            // Consulta directa para asegurarnos de obtener los datos correctos
            $query = $db->query("SELECT idmaquiladora, nombre FROM maquiladora WHERE activa = 1 ORDER BY nombre ASC");
            $maquiladoras = $query->getResultArray();
            
            if (empty($maquiladoras)) {
                throw new \Exception('No se encontraron maquiladoras activas');
            }
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $maquiladoras
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error al obtener maquiladoras: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al cargar las maquiladoras: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function dashboard()
    {
        // Prevenir caché
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        $db     = \Config\Database::connect();
        $range  = (int) ($this->request->getGet('range') ?? 30);
        $errors = [];

        // Obtener ID de maquiladora de la sesión
        $maquiladoraId = session()->get('maquiladora_id');

        // Fallback: Si no está en sesión, intentar recargar desde DB usando user_id
        if (empty($maquiladoraId) && session()->get('user_id')) {
            try {
                $u = $db->table('users')->select('maquiladoraIdFK')->where('id', session()->get('user_id'))->get()->getRowArray();
                if ($u && !empty($u['maquiladoraIdFK'])) {
                    $maquiladoraId = $u['maquiladoraIdFK'];
                    session()->set('maquiladora_id', $maquiladoraId);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Si aún no hay maquiladoraId, retornar ceros (seguridad para no mostrar todo)
        if (empty($maquiladoraId)) {
            return $this->response->setJSON([
                'kpis' => [
                    'ordenes_activas' => 0,
                    'wip_cantidad'    => 0,
                    'tasa_defectos'   => 0,
                    'stock_critico'   => 0,
                ],
                'produccion' => [
                    'labels'      => [],
                    'datasets' => [['data' => []], ['data' => []]],
                ],
                'inventario' => [
                    'labels' => [],
                    'datasets' => [['data' => []]],
                    'min' => [], 'max' => []
                ],
                'calidad' => ['labels' => [], 'tasa' => []],
                'logistica' => ['labels' => [], 'data' => []],
                'errors' => ['No se identificó la maquiladora del usuario'],
                'debug' => [
                    'maquiladora_id' => null,
                    'user_id' => session()->get('user_id'),
                    'message' => 'Maquiladora ID missing'
                ]
            ]);
        }

        // Filtro SQL para orden_produccion
        $opFilter = " AND (op.maquiladoraID = " . $db->escape($maquiladoraId) . " OR op.maquiladoraCompartidaID = " . $db->escape($maquiladoraId) . ") ";

        // Filtro SQL para inspeccion (asumiendo columna maquiladoraID)
        $inspFilter = "";
        // Verificar si la columna existe antes de filtrar
        $hasInspCol = $db->fieldExists('maquiladoraID', 'inspeccion');
        if ($hasInspCol) {
            $inspFilter = " AND i.maquiladoraID = " . $db->escape($maquiladoraId) . " ";
        }

        /* =========================================================
         * PRODUCCIÓN · últimas 6 semanas CON datos (usa OC.fecha como respaldo)
         * ========================================================= */
        $p_labels = $p_activas = $p_completadas = [];
        try {
            $wrows = $db->query("
                SELECT yw, CONCAT('S-', LPAD(week_no,2,'0')) AS label
                FROM (
                  SELECT
                    YEARWEEK(COALESCE(op.fechaInicioPlan, op.fechaFinPlan, oc.fecha), 1) AS yw,
                    WEEK(COALESCE(op.fechaInicioPlan, op.fechaFinPlan, oc.fecha), 1) AS week_no
                  FROM orden_produccion op
                  LEFT JOIN orden_compra oc ON oc.id = op.ordenCompraId
                  WHERE COALESCE(op.fechaInicioPlan, op.fechaFinPlan, oc.fecha) IS NOT NULL
                  $opFilter
                ) t
                WHERE yw IS NOT NULL
                GROUP BY yw, week_no
                ORDER BY yw DESC
                LIMIT 6
            ")->getResultArray();

            if (empty($wrows)) {
                for ($i = 5; $i >= 0; $i--) {
                    $ts         = strtotime("-{$i} week");
                    $p_labels[] = 'S-' . str_pad(date('W', $ts), 2, '0', STR_PAD_LEFT);
                }
                $p_activas = $p_completadas = array_fill(0, 6, 0);
            } else {
                $wrows  = array_reverse($wrows);
                $ywList = implode(',', array_map(fn($r) => (int)$r['yw'], $wrows));

                $act = $db->query("
                    SELECT YEARWEEK(COALESCE(op.fechaInicioPlan, oc.fecha), 1) AS yw, COUNT(*) AS c
                    FROM orden_produccion op
                    LEFT JOIN orden_compra oc ON oc.id = op.ordenCompraId
                    WHERE YEARWEEK(COALESCE(op.fechaInicioPlan, oc.fecha), 1) IN ($ywList)
                      AND (
                           op.fechaFinPlan IS NULL OR op.fechaFinPlan = '0000-00-00'
                           OR op.status NOT IN ('Completada','Finalizada','Cerrada')
                      )
                      $opFilter
                    GROUP BY 1
                ")->getResultArray();
                $mapAct = [];
                foreach ($act as $r) $mapAct[(int)$r['yw']] = (int)$r['c'];

                $com = $db->query("
                    SELECT YEARWEEK(COALESCE(op.fechaFinPlan, oc.fecha), 1) AS yw, COUNT(*) AS c
                    FROM orden_produccion op
                    LEFT JOIN orden_compra oc ON oc.id = op.ordenCompraId
                    WHERE YEARWEEK(COALESCE(op.fechaFinPlan, oc.fecha), 1) IN ($ywList)
                      AND (
                           (op.fechaFinPlan IS NOT NULL AND op.fechaFinPlan <> '0000-00-00')
                           OR op.status IN ('Completada','Finalizada','Cerrada')
                      )
                      $opFilter
                    GROUP BY 1
                ")->getResultArray();
                $mapCom = [];
                foreach ($com as $r) $mapCom[(int)$r['yw']] = (int)$r['c'];

                foreach ($wrows as $w) {
                    $p_labels[]      = $w['label'];
                    $yw              = (int)$w['yw'];
                    $p_activas[]     = $mapAct[$yw] ?? 0;
                    $p_completadas[] = $mapCom[$yw] ?? 0;
                }
            }
        } catch (\Throwable $e) {
            $errors[]   = 'Producción: ' . $e->getMessage();
            $p_labels   = ['S-01','S-02','S-03','S-04','S-05','S-06'];
            $p_activas  = $p_completadas = array_fill(0, 6, 0);
        }

        /* =========================================================
         * INVENTARIO · stock + articulo (umbrales opcionales)
         * ========================================================= */
        $i_labels = $i_actual = $i_min = $i_max = [];
        $colMin = null; $colMax = null;

        try {
            foreach (['minimo','stock_min','stockMin'] as $c) {
                $row = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                     AND TABLE_NAME = 'articulo'
                                     AND COLUMN_NAME = ?", [$c])->getRow();
                if ($row) { $colMin = $c; break; }
            }
            foreach (['maximo','stock_max','stockMax'] as $c) {
                $row = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                     AND TABLE_NAME = 'articulo'
                                     AND COLUMN_NAME = ?", [$c])->getRow();
                if ($row) { $colMax = $c; break; }
            }

            $selectMin = $colMin ? "MIN(a.`$colMin`)" : "0";
            $selectMax = $colMax ? "MIN(a.`$colMax`)" : "0";
            $orderExpr = $colMin ? " (SUM(s.cantidad) / NULLIF(MIN(a.`$colMin`),0)) ASC "
                : " SUM(s.cantidad) ASC ";

            $inv = $db->query("
                SELECT a.nombre AS item,
                       SUM(s.cantidad) AS stock_actual,
                       $selectMin AS stock_min,
                       $selectMax AS stock_max
                FROM articulo a
                JOIN stock s ON s.articuloId = a.id
                GROUP BY a.id, a.nombre
                ORDER BY $orderExpr
                LIMIT 6
            ")->getResultArray();

            $i_labels = array_column($inv, 'item');
            $i_actual = array_map('floatval', array_column($inv, 'stock_actual'));
            $i_min    = array_map('floatval', array_column($inv, 'stock_min'));
            $i_max    = array_map('floatval', array_column($inv, 'stock_max'));
        } catch (\Throwable $e) {
            $errors[] = 'Inventario: ' . $e->getMessage();
        }

        /* =========================================================
         * CALIDAD · 30 días → fallback 90
         * ========================================================= */
        $c_labels = $c_tasa = [];
        $calidadRangeUsed = $range;
        $calidadSource = 'unknown';

        try {
            $hasDefTable = (bool)$db->query("
                SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='inspeccion_defecto'
            ")->getRow();
            $hasDefCol = false;
            if ($hasDefTable) {
                $hasDefCol = (bool)$db->query("
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='inspeccion_defecto' AND COLUMN_NAME='inspeccionId'
                ")->getRow();
            }

            $tryRanges = [$range, 90];
            foreach ($tryRanges as $days) {
                $rows = [];

                if ($hasDefTable && $hasDefCol) {
                    $rows = $db->query("
                        SELECT DATE(i.fecha) AS dia,
                               COUNT(*) AS total,
                               COUNT(DISTINCT idf.inspeccionId) AS defectivas
                        FROM inspeccion i
                        LEFT JOIN inspeccion_defecto idf ON idf.inspeccionId = i.id
                        WHERE i.fecha >= CURDATE() - INTERVAL {$days} DAY
                        $inspFilter
                        GROUP BY DATE(i.fecha)
                        ORDER BY dia
                    ")->getResultArray();

                    if (!empty($rows)) {
                        $c_labels = array_column($rows, 'dia');
                        $tot      = array_map('floatval', array_column($rows, 'total'));
                        $def      = array_map('floatval', array_column($rows, 'defectivas'));
                        $c_tasa   = [];
                        for ($k=0; $k<count($rows); $k++) {
                            $c_tasa[] = $tot[$k] > 0 ? round(100.0 * $def[$k] / $tot[$k], 2) : 0.0;
                        }
                        $calidadRangeUsed = $days;
                        $calidadSource    = 'inspeccion_defecto';
                        break;
                    }
                }

                $rows = $db->query("
                    SELECT DATE(fecha) AS dia,
                           ROUND(
                             100 * SUM(
                               CASE
                                 WHEN LOWER(resultado) LIKE '%defec%' OR LOWER(resultado) LIKE '%rech%'
                                   OR LOWER(resultado) LIKE '%no conform%' OR LOWER(resultado) LIKE '%nc%'
                                 THEN 1 ELSE 0 END
                             ) / NULLIF(COUNT(*),0)
                           , 2) AS tasa
                    FROM inspeccion i
                    WHERE fecha >= CURDATE() - INTERVAL {$days} DAY
                    $inspFilter
                    GROUP BY DATE(fecha)
                    ORDER BY DATE(fecha)
                ")->getResultArray();

                if (!empty($rows)) {
                    $c_labels = array_column($rows, 'dia');
                    $c_tasa   = array_map('floatval', array_column($rows, 'tasa'));
                    $calidadRangeUsed = $days;
                    $calidadSource    = 'resultado_like';
                    break;
                }
            }

            if (empty($c_labels)) {
                $calidadSource = 'zero_fill';
                $calidadRangeUsed = $range;
                $c_labels = [];
                for ($i = $range-1; $i >= 0; $i--) {
                    $c_labels[] = date('Y-m-d', strtotime("-{$i} day"));
                }
                $c_tasa = array_fill(0, $range, 0.0);
            }
        } catch (\Throwable $e) {
            $errors[] = 'Calidad: ' . $e->getMessage();
        }

        /* =========================================================
         * LOGÍSTICA · 1 → 7 → 30 → 90 → 365 → total
         * ========================================================= */
        $l_labels = $l_data = []; $logRangeUsed = null;
        try {
            foreach ([1,7,30,90,365,0] as $days) {
                $sql = "SELECT estatus, COUNT(*) AS total FROM orden_compra ";
                $sql .= $days > 0 ? "WHERE fecha >= CURDATE() - INTERVAL {$days} DAY " : "";
                $sql .= "GROUP BY estatus ORDER BY estatus";
                $rows = $db->query($sql)->getResultArray();

                $suma = array_sum(array_map(fn($r)=>(int)$r['total'], $rows));
                if ($suma > 0) {
                    $l_labels = array_column($rows, 'estatus');
                    $l_data   = array_map('intval', array_column($rows, 'total'));
                    $logRangeUsed = $days ?: 'all';
                    break;
                }
            }
            if ($logRangeUsed === null) { $logRangeUsed = 1; }
        } catch (\Throwable $e) {
            $errors[] = 'Logística: ' . $e->getMessage();
        }

        /* =========================================================
         * KPIs
         * ========================================================= */
        $k1=$k2=$k4=0; $k3=0.0;
        try {
            $sqlK1 = "SELECT COUNT(*) c
                FROM orden_produccion op
                WHERE ((op.fechaFinPlan IS NULL OR op.fechaFinPlan = '0000-00-00')
                   OR op.status IN ('Planificada','En proceso','Pausada'))
                   $opFilter";
            $k1 = (int) ($db->query($sqlK1)->getRow('c') ?? 0);

            $sqlK2 = "SELECT COALESCE(SUM(cantidadPlan),0) c
                FROM orden_produccion op
                WHERE ((op.fechaFinPlan IS NULL OR op.fechaFinPlan = '0000-00-00')
                   OR op.status IN ('Planificada','En proceso','Pausada'))
                   $opFilter";
            $k2 = (int) ($db->query($sqlK2)->getRow('c') ?? 0);

            if (!empty($i_labels)) {
                if ($colMin) {
                    $k4 = (int) ($db->query("
                        SELECT COUNT(*) c FROM (
                          SELECT a.id,
                                 SUM(s.cantidad) AS actual,
                                 MIN(a.`$colMin`) AS minimo
                          FROM articulo a
                          LEFT JOIN stock s ON s.articuloId = a.id
                          GROUP BY a.id
                        ) t
                        WHERE t.actual < t.minimo
                    ")->getRow('c') ?? 0);
                }
            }

            $k3 = !empty($c_tasa) ? round(array_sum($c_tasa)/max(count($c_tasa),1), 1) : 0.0;
        } catch (\Throwable $e) {
            $errors[] = 'KPIs: ' . $e->getMessage();
        }

        /* =========================================================
         * Respuesta JSON
         * ========================================================= */
        return $this->response->setJSON([
            'kpis' => [
                'ordenes_activas' => $k1,
                'wip_cantidad'    => $k2,
                'tasa_defectos'   => $k3,
                'stock_critico'   => $k4,
            ],
            'produccion' => [
                'labels'      => $p_labels,
                'datasets' => [
                    ['data' => $p_completadas],
                    ['data' => $p_activas]
                ],
                'meta'        => ['window' => 'last_6_weeks_with_data_or_oc_date'],
            ],
            'inventario' => [
                'labels' => $i_labels,
                'datasets' => [
                    ['data' => $i_actual]
                ],
                'min'    => $i_min,
                'max'    => $i_max,
            ],
            'calidad' => [
                'labels' => $c_labels,
                'tasa'   => $c_tasa,
                'meta'   => ['rangeDays' => $calidadRangeUsed, 'source' => $calidadSource],
            ],
            'logistica' => [
                'labels' => $l_labels,
                'data'   => $l_data,
                'meta'   => ['rangeDays' => $logRangeUsed],
            ],
            'errors' => $errors,
            'debug' => [
                'maquiladora_id' => $maquiladoraId,
                'user_id' => session()->get('user_id'),
                'sql_k1' => $sqlK1
            ]
        ]);
    }
}
