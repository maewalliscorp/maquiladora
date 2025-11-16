<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ApiOptimized extends BaseController
{
    /**
     * API optimizada para dashboard con cache
     */
    public function dashboard()
    {
        $cacheKey = 'api_dashboard_' . session()->get('user_id') . '_' . ($this->request->getGet('range') ?? 30);
        $cachedData = cache($cacheKey);
        
        if ($cachedData !== null) {
            return $this->response->setJSON($cachedData);
        }
        
        $db     = \Config\Database::connect();
        $range  = (int) ($this->request->getGet('range') ?? 30);
        $errors = [];
        $data = [];

        // PRODUCCIÓN - Query optimizada con índices sugeridos
        try {
            $p_labels = $p_activas = $p_completadas = [];
            $wrows = $db->query("
                SELECT yw, CONCAT('S-', LPAD(week_no,2,'0')) AS label
                FROM (
                  SELECT
                    YEARWEEK(COALESCE(op.fechaInicioPlan, op.fechaFinPlan, oc.fecha), 1) AS yw,
                    WEEK(COALESCE(op.fechaInicioPlan, op.fechaFinPlan, oc.fecha), 1) AS week_no
                  FROM orden_produccion op
                  LEFT JOIN orden_compra oc ON oc.id = op.ordenCompraId
                  WHERE COALESCE(op.fechaInicioPlan, op.fechaFinPlan, oc.fecha) IS NOT NULL
                ) t
                WHERE yw IS NOT NULL
                GROUP BY yw, week_no
                ORDER BY yw DESC
                LIMIT 6
            ")->getResultArray();

            if (empty($wrows)) {
                for ($i = 5; $i >= 0; $i--) {
                    $ts = strtotime("-{$i} week");
                    $p_labels[] = 'S-' . str_pad(date('W', $ts), 2, '0', STR_PAD_LEFT);
                }
                $p_activas = $p_completadas = array_fill(0, 6, 0);
            } else {
                $wrows = array_reverse($wrows);
                $ywList = implode(',', array_map(fn($r) => (int)$r['yw'], $wrows));

                // Queries optimizadas con IN en lugar de múltiples consultas
                $results = $db->query("
                    SELECT 
                        YEARWEEK(COALESCE(op.fechaInicioPlan, oc.fecha), 1) AS yw, 
                        SUM(CASE WHEN op.fechaFinPlan IS NULL OR op.fechaFinPlan = '0000-00-00' OR op.status NOT IN ('Completada','Finalizada','Cerrada') THEN 1 ELSE 0 END) as activas,
                        SUM(CASE WHEN op.status IN ('Completada','Finalizada','Cerrada') THEN 1 ELSE 0 END) as completadas
                    FROM orden_produccion op
                    LEFT JOIN orden_compra oc ON oc.id = op.ordenCompraId
                    WHERE YEARWEEK(COALESCE(op.fechaInicioPlan, oc.fecha), 1) IN ($ywList)
                    GROUP BY 1
                ")->getResultArray();

                $mapAct = [];
                $mapComp = [];
                foreach ($results as $r) {
                    $mapAct[(int)$r['yw']] = (int)$r['activas'];
                    $mapComp[(int)$r['yw']] = (int)$r['completadas'];
                }

                foreach ($wrows as $wr) {
                    $yw = (int)$wr['yw'];
                    $p_labels[] = $wr['label'];
                    $p_activas[] = $mapAct[$yw] ?? 0;
                    $p_completadas[] = $mapComp[$yw] ?? 0;
                }
            }

            $data['produccion'] = [
                'labels' => $p_labels,
                'activas' => $p_activas,
                'completadas' => $p_completadas
            ];
        } catch (\Throwable $e) {
            $errors[] = 'Producción: ' . $e->getMessage();
            $data['produccion'] = ['labels' => [], 'activas' => [], 'completadas' => []];
        }

        // INVENTARIO - Query optimizada
        try {
            $i_labels = $i_actual = $i_min = $i_max = [];
            $colMin = null; 
            $colMax = null;

            // Verificar columnas una sola vez
            $columns = $db->query("
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'articulo' 
                  AND COLUMN_NAME IN ('minimo','stock_min','stockMin','maximo','stock_max','stockMax')
            ")->getResultArray();

            foreach ($columns as $col) {
                $name = $col['COLUMN_NAME'];
                if (in_array($name, ['minimo','stock_min','stockMin']) && !$colMin) {
                    $colMin = $name;
                }
                if (in_array($name, ['maximo','stock_max','stockMax']) && !$colMax) {
                    $colMax = $name;
                }
            }

            $selectMin = $colMin ? "MIN(a.`$colMin`)" : "0";
            $selectMax = $colMax ? "MIN(a.`$colMax`)" : "0";
            $orderExpr = $colMin ? " (SUM(s.cantidad) / NULLIF(MIN(a.`$colMin`),0)) ASC " : " SUM(s.cantidad) ASC ";

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

            $data['inventario'] = [
                'labels' => $i_labels,
                'actual' => $i_actual,
                'min' => $i_min,
                'max' => $i_max
            ];
        } catch (\Throwable $e) {
            $errors[] = 'Inventario: ' . $e->getMessage();
            $data['inventario'] = ['labels' => [], 'actual' => [], 'min' => [], 'max' => []];
        }

        // Cache por 3 minutos
        cache()->save($cacheKey, $data, 180);

        return $this->response->setJSON([
            'data' => $data,
            'errors' => $errors,
            'cached' => false
        ]);
    }
}
