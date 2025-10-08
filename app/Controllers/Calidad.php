<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use App\Models\ReprocesoModel;

class Calidad extends BaseController
{
    public function desperdicios()
    {
        $mR = new ReprocesoModel();

        // Desperdicios (accion = Desecho o Scrap)
        $desp = $mR->select('reproceso.id, reproceso.cantidad, reproceso.fecha,
                             inspeccion.ordenProduccionId AS op,
                             inspeccion.resultado, inspeccion.observaciones')
            ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
            ->whereIn('reproceso.accion',['Desecho','Scrap'])
            ->orderBy('reproceso.fecha','DESC')
            ->findAll();

        // Reprocesos (accion = Reproceso)
        $rep = $mR->select('reproceso.id, reproceso.cantidad AS pendientes, reproceso.fecha AS eta,
                            inspeccion.ordenProduccionId AS op,
                            inspeccion.observaciones AS tarea')
            ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
            ->where('reproceso.accion','Reproceso')
            ->orderBy('reproceso.fecha','ASC')
            ->findAll();

        return view('modulos/desperdicios', [
            'title' => 'Desperdicios & Reprocesos',
            'desp'  => $desp,
            'rep'   => $rep,
        ]);
    }

    // --------- Creación
    public function guardarDesecho()
    {
        $post = $this->request->getPost();
        $db   = \Config\Database::connect();
        $db->transStart();

        // 1) Crear/actualizar inspección base
        $mI = new InspeccionModel();
        $insData = [
            'ordenProduccionId' => (int)$post['op'],
            'puntoInspeccionId' => $post['puntoInspeccionId'] ?? null,
            'inspectorId'       => $post['inspectorId'] ?? null,
            'fecha'             => $post['fecha'],
            'resultado'         => 'rechazo',
            'observaciones'     => $post['motivo'] ?? null,
        ];
        $mI->insert($insData);
        $inspeccionId = $mI->getInsertID();

        // 2) Crear registro en reproceso con accion=“Desecho”
        $mR = new ReprocesoModel();
        $mR->insert([
            'inspeccionId' => $inspeccionId,
            'accion'       => 'Desecho',
            'cantidad'     => $post['cantidad'],
            'fecha'        => $post['fecha'],
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error','No se pudo registrar el desecho');
        }
        return redirect()->to('/calidad/desperdicios')->with('success','Desecho registrado');
    }

    public function guardarReproceso()
    {
        $post = $this->request->getPost();
        $db   = \Config\Database::connect();
        $db->transStart();

        $mI = new InspeccionModel();
        $mI->insert([
            'ordenProduccionId' => (int)$post['op'],
            'puntoInspeccionId' => $post['puntoInspeccionId'] ?? null,
            'inspectorId'       => $post['inspectorId'] ?? null,
            'fecha'             => $post['eta'],               // fecha objetivo
            'resultado'         => 'reproceso',
            'observaciones'     => $post['tarea'] ?? null,
        ]);
        $inspeccionId = $mI->getInsertID();

        $mR = new ReprocesoModel();
        $mR->insert([
            'inspeccionId' => $inspeccionId,
            'accion'       => 'Reproceso',
            'cantidad'     => $post['pendientes'],
            'fecha'        => $post['eta'],
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error','No se pudo registrar el reproceso');
        }
        return redirect()->to('/calidad/desperdicios')->with('success','Reproceso registrado');
    }

    // --------- Detalles (JSON para “Vista”)
    public function verDesecho($id)
    {
        $mR = new ReprocesoModel();
        $row = $mR->select('reproceso.*, inspeccion.ordenProduccionId AS op,
                            inspeccion.resultado, inspeccion.observaciones')
            ->join('inspeccion','inspeccion.id=reproceso.inspeccionId')
            ->where('reproceso.id',$id)
            ->first();
        if (!$row) return $this->response->setStatusCode(404)->setBody('No encontrado');
        return $this->response->setJSON($row);
    }

    public function verReproceso($id)
    {
        $mR = new ReprocesoModel();
        $row = $mR->select('reproceso.*, inspeccion.ordenProduccionId AS op,
                            inspeccion.observaciones AS tarea')
            ->join('inspeccion','inspeccion.id=reproceso.inspeccionId')
            ->where('reproceso.id',$id)
            ->first();
        if (!$row) return $this->response->setStatusCode(404)->setBody('No encontrado');
        return $this->response->setJSON($row);
    }

    // --------- Edición (actualiza inspeccion + reproceso)
    public function editarDesecho($id)
    {
        $post = $this->request->getPost();
        $mR   = new ReprocesoModel();
        $r    = $mR->find($id);
        if (!$r) return redirect()->back()->with('error','Registro no existe');

        $db = \Config\Database::connect();
        $db->transStart();

        // reproceso
        $mR->update($id, [
            'accion'   => 'Desecho',
            'cantidad' => $post['cantidad'],
            'fecha'    => $post['fecha'],
        ]);

        // inspeccion asociada
        $mI = new InspeccionModel();
        $mI->update($r['inspeccionId'], [
            'ordenProduccionId' => (int)$post['op'],
            'fecha'             => $post['fecha'],
            'resultado'         => 'rechazo',
            'observaciones'     => $post['motivo'] ?? null,
        ]);

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('error','No se pudo editar');
        }
        return redirect()->to('/calidad/desperdicios')->with('success','Actualizado');
    }

    public function editarReproceso($id)
    {
        $post = $this->request->getPost();
        $mR   = new ReprocesoModel();
        $r    = $mR->find($id);
        if (!$r) return redirect()->back()->with('error','Registro no existe');

        $db = \Config\Database::connect();
        $db->transStart();

        $mR->update($id, [
            'accion'   => 'Reproceso',
            'cantidad' => $post['pendientes'],
            'fecha'    => $post['eta'],
        ]);

        $mI = new InspeccionModel();
        $mI->update($r['inspeccionId'], [
            'ordenProduccionId' => (int)$post['op'],
            'fecha'             => $post['eta'],
            'resultado'         => 'reproceso',
            'observaciones'     => $post['tarea'] ?? null,
        ]);

        $db->transComplete();
        if (!$db->transStatus()) {
            return redirect()->back()->with('error','No se pudo editar');
        }
        return redirect()->to('/calidad/desperdicios')->with('success','Actualizado');
    }
}
