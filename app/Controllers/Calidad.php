<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use App\Models\ReprocesoModel;

class Calidad extends BaseController
{
    /* =========================================================
     * Helper: resolver ordenProduccionId desde input del usuario
     * Acepta: id o numero. Devuelve id o null si no existe.
     * ========================================================= */
    private function resolveOrdenProduccionId($op): ?int
    {
        $op = trim((string)$op);
        if ($op === '') return null;

        $db  = \Config\Database::connect();
        $tbl = $db->table('orden_produccion');

        // Busca por id exacto o por numero
        $row = $tbl->select('id')
            ->groupStart()
            ->where('id', (int)$op)
            ->orWhere('numero', $op)
            ->groupEnd()
            ->get(1)->getRowArray();

        return $row['id'] ?? null;
    }

    /** ===================== LISTA ===================== */
    public function desperdicios()
    {
        $mR   = new ReprocesoModel();
        $todo = (bool) $this->request->getGet('todo');
        $maquiladoraId = session()->get('maquiladora_id');

        if ($todo) {
            $repBuilder = $mR->select(
                'reproceso.id,
                 reproceso.accion AS tarea,
                 reproceso.cantidad AS pendientes,
                 reproceso.fecha AS eta,
                 inspeccion.ordenProduccionId AS op'
            )
                ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                ->join('orden_produccion op','op.id = inspeccion.ordenProduccionId','left')
                ->orderBy('reproceso.fecha','DESC')
                ->groupBy('reproceso.id')->distinct();

            if ($maquiladoraId) {
                $repBuilder->where('op.maquiladoraID', (int)$maquiladoraId);
            }

            $rep = $repBuilder->findAll();

            $desp = [];
        } else {
            // Desechos
            $despBuilder = $mR->select(
                'reproceso.id,
                 reproceso.cantidad,
                 reproceso.fecha,
                 inspeccion.ordenProduccionId AS op,
                 inspeccion.resultado,
                 inspeccion.observaciones'
            )
                ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                ->join('orden_produccion op','op.id = inspeccion.ordenProduccionId','left')
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
                ->groupBy('reproceso.id')->distinct();

            if ($maquiladoraId) {
                $despBuilder->where('op.maquiladoraID', (int)$maquiladoraId);
            }

            $desp = $despBuilder->findAll();

            $despIDs = array_map('intval', array_column($desp, 'id'));

            // Reprocesos
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
                ->join('orden_produccion op','op.id = inspeccion.ordenProduccionId','left')
                ->where($notIn, null, false)
                ->orderBy('reproceso.fecha','ASC')
                ->groupBy('reproceso.id')->distinct();

            if ($maquiladoraId) {
                $repBuilder->where('op.maquiladoraID', (int)$maquiladoraId);
            }

            if (!empty($despIDs)) $repBuilder->whereNotIn('reproceso.id', $despIDs);
            $rep = $repBuilder->findAll();

            if (empty($desp) && empty($rep)) {
                $repBuilder2 = $mR->select(
                    'reproceso.id,
                     reproceso.accion AS tarea,
                     reproceso.cantidad AS pendientes,
                     reproceso.fecha AS eta,
                     inspeccion.ordenProduccionId AS op'
                )
                    ->join('inspeccion','inspeccion.id = reproceso.inspeccionId')
                    ->join('orden_produccion op','op.id = inspeccion.ordenProduccionId','left')
                    ->orderBy('reproceso.fecha','DESC')
                    ->groupBy('reproceso.id')->distinct();

                if ($maquiladoraId) {
                    $repBuilder2->where('op.maquiladoraID', (int)$maquiladoraId);
                }

                $rep = $repBuilder2->findAll();
            }
        }

        return view('modulos/desperdicios', [
            'title' => 'Desperdicios & Reprocesos',
            'desp'  => $desp,
            'rep'   => $rep,
            'todo'  => $todo,
        ]);
    }

    /** ===================== CREAR ===================== */

    public function guardarDesecho()
    {
        $post = $this->request->getPost();
        $db   = \Config\Database::connect();

        try {
            $db->transStart();

            // Resolver OP a id real (FK). Si no existe, quedará null.
            $ordenId = $this->resolveOrdenProduccionId($post['op'] ?? null);

            $mI = new InspeccionModel();
            $insId = $mI->crearInspeccion([
                'ordenProduccionId' => $ordenId, // id real o null
                'fecha'             => $post['fecha'] ?? date('Y-m-d'),
                'resultado'         => 'rechazo',
                'observaciones'     => $post['motivo'] ?? null,
            ]);

            $mR = new ReprocesoModel();
            $mR->insert([
                'inspeccionId' => $insId,
                'accion'       => $post['motivo'] ?: 'Desecho',
                'cantidad'     => $post['cantidad'],
                'fecha'        => $post['fecha'] ?? date('Y-m-d'),
            ]);

            $db->transComplete();
            if (!$db->transStatus()) throw new \RuntimeException('DB transaction failed');

            return $this->respOkRedirect('Desecho registrado');
        } catch (\Throwable $e) {
            log_message('error', '[Desecho] '.$e->getMessage());
            return $this->respErrRedirect('No se pudo registrar el desecho');
        }
    }

    public function guardarReproceso()
    {
        $post = $this->request->getPost();
        $db   = \Config\Database::connect();

        try {
            $db->transStart();

            $ordenId = $this->resolveOrdenProduccionId($post['op'] ?? null);

            $mI = new InspeccionModel();
            $insId = $mI->crearInspeccion([
                'ordenProduccionId' => $ordenId,
                'fecha'             => $post['eta'] ?? date('Y-m-d'),
                'resultado'         => 'reproceso',
                'observaciones'     => $post['tarea'] ?? null,
            ]);

            $mR = new ReprocesoModel();
            $mR->insert([
                'inspeccionId' => $insId,
                'accion'       => $post['tarea'] ?: 'Reproceso',
                'cantidad'     => $post['pendientes'],
                'fecha'        => $post['eta'] ?? date('Y-m-d'),
            ]);

            $db->transComplete();
            if (!$db->transStatus()) throw new \RuntimeException('DB transaction failed');

            return $this->respOkRedirect('Reproceso registrado');
        } catch (\Throwable $e) {
            log_message('error', '[Reproceso] '.$e->getMessage());
            return $this->respErrRedirect('No se pudo registrar el reproceso');
        }
    }

    /** ===================== EDITAR ===================== */

    public function editarDesecho($id)
    {
        $post = $this->request->getPost();
        $mR   = new ReprocesoModel();
        $r    = $mR->find($id);
        if (!$r) return $this->respErrRedirect('Registro no existe');

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            $mR->update($id, [
                'accion'   => $post['motivo'] ?: 'Desecho',
                'cantidad' => $post['cantidad'],
                'fecha'    => $post['fecha'],
            ]);

            $ordenId = $this->resolveOrdenProduccionId($post['op'] ?? null);

            (new InspeccionModel())->update($r['inspeccionId'], [
                'ordenProduccionId' => $ordenId,
                'fecha'             => $post['fecha'],
                'resultado'         => 'rechazo',
                'observaciones'     => $post['motivo'] ?? null,
            ]);

            $db->transComplete();
            if (!$db->transStatus()) throw new \RuntimeException('DB transaction failed');

            return $this->respOkRedirect('Actualizado');
        } catch (\Throwable $e) {
            log_message('error', '[EditarDesecho] '.$e->getMessage());
            return $this->respErrRedirect('No se pudo editar');
        }
    }

    public function editarReproceso($id)
    {
        $post = $this->request->getPost();
        $mR   = new ReprocesoModel();
        $r    = $mR->find($id);
        if (!$r) return $this->respErrRedirect('Registro no existe');

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            $mR->update($id, [
                'accion'   => $post['tarea'] ?: 'Reproceso',
                'cantidad' => $post['pendientes'],
                'fecha'    => $post['eta'],
            ]);

            $ordenId = $this->resolveOrdenProduccionId($post['op'] ?? null);

            (new InspeccionModel())->update($r['inspeccionId'], [
                'ordenProduccionId' => $ordenId,
                'fecha'             => $post['eta'],
                'resultado'         => 'reproceso',
                'observaciones'     => $post['tarea'] ?? null,
            ]);

            $db->transComplete();
            if (!$db->transStatus()) throw new \RuntimeException('DB transaction failed');

            return $this->respOkRedirect('Actualizado');
        } catch (\Throwable $e) {
            log_message('error', '[EditarReproceso] '.$e->getMessage());
            return $this->respErrRedirect('No se pudo editar');
        }
    }

    /** ===================== ELIMINAR ===================== */

    public function eliminarDesecho($id)
    {
        $reqAjax = $this->request->isAJAX();
        $mR = new ReprocesoModel();
        $row = $mR->find($id);

        if (!$row) {
            $msg = ['ok'=>false,'message'=>'Registro no encontrado','csrf'=>csrf_hash()];
            return $reqAjax ? $this->response->setJSON($msg)->setStatusCode(404)
                : redirect()->back()->with('error',$msg['message']);
        }

        $db = \Config\Database::connect();
        try {
            $db->transStart();
            $mR->delete($id);
            if (!empty($row['inspeccionId'])) {
                (new InspeccionModel())->delete((int)$row['inspeccionId']);
            }
            $db->transComplete();
            if (!$db->transStatus()) throw new \RuntimeException('DB transaction failed');

            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Eliminado correctamente',
                    'csrf' => csrf_hash(),
                ]);
            }
            return redirect()->to('/calidad/desperdicios')->with('success','Eliminado');
        } catch (\Throwable $e) {
            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'No se pudo eliminar',
                    'csrf' => csrf_hash(),
                ], 500);
            }
            return redirect()->back()->with('error','No se pudo eliminar');
        }
    }

    public function eliminarReproceso($id)
    {
        $reqAjax = $this->request->isAJAX();
        $mR = new ReprocesoModel();
        $row = $mR->find($id);

        if (!$row) {
            $msg = ['ok'=>false,'message'=>'Registro no encontrado','csrf'=>csrf_hash()];
            return $reqAjax ? $this->response->setJSON($msg)->setStatusCode(404)
                : redirect()->back()->with('error',$msg['message']);
        }

        $db = \Config\Database::connect();
        try {
            $db->transStart();
            $mR->delete($id);
            if (!empty($row['inspeccionId'])) {
                (new InspeccionModel())->delete((int)$row['inspeccionId']);
            }
            $db->transComplete();
            if (!$db->transStatus()) throw new \RuntimeException('DB transaction failed');

            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Eliminado correctamente',
                    'csrf' => csrf_hash(),
                ]);
            }
            return redirect()->to('/calidad/desperdicios')->with('success','Eliminado');
        } catch (\Throwable $e) {
            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'No se pudo eliminar',
                    'csrf' => csrf_hash(),
                ], 500);
            }
            return redirect()->back()->with('error','No se pudo eliminar');
        }
    }

    /** ===================== JSON VISTA DETALLE ===================== */

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

    /** ===================== HELPERS RESPUESTA ===================== */

    private function respOkRedirect(string $msg)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok'=>true,'message'=>$msg,'csrf'=>csrf_hash()]);
        }
        return redirect()->to('/calidad/desperdicios')->with('success', $msg);
    }

    private function respErrRedirect(string $msg)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok'=>false,'message'=>$msg,'csrf'=>csrf_hash()], 422);
        }
        return redirect()->back()->with('error', $msg);
    }
}
