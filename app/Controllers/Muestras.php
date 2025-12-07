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
        if (!can('menu.muestras')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $maquiladoraId = session()->get('maquiladora_id');
        $data = [
            'title' => 'Gestión de Muestras',
            'muestras' => $this->muestraModel->getMuestrasConPrototipo($maquiladoraId),
            'muestrasDecision' => $this->muestraModel->getMuestrasConDecision($maquiladoraId)
        ];

        return view('modulos/muestras', $data);
    }

    // Método para la API de DataTables
    public function data()
    {
        if (!can('menu.muestras')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }
        
        $maquiladoraId = session()->get('maquiladora_id');
        $data = $this->muestraModel->getMuestrasConPrototipo($maquiladoraId);

        return $this->response->setJSON([
            'draw' => (int)($this->request->getPost('draw') ?? 1),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }
    public function evaluar($id = null)
    {
        if (!can('menu.muestras')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
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

    public function guardar()
    {
        if (!can('menu.muestras')) {
            return $this->response->setStatusCode(403)->setJSON(['ok'=>false, 'message'=>'Acceso denegado']);
        }
        
        $db = \Config\Database::connect();
        $id = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false, 'message'=>'ID inválido']);
        }
        $clienteId = $this->request->getPost('clienteId');
        $prototipoId = $this->request->getPost('prototipoId');
        $fecha = $this->request->getPost('fecha');
        $solicitadaPor = trim((string)$this->request->getPost('solicitadaPor'));
        $fechaSolicitud = $this->request->getPost('fechaSolicitud');
        $decision = $this->request->getPost('decision');
        $estado = $this->request->getPost('estado');
        $comentarios = $this->request->getPost('comentarios');
        $observaciones = $this->request->getPost('observaciones');

        $db->transStart();
        try {
            $mData = [];
            if ($prototipoId !== null && $prototipoId !== '') { $mData['prototipoId'] = (int)$prototipoId; }
            if ($solicitadaPor !== '') { $mData['solicitadaPor'] = $solicitadaPor; }
            if ($fechaSolicitud !== null && $fechaSolicitud !== '') { $mData['fechaSolicitud'] = $fechaSolicitud; }
            if ($estado !== null && $estado !== '') { $mData['estado'] = $estado; }
            if ($observaciones !== null) { $mData['observaciones'] = $observaciones; }
            if ($mData) { $db->table('muestra')->where('id', $id)->update($mData); }

            $rowA = null;
            try { $rowA = $db->table('aprobacion_muestra')->where('muestraId', $id)->get()->getRowArray(); } catch (\Throwable $e) { $rowA = null; }
            $aData = [
                'muestraId' => $id,
                'clienteId' => ($clienteId !== null && $clienteId !== '') ? (int)$clienteId : null,
                'fecha' => ($fecha !== null && $fecha !== '') ? $fecha : null,
                'decision' => $decision !== '' ? $decision : null,
                'comentarios' => $comentarios !== '' ? $comentarios : null,
            ];
            if ($rowA) {
                $db->table('aprobacion_muestra')->where('muestraId', $id)->update($aData);
            } else {
                $db->table('aprobacion_muestra')->insert($aData);
            }

            // Si la decisión y el estado son "Aprobada", actualizar diseno_version.aprobado = 1
            if ($decision === 'Aprobada' && $estado === 'Aprobada') {
                $rowP = $db->query("
                    SELECT p.disenoVersionId 
                    FROM muestra m 
                    JOIN prototipo p ON m.prototipoId = p.id 
                    WHERE m.id = ?
                ", [$id])->getRowArray();

                if ($rowP && !empty($rowP['disenoVersionId'])) {
                    $db->table('diseno_version')
                       ->where('id', $rowP['disenoVersionId'])
                       ->update(['aprobado' => 1]);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }
            return $this->response->setJSON(['ok'=>true, 'message'=>'Guardado']);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'message'=>'Error: '.$e->getMessage()]);
        }
    }

    // Devuelve archivos (blobs) del diseño asociado a la muestra (por su prototipo -> diseno_version)
    public function archivo($id = null)
    {
        if (!can('menu.muestras')) {
            return $this->response->setStatusCode(403)->setJSON(['ok'=>false, 'message'=>'Acceso denegado']);
        }
        
        $muestraId = (int)($id ?? 0);
        if ($muestraId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false, 'message'=>'ID inválido']);
        }

        $db = \Config\Database::connect();
        try {
            $row = $db->query(
                "SELECT dv.foto, dv.patron
                 FROM muestra m
                 JOIN prototipo p ON p.id = m.prototipoId
                 JOIN diseno_version dv ON dv.id = p.disenoVersionId
                 WHERE m.id = ?",
                [$muestraId]
            )->getRowArray();

            if (!$row) {
                return $this->response->setStatusCode(404)->setJSON(['ok'=>false, 'message'=>'No se encontraron archivos para la muestra']);
            }

            $fotoBlob = $row['foto'] ?? null;
            $patronBlob = $row['patron'] ?? null;

            $response = ['ok' => true];

            if ($fotoBlob) {
                $mime = $this->getMimeType($fotoBlob);
                $response['fotoBase64'] = 'data:' . $mime . ';base64,' . base64_encode($fotoBlob);
                $response['fotoMime'] = $mime;
            } else {
                $response['fotoBase64'] = null;
            }

            if ($patronBlob) {
                $mime = $this->getMimeType($patronBlob);
                $response['patronBase64'] = 'data:' . $mime . ';base64,' . base64_encode($patronBlob);
                $response['patronMime'] = $mime;
            } else {
                $response['patronBase64'] = null;
            }

            return $this->response->setJSON($response);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'message'=>'Error: '.$e->getMessage()]);
        }
    }

    private function getMimeType($data) {
        if (strncmp($data, "\x89PNG", 4) === 0) return 'image/png';
        if (strncmp($data, "\xFF\xD8", 2) === 0) return 'image/jpeg';
        if (strncmp($data, "GIF8", 4) === 0) return 'image/gif';
        if (strncmp($data, "%PDF", 4) === 0) return 'application/pdf';
        return 'application/octet-stream';
    }
}
