<?php
namespace App\Controllers;

use App\Models\WipModel;

class Wip extends BaseController
{
    public function index()
    {
        $m = new WipModel();
        return view('modulos/wip', [
            'title' => 'Trabajo en Proceso',
            'rows'  => $m->getDatosDiseno(),
        ]);
    }
}
