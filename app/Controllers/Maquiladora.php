<?php

namespace App\Controllers;

use App\Models\MaquiladoraModel;

class Maquiladora extends BaseController
{
    protected $maquiladoraModel;

    public function __construct()
    {
        $this->maquiladoraModel = new MaquiladoraModel();
    }

    public function index()
    {
        $maquiladoraId = session()->get('maquiladora_id');
        
        if (!$maquiladoraId) {
            return redirect()->to('login')->with('error', 'No se encontró información de la maquiladora');
        }

        $maquiladora = $this->maquiladoraModel->find($maquiladoraId);
        
        if (!$maquiladora) {
            // Intentar con mayúsculas si no se encuentra
            $db = \Config\Database::connect();
            $maquiladora = $db->table('Maquiladora')
                ->where('idmaquiladora', $maquiladoraId)
                ->get()
                ->getRowArray();
                
            if (!$maquiladora) {
                return redirect()->back()->with('error', 'No se encontró la información de la maquiladora');
            }
        }

        $data = [
            'title' => 'Información de la Maquiladora',
            'maquiladora' => $maquiladora,
            'notifCount' => 0
        ];
        
        // Preparar logo para visualización
        if (!empty($maquiladora['logo'])) {
            $data['logo_base64'] = base64_encode($maquiladora['logo']);
        } else {
            $data['logo_base64'] = null;
        }

        return view('modulos/maquiladora', $data);
    }

    public function update()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Solicitud inválida']);
        }

        $id = $this->request->getPost('idmaquiladora');
        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID no proporcionado']);
        }

        $data = [
            'Nombre_Maquila' => $this->request->getPost('Nombre_Maquila'),
            'Dueno'          => $this->request->getPost('Dueno'),
            'Telefono'       => $this->request->getPost('Telefono'),
            'Correo'         => $this->request->getPost('Correo'),
            'Domicilio'      => $this->request->getPost('Domicilio'),
            'tipo'           => $this->request->getPost('tipo'),
            // NO incluimos 'status' para protegerlo
        ];

        // Validar 'tipo'
        $allowedTipos = ['empresa', 'sucursal', 'empresa externa'];
        if (!in_array($data['tipo'], $allowedTipos)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tipo de maquiladora inválido']);
        }

        // Procesar Logo
        $file = $this->request->getFile('logo_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validar tamaño (2MB)
            if ($file->getSizeByUnit('mb') > 2) {
                 return $this->response->setJSON(['success' => false, 'message' => 'El logo excede el tamaño máximo de 2MB']);
            }

            // Validar tipo de imagen
            $mime = $file->getMimeType();
            if (strpos($mime, 'image/') === 0) {
                $binary = file_get_contents($file->getTempName());
                $data['logo'] = $binary;
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'El archivo debe ser una imagen']);
            }
        }

        try {
            $this->maquiladoraModel->update($id, $data);
            return $this->response->setJSON(['success' => true, 'message' => 'Actualizado correctamente']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
}