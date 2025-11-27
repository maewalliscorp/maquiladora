<?php
namespace App\Services;

use Config\Database;

class DashboardService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getKPIs()
    {
        try {
            $builder = $this->db->table('orden_produccion');

            // 1. Órdenes Activas (No completadas/finalizadas/cerradas)
            $activas = $builder->whereNotIn('status', ['Completada', 'Finalizada', 'Cerrada'])->countAllResults(false); // false to not reset query

            // 2. WIP (Work In Process) - Suma de cantidadPlan
            $builder->resetQuery();
            $wip = $builder->selectSum('cantidadPlan')
                ->whereNotIn('status', ['Completada', 'Finalizada', 'Cerrada'])
                ->get()->getRow()->cantidadPlan ?? 0;

            // 3. Tasa de Defectos (Últimos 30 días)
            $defectosQuery = $this->db->table('inspeccion')
                ->select('COUNT(*) as total, SUM(CASE WHEN resultado LIKE "%defecto%" OR resultado LIKE "%rechazo%" THEN 1 ELSE 0 END) as defectuosos')
                ->where('fecha >=', date('Y-m-d', strtotime('-30 days')))
                ->get()->getRow();

            $tasaDefectos = 0;
            if ($defectosQuery && $defectosQuery->total > 0) {
                $tasaDefectos = ($defectosQuery->defectuosos / $defectosQuery->total) * 100;
            }

            // 4. Stock Crítico
            $stockCritico = 0;
            try {
                $stockCritico = $this->db->query("
                    SELECT COUNT(*) as c 
                    FROM articulo a 
                    JOIN stock s ON s.articuloId = a.id 
                    WHERE s.cantidad < a.stock_min
                ")->getRow()->c ?? 0;
            } catch (\Throwable $e) {
                $stockCritico = 0;
            }

            return [
                'ordenes_activas' => $activas,
                'wip_cantidad' => $wip,
                'tasa_defectos' => round($tasaDefectos, 2),
                'stock_critico' => $stockCritico
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in getKPIs: ' . $e->getMessage());
            return [
                'ordenes_activas' => 0,
                'wip_cantidad' => 0,
                'tasa_defectos' => 0,
                'stock_critico' => 0
            ];
        }
    }

    public function getProduccionStats($weeks = 6)
    {
        try {
            $sql = "
                SELECT 
                    YEARWEEK(fechaFinPlan, 1) as semana,
                    COUNT(CASE WHEN status IN ('Completada', 'Finalizada') THEN 1 END) as completadas,
                    COUNT(CASE WHEN status NOT IN ('Completada', 'Finalizada', 'Cerrada') THEN 1 END) as pendientes
                FROM orden_produccion
                WHERE fechaFinPlan >= DATE_SUB(NOW(), INTERVAL ? WEEK)
                GROUP BY YEARWEEK(fechaFinPlan, 1)
                ORDER BY semana ASC
            ";

            $result = $this->db->query($sql, [$weeks])->getResultArray();

            $labels = [];
            $dataCompletadas = [];
            $dataPendientes = [];

            foreach ($result as $row) {
                $weekNum = substr($row['semana'], 4);
                $labels[] = 'Sem ' . $weekNum;
                $dataCompletadas[] = (int) $row['completadas'];
                $dataPendientes[] = (int) $row['pendientes'];
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Completadas',
                        'data' => $dataCompletadas,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    ],
                    [
                        'label' => 'Pendientes',
                        'data' => $dataPendientes,
                        'backgroundColor' => 'rgba(255, 206, 86, 0.6)',
                    ]
                ]
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in getProduccionStats: ' . $e->getMessage());
            return ['labels' => [], 'datasets' => []];
        }
    }

    public function getCalidadStats($days = 30)
    {
        try {
            $sql = "
                SELECT 
                    DATE(fecha) as fecha,
                    COUNT(*) as total_inspecciones,
                    SUM(CASE WHEN resultado LIKE '%defecto%' OR resultado LIKE '%rechazo%' THEN 1 ELSE 0 END) as defectuosas
                FROM inspeccion
                WHERE fecha >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(fecha)
                ORDER BY fecha ASC
            ";

            $result = $this->db->query($sql, [$days])->getResultArray();

            $labels = [];
            $dataTasa = [];

            foreach ($result as $row) {
                $labels[] = date('d/m', strtotime($row['fecha']));
                $tasa = $row['total_inspecciones'] > 0 ? ($row['defectuosas'] / $row['total_inspecciones']) * 100 : 0;
                $dataTasa[] = round($tasa, 2);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => '% Defectos',
                        'data' => $dataTasa,
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'fill' => true,
                        'tension' => 0.4
                    ]
                ]
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in getCalidadStats: ' . $e->getMessage());
            return ['labels' => [], 'datasets' => []];
        }
    }

    public function getInventarioStats()
    {
        try {
            // Top 5 artículos con menor stock relativo (stock / stock_min)
            try {
                $sql = "
                    SELECT a.nombre, s.cantidad, a.stock_min
                    FROM articulo a
                    JOIN stock s ON s.articuloId = a.id
                    WHERE a.stock_min > 0
                    ORDER BY (s.cantidad / a.stock_min) ASC
                    LIMIT 5
                ";
                $result = $this->db->query($sql)->getResultArray();
            } catch (\Throwable $e) {
                // Fallback: Top 5 con menos stock
                $sql = "
                    SELECT a.nombre, SUM(s.cantidad) as cantidad
                    FROM articulo a
                    JOIN stock s ON s.articuloId = a.id
                    GROUP BY a.id, a.nombre
                    ORDER BY cantidad ASC
                    LIMIT 5
                ";
                $result = $this->db->query($sql)->getResultArray();
            }

            $labels = [];
            $data = [];

            foreach ($result as $row) {
                $labels[] = substr($row['nombre'], 0, 15) . '...';
                $data[] = (float) $row['cantidad'];
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Stock Actual',
                        'data' => $data,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    ]
                ]
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in getInventarioStats: ' . $e->getMessage());
            return ['labels' => [], 'datasets' => []];
        }
    }
    public function getNotifications($userId, $limit = 5)
    {
        // Notificaciones del usuario o generales
        $sql = "
            SELECT nivel, titulo, sub, mensaje, color, created_at
            FROM notificaciones 
            ORDER BY created_at DESC 
            LIMIT ?
        ";

        $result = $this->db->query($sql, [$limit])->getResultArray();

        // Si no hay notificaciones, devolver array vacío (o simuladas si es demo)
        if (empty($result)) {
            return [];
        }

        return $result;
    }
    public function getLogisticaStats()
    {
        try {
            // Órdenes de compra por estado
            // CORRECCIÓN: Usar 'estatus' en lugar de 'status' para orden_compra

            $sql = "
                SELECT 
                    estatus, 
                    COUNT(*) as total
                FROM orden_compra
                GROUP BY estatus
            ";

            $result = $this->db->query($sql)->getResultArray();

            $labels = [];
            $data = [];

            // Map results
            $map = [];
            foreach ($result as $row) {
                $map[$row['estatus']] = (int) $row['total'];
            }

            // Ensure we have all keys for the chart
            $statuses = ['Pendiente', 'En tránsito', 'Entregado', 'Cancelado'];

            foreach ($statuses as $s) {
                $labels[] = $s;
                $data[] = $map[$s] ?? 0;
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Error in getLogisticaStats: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }
}
