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

        $service = new \App\Services\DashboardService();
        $userId = session()->get('user_id');

        // Obtener datos del servicio
        $kpis = $service->getKPIs();
        $notifications = $service->getNotifications($userId);

        // Datos para gráficas
        $charts = [
            'produccion' => $service->getProduccionStats(),
            'calidad' => $service->getCalidadStats(),
            'inventario' => $service->getInventarioStats(),
        ];

        return view('modulos/dashboard', [
            'title' => 'Dashboard Principal',
            'kpis' => $kpis,
            'notifications' => $notifications,
            'charts' => $charts,
            'userEmail' => session()->get('user_email') ?? 'usuario@fabrica.com',
            'userName' => session()->get('user_name') ?? 'Usuario',
            'userRole' => session()->get('user_role') ?? 'empleado'
        ]);
    }
    public function api()
    {
        $service = new \App\Services\DashboardService();
        $userId = session()->get('user_id');
        $range = $this->request->getGet('range') ?? 30;

        $data = [
            'kpis' => $service->getKPIs(),
            'produccion' => $service->getProduccionStats(), // Could accept range
            'calidad' => $service->getCalidadStats($range),
            'inventario' => $service->getInventarioStats(),
            'logistica' => $service->getLogisticaStats(),
            'notifications' => $service->getNotifications($userId)
        ];

        return $this->response->setJSON($data);
    }
}
