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
            'muestras' => $this->muestraModel->getMuestrasConPrototipo()
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
    public function evaluar($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Solicitud no válida'
            ])->setStatusCode(400);
        }

        try {
            $evaluacion = $this->muestraModel->getEvaluacionMuestra($id);

            if (empty($evaluacion)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Muestra no encontrada'
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $evaluacion
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en Muestras::evaluar - ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener los datos de la muestra'
            ])->setStatusCode(500);
        }
    }
}
