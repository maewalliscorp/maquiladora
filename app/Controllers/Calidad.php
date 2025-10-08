<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use App\Models\ReprocesoModel;

class Calidad extends BaseController
{
    /**
     * Vista principal: Desperdicios & Reprocesos
     */
    public function desperdicios()
    {
        $mR   = new ReprocesoModel();
        $todo = (bool) $this->request->getGet('todo');

        if ($todo) {
            $rep = $mR->select(
                'reproceso.id,
                     reproceso.accion AS tarea,
                     reproceso.cantidad AS pendientes,
                     reproceso.fecha AS eta,
                     inspeccion.ordenProduccionId AS op'
            )
                ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                ->orderBy('reproceso.fecha','DESC')
                ->groupBy('reproceso.id')->distinct()->findAll();
            $desp = [];
        } else {
            // 1) Desechos (sinónimos)
            $desp = $mR->select(
                'reproceso.id,
                     reproceso.cantidad,
                     reproceso.fecha,
                     inspeccion.ordenProduccionId AS op,
                     inspeccion.resultado,
                     inspeccion.observaciones'
            )
                ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                ->groupStart()
                ->where('LOWER(TRIM(inspeccion.resultado))', 'rechazo')
                ->orWhere('LOWER(TRIM(inspeccion.resultado))', 'desecho')
                ->orWhere('LOWER(TRIM(inspeccion.resultado))', 'desperdicio')
                ->orWhere('LOWER(TRIM(inspeccion.resultado))', 'scrap')
                ->orWhere('LOWER(TRIM(inspeccion.resultado))', 'rechazado')
                ->orWhere('LOWER(TRIM(inspeccion.resultado))', 'descartado')
                ->orWhere('LOWER(TRIM(inspeccion.resultado))', 'merma')
                ->groupEnd()
                ->orderBy('reproceso.fecha','DESC')
                ->groupBy('reproceso.id')->distinct()->findAll();

            $despIDs = array_map('intval', array_column($desp, 'id'));

            // 2) Reprocesos: todo lo que NO sea desecho (incluye vacío/NULL/reproceso)
            $notIn = "LOWER(TRIM(COALESCE(inspeccion.resultado,''))) NOT IN
                  ('rechazo','desecho','desperdicio','scrap','rechazado','descartado','merma')";

            $repBuilder = $mR->select(
                'reproceso.id,
                             reproceso.accion AS tarea,
                             reproceso.cantidad AS pendientes,
                             reproceso.fecha AS eta,
                             inspeccion.ordenProduccionId AS op'
            )
                ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                ->where($notIn, null, false)
                ->orderBy('reproceso.fecha','ASC')
                ->groupBy('reproceso.id')->distinct();

            if (!empty($despIDs)) {
                $repBuilder->whereNotIn('reproceso.id', $despIDs);
            }
            $rep = $repBuilder->findAll();

            // 3) Fallback: si nada entró, muestra todo como reproceso
            if (empty($desp) && empty($rep)) {
                $rep = $mR->select(
                    'reproceso.id,
                         reproceso.accion AS tarea,
                         reproceso.cantidad AS pendientes,
                         reproceso.fecha AS eta,
                         inspeccion.ordenProduccionId AS op'
                )
                    ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                    ->orderBy('reproceso.fecha','DESC')
                    ->groupBy('reproceso.id')->distinct()->findAll();
            }
        }

        return view('modulos/desperdicios', [
            'title' => 'Desperdicios & Reprocesos',
            'desp'  => $desp,
            'rep'   => $rep,
            'todo'  => $todo,
        ]);
    }


    /**
     * Crear registro de Desecho
     */
    public function guardarDesecho()
    {
        $post = $this->request->getPost();
        $db   = \Config\Database::connect();
        $db->transStart();

        // Inspección asociada con resultado estandarizado "rechazo"
        $mI = new InspeccionModel();
        $mI->insert([
            'ordenProduccionId' => (int)$post['op'],
            'puntoInspeccionId' => $post['puntoInspeccionId'] ?? null,
            'inspectorId'       => $post['inspectorId'] ?? null,
            'fecha'             => $post['fecha'],
            'resultado'         => 'rechazo',
            'observaciones'     => $post['motivo'] ?? null,
        ]);
        $insId = $mI->getInsertID();

        // En tu BD, "accion" es la descripción/motivo
        $mR = new ReprocesoModel();
        $mR->insert([
            'inspeccionId' => $insId,
            'accion'       => $post['motivo'] ?: 'Desecho',
            'cantidad'     => $post['cantidad'],
            'fecha'        => $post['fecha'],
        ]);

        $db->transComplete();
        return $db->transStatus()
            ? redirect()->to('/calidad/desperdicios')->with('success', 'Desecho registrado')
            : redirect()->back()->with('error', 'No se pudo registrar el desecho');
    }

    /**
     * Crear registro de Reproceso
     */
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
            'fecha'             => $post['eta'],
            'resultado'         => 'reproceso',
            'observaciones'     => $post['tarea'] ?? null,
        ]);
        $insId = $mI->getInsertID();

        $mR = new ReprocesoModel();
        $mR->insert([
            'inspeccionId' => $insId,
            'accion'       => $post['tarea'] ?: 'Reproceso',
            'cantidad'     => $post['pendientes'],
            'fecha'        => $post['eta'],
        ]);

        $db->transComplete();
        return $db->transStatus()
            ? redirect()->to('/calidad/desperdicios')->with('success', 'Reproceso registrado')
            : redirect()->back()->with('error', 'No se pudo registrar el reproceso');
    }

    /**
     * Editar Desecho
     */
    public function editarDesecho($id)
    {
        $post = $this->request->getPost();
        $mR   = new ReprocesoModel();
        $r    = $mR->find($id);
        if (!$r) return redirect()->back()->with('error', 'Registro no existe');

        $db = \Config\Database::connect();
        $db->transStart();

        $mR->update($id, [
            'accion'   => $post['motivo'] ?: 'Desecho',
            'cantidad' => $post['cantidad'],
            'fecha'    => $post['fecha'],
        ]);

        (new InspeccionModel())->update($r['inspeccionId'], [
            'ordenProduccionId' => (int)$post['op'],
            'fecha'             => $post['fecha'],
            'resultado'         => 'rechazo',
            'observaciones'     => $post['motivo'] ?? null,
        ]);

        $db->transComplete();
        return $db->transStatus()
            ? redirect()->to('/calidad/desperdicios')->with('success', 'Actualizado')
            : redirect()->back()->with('error', 'No se pudo editar');
    }

    /**
     * Editar Reproceso
     */
    public function editarReproceso($id)
    {
        $post = $this->request->getPost();
        $mR   = new ReprocesoModel();
        $r    = $mR->find($id);
        if (!$r) return redirect()->back()->with('error', 'Registro no existe');

        $db = \Config\Database::connect();
        $db->transStart();

        $mR->update($id, [
            'accion'   => $post['tarea'] ?: 'Reproceso',
            'cantidad' => $post['pendientes'],
            'fecha'    => $post['eta'],
        ]);

        (new InspeccionModel())->update($r['inspeccionId'], [
            'ordenProduccionId' => (int)$post['op'],
            'fecha'             => $post['eta'],
            'resultado'         => 'reproceso',
            'observaciones'     => $post['tarea'] ?? null,
        ]);

        $db->transComplete();
        return $db->transStatus()
            ? redirect()->to('/calidad/desperdicios')->with('success', 'Actualizado')
            : redirect()->back()->with('error', 'No se pudo editar');
    }

    /**
     * JSON detalle Desecho (modal "Vista")
     */
    public function verDesecho($id)
    {
        $row = (new ReprocesoModel())
            ->select('reproceso.*, inspeccion.ordenProduccionId AS op, inspeccion.resultado, inspeccion.observaciones')
            ->join('inspeccion', 'inspeccion.id = reproceso.inspeccionId')
            ->where('reproceso.id', $id)
            ->groupBy('reproceso.id')
            ->first();

        return $row ? $this->response->setJSON($row)
            : $this->response->setStatusCode(404)->setBody('No encontrado');
    }

    /**
     * JSON detalle Reproceso (modal "Vista")
     */
    public function verReproceso($id)
    {
        $row = (new ReprocesoModel())
            ->select('reproceso.*, inspeccion.ordenProduccionId AS op')
            ->join('inspeccion', 'inspeccion.id = reproceso.inspeccionId')
            ->where('reproceso.id', $id)
            ->groupBy('reproceso.id')
            ->first();

        return $row ? $this->response->setJSON($row)
            : $this->response->setStatusCode(404)->setBody('No encontrado');
    }

    /**
     * Diagnóstico rápido (JSON)
     */
    public function diag()
    {
        $db  = \Config\Database::connect();
        $out = ['ok' => true, 'steps' => []];

        try {
            // Tablas
            $tables = $db->listTables();
            $out['database'] = $db->getDatabase();
            $out['tables']   = $tables;
            $out['has']      = [
                'inspeccion' => in_array('inspeccion', $tables),
                'reproceso'  => in_array('reproceso',  $tables),
            ];
            $out['steps'][] = 'tables_ok';

            // Conteos
            $out['counts'] = [];
            if ($out['has']['reproceso'])  $out['counts']['reproceso']  = (int)$db->table('reproceso')->countAll();
            if ($out['has']['inspeccion']) $out['counts']['inspeccion'] = (int)$db->table('inspeccion')->countAll();
            $out['steps'][] = 'counts_ok';

            // Valores de reproceso.accion
            $out['accion_values'] = $out['has']['reproceso']
                ? $db->query("SELECT LOWER(accion) AS accion, COUNT(*) c FROM reproceso GROUP BY LOWER(accion)")
                    ->getResultArray()
                : [];
            $out['steps'][] = 'accion_ok';

            // Valores de inspeccion.resultado
            $out['resultado_values'] = $out['has']['inspeccion']
                ? $db->query("SELECT LOWER(COALESCE(resultado,'')) AS resultado, COUNT(*) c
                              FROM inspeccion GROUP BY LOWER(COALESCE(resultado,''))")->getResultArray()
                : [];
            $out['steps'][] = 'resultado_ok';

            // JOIN + muestra
            if ($out['has']['reproceso'] && $out['has']['inspeccion']) {
                $out['join_count'] = (int)($db->query("
                        SELECT COUNT(*) c
                        FROM reproceso r
                        JOIN inspeccion i ON i.id = r.inspeccionId
                    ")->getRowArray()['c'] ?? 0);

                $out['sample'] = $db->query("
                        SELECT r.id, r.accion, r.cantidad, r.fecha,
                               i.ordenProduccionId AS op, i.observaciones, i.resultado
                        FROM reproceso r
                        JOIN inspeccion i ON i.id = r.inspeccionId
                        ORDER BY r.fecha DESC
                        LIMIT 5
                    ")->getResultArray();
            } else {
                $out['join_count'] = 0;
                $out['sample'] = [];
            }
            $out['steps'][] = 'join_ok';

        } catch (\Throwable $e) {
            $out['ok']    = false;
            $out['error'] = $e->getMessage();
        }

        return $this->response->setJSON($out, $out['ok'] ? 200 : 500);
    }
}
