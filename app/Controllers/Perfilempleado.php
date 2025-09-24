<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PerfilEmpleado extends Controller
{
    public function index()
    {
        // Aquí iría consulta a la BD
        // $empleado = $this->EmpleadoModel->find($id);

        // Si no hay datos en la BD, enviamos null
        $empleado = null;

        return view('perfilempleado', ['empleado' => $empleado]);
    }
}
