<?php

namespace App\Controllers;

class   Modulo3 extends BaseController
{
    public function dashboard()
    {
        $kpis = [
            ['label'=>'Órdenes Activas','value'=>8],
            ['label'=>'WIP (%)','value'=>62],
            ['label'=>'Incidencias Hoy','value'=>3],
            ['label'=>'Órdenes Completadas','value'=>21],
        ];
        return view('modulo3/dashboard', [
            'title' => 'Módulo 3 · Dashboard',
            'kpis'  => $kpis,
            'notifCount' => 3,
            'userEmail'  => 'admin@fabrica.com',
        ]);
    }

    public function ordenes()     { return view('modulo3/ordenes',     ['title'=>'Órdenes']); }
    public function wip()         { return view('modulo3/wip',         ['title'=>'WIP']); }
    public function incidencias() { return view('modulo3/incidencias', ['title'=>'Incidencias']); }
    public function reportes()    { return view('modulo3/reportes',    ['title'=>'Reportes']); }

    public function notificaciones()
    {
        $items = [
            ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Módulo: Confección (WIP)'],
            ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Módulo: Prototipos'],
            ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Módulo: Prototipos'],
        ];
        return view('modulo3/notificaciones', [
            'title'=>'Notificaciones',
            'items'=>$items,
            'notifCount'=>count($items),
            'userEmail'=>'admin@fabrica.com'
        ]);
    }
}
