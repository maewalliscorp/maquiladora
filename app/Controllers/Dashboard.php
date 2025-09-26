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

        // Datos del dashboard
        $kpis = [
            ['label'=>'Órdenes Activas','value'=>8, 'color'=>'primary'],
            ['label'=>'WIP (%)','value'=>62, 'color'=>'info'],
            ['label'=>'Incidencias Hoy','value'=>3, 'color'=>'danger'],
            ['label'=>'Órdenes Completadas','value'=>21, 'color'=>'success'],
        ];

        $recentNotifs = [
            ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Módulo: Confección (WIP)'],
            ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Módulo: Prototipos'],
            ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Módulo: Prototipos'],
        ];

        return view('modulo3/dashboard', [
            'title' => 'Dashboard Principal',
            'kpis'  => $kpis,
            'notifCount' => count($recentNotifs),
            'userEmail'  => session()->get('user_email') ?? 'almacenista@fabrica.com',
            'userName' => session()->get('user_name') ?? 'Almacenista',
            'userRole' => session()->get('user_role') ?? 'almacenista',
            'recentNotifs' => $recentNotifs,
            'ordersInProcess' => 3,
            'overdue' => 1,
            'openIncidents' => 2
        ]);
    }
}
