<?php
namespace App\Controllers;

use App\Controllers\BaseController;

class MttoProgramacion extends BaseController
{
    // opcional: redirige si alguien invoca el controller sin método
    public function index()
    {
        return redirect()->to(site_url('mtto/calendario'));
    }

    public function calendario()
    {
        return view('modulos/mtto_calendario', ['title' => 'Calendario de Mantenimiento']);
    }

    public function lista()
    {
        $items = [
            ['id'=>1,'maquina'=>'Cortadora 1','tarea'=>'Cambio de aceite','fecha'=>'2025-11-20','responsable'=>'Juan'],
            ['id'=>2,'maquina'=>'Bordadora 3','tarea'=>'Ajuste agujas','fecha'=>'2025-11-22','responsable'=>'María'],
            ['id'=>3,'maquina'=>'Plana 7','tarea'=>'Limpieza general','fecha'=>'2025-11-23','responsable'=>'Luis'],
        ];
        return view('modulos/mtto_programacion', [
            'title' => 'Programación de Mantenimiento',
            'items' => $items
        ]);
    }

    public function apiEventos()
    {
        $eventos = [
            ['id'=>1,'title'=>'Mtto Cortadora 1','start'=>'2025-11-20'],
            ['id'=>2,'title'=>'Inspección Bordadora','start'=>'2025-11-22','end'=>'2025-11-23'],
            ['id'=>3,'title'=>'Limpieza Plana 7','start'=>'2025-11-23T09:00:00','end'=>'2025-11-23T12:00:00'],
        ];
        return $this->response->setJSON($eventos);
    }
}
