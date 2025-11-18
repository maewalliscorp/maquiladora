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

        return view('modulos/maquiladora', $data);
    }
}