<?php

namespace App\Controllers;

use App\Models\MuestraModel;

class Muestras extends BaseController
{
    protected $muestraModel;

    public function __construct()
    {
        $this->muestraModel = new MuestraModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Gestión de Muestras',
            'muestras' => $this->muestraModel->getMuestrasConPrototipo(),
            'muestrasDecision' => $this->muestraModel->getMuestrasConDecision()
        ];

        return view('modulos/muestras', $data);
    }

    // Método para la API de DataTables
    public function data()
    {
        $data = $this->muestraModel->getMuestrasConPrototipo();

        return $this->response->setJSON([
            'draw' => (int)($this->request->getPost('draw') ?? 1),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }
    public function evaluar($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['error' => 'ID de muestra no proporcionado'])->setStatusCode(400);
        }

        // Cargar el modelo de Muestras
        $muestraModel = new \App\Models\MuestraModel();
        $muestra = $muestraModel->find($id);

        if (!$muestra) {
            return $this->response->setJSON(['error' => 'Muestra no encontrada'])->setStatusCode(404);
        }

        // Devuelve los datos en formato JSON
        return $this->response->setJSON([
            'evaluacion' => [
                'muestraId' => $muestra['id'],
                'estado' => $muestra['estado'] ?? 'Pendiente',
                // Agrega aquí los demás campos que necesites
            ]
        ]);
    }
}
