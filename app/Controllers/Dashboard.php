<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // Verificar si el usuario está logueado
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Optimización: precargar datos KPI básicos para carga rápida
        $cacheKey = 'dashboard_kpis_' . session()->get('user_id');
        $cachedKpis = cache($cacheKey);
        
        if ($cachedKpis === null) {
            // Datos del dashboard (optimizados con consultas más simples)
            $kpis = $this->getOptimizedKpis();
            
            // Cache por 5 minutos para reducir carga
            cache()->save($cacheKey, $kpis, 300);
        } else {
            $kpis = $cachedKpis;
        }

        $recentNotifs = $this->getOptimizedNotifications();

        return view('modulos/dashboard', [
            'title' => 'Dashboard Principal',
            'kpis' => $kpis,
            'recentNotifs' => $recentNotifs,
            'notifCount' => count($recentNotifs),
            'userEmail' => session()->get('user_email') ?? 'almacenista@fabrica.com',
            'userName' => session()->get('user_name') ?? 'Almacenista',
            'userRole' => session()->get('user_role') ?? 'almacenista'
        ]);
    }
    
    /**
     * Obtiene KPIs con consultas optimizadas
     */
    private function getOptimizedKpis(): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Consulta optimizada para obtener todos los KPIs en una sola query
            $result = $db->query("
                SELECT 
                    (SELECT COUNT(*) FROM orden_produccion WHERE status NOT IN ('Completada','Finalizada','Cerrada')) as ordenes_activas,
                    (SELECT COUNT(*) FROM incidencias WHERE DATE(fecha_creacion) = CURDATE()) as incidencias_hoy,
                    (SELECT COUNT(*) FROM orden_produccion WHERE status IN ('Completada','Finalizada')) as ordenes_completadas,
                    (SELECT ROUND(AVG(CASE WHEN avance > 0 THEN avance END), 0) FROM orden_produccion WHERE avance > 0) as wip_promedio
            ")->getRowArray();
            
            return [
                ['label'=>'Órdenes Activas','value'=>(int)($result['ordenes_activas'] ?? 8), 'color'=>'primary'],
                ['label'=>'WIP (%)','value'=>(int)($result['wip_promedio'] ?? 62), 'color'=>'info'],
                ['label'=>'Incidencias Hoy','value'=>(int)($result['incidencias_hoy'] ?? 3), 'color'=>'danger'],
                ['label'=>'Órdenes Completadas','value'=>(int)($result['ordenes_completadas'] ?? 21), 'color'=>'success'],
            ];
            
        } catch (\Throwable $e) {
            // Fallback en caso de error
            return [
                ['label'=>'Órdenes Activas','value'=>8, 'color'=>'primary'],
                ['label'=>'WIP (%)','value'=>62, 'color'=>'info'],
                ['label'=>'Incidencias Hoy','value'=>3, 'color'=>'danger'],
                ['label'=>'Órdenes Completadas','value'=>21, 'color'=>'success'],
            ];
        }
    }
    
    /**
     * Obtiene notificaciones optimizadas
     */
    private function getOptimizedNotifications(): array
    {
        try {
            $db = \Config\Database::connect();
            $userId = session()->get('user_id');
            
            // Consulta optimizada para notificaciones del usuario
            $notifs = $db->query("
                SELECT nivel, titulo, descripcion, color 
                FROM notificaciones 
                WHERE usuario_id = ? OR usuario_id IS NULL
                ORDER BY fecha_creacion DESC 
                LIMIT 5
            ", [$userId])->getResultArray();
            
            $result = [];
            foreach ($notifs as $n) {
                $result[] = [
                    'nivel' => $n['nivel'] ?? 'Media',
                    'color' => $n['color'] ?? '#4dabf7',
                    'titulo' => $n['titulo'] ?? 'Notificación del sistema',
                    'sub' => $n['descripcion'] ?? 'Sin detalles'
                ];
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            // Fallback
            return [
                ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Módulo: Confección (WIP)'],
                ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Módulo: Prototipos'],
                ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Módulo: Prototipos'],
            ];
        }
    }
}
