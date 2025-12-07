<?php
namespace App\Controllers;

use App\Models\WipModel;

class Wip extends BaseController
{
    public function index()
    {
        if (!can('menu.wip')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $m = new WipModel();
        return view('modulos/wip', [
            'title' => 'Trabajo en Proceso',
            'rows'  => $m->getDatosDiseno(),
        ]);
    }
}
