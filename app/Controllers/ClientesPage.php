<?php

namespace App\Controllers;

class ClientesPage extends BaseController
{
    public function index()
    {
        return view('modulos/agregar_cliente', [
            'title' => 'Clientes',
        ]);
    }
}
