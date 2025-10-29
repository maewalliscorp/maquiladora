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

    public function guardar()
    {
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

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }
            return $this->response->setJSON(['ok'=>true, 'message'=>'Guardado']);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'message'=>'Error: '.$e->getMessage()]);
        }
    }

    // Devuelve URLs de archivos del diseño asociado a la muestra (por su prototipo -> diseno_version)
    public function archivo($id = null)
    {
        $muestraId = (int)($id ?? 0);
        if ($muestraId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false, 'message'=>'ID inválido']);
        }

        $db = \Config\Database::connect();
        try {
            $row = $db->query(
                "SELECT dv.archivoCadUrl, dv.archivoPatronUrl
                 FROM muestra m
                 JOIN prototipo p ON p.id = m.prototipoId
                 JOIN diseno_version dv ON dv.id = p.disenoVersionId
                 WHERE m.id = ?",
                [$muestraId]
            )->getRowArray();

            if (!$row) {
                return $this->response->setStatusCode(404)->setJSON(['ok'=>false, 'message'=>'No se encontraron archivos para la muestra']);
            }

            $cad = $row['archivoCadUrl'] ?? null;
            $pat = $row['archivoPatronUrl'] ?? null;

            $toAbs = static function ($rel) {
                if (!$rel) return null;
                // Si ya es absoluta, devolver tal cual
                if (preg_match('/^https?:\/\//i', $rel)) return $rel;
                return base_url(trim($rel, '/'));
            };

            return $this->response->setJSON([
                'ok' => true,
                'cadUrl' => $toAbs($cad),
                'patronUrl' => $toAbs($pat),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'message'=>'Error: '.$e->getMessage()]);
        }
    }
}
