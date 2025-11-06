<?php namespace App\Controllers;

use App\Models\OrdenProduccionModel;
use App\Models\AsignacionTareaModel;
use App\Models\EmpleadoModel;

class Produccion extends BaseController
{
    public function ordenes()
    {
        $model   = new OrdenProduccionModel();
        $ordenes = $model->getListado();

        foreach ($ordenes as &$r) {
            $r['ini'] = $r['ini'] ? date('Y-m-d', strtotime($r['ini'])) : '';
            $r['fin'] = $r['fin'] ? date('Y-m-d', strtotime($r['fin'])) : '';
        }

        return view('modulos/m1_ordenes', [
            'title'   => 'Órdenes de Producción',
            'ordenes' => $ordenes,
        ]);
    }

    public function actualizarEstatus()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        $id = (int)($this->request->getPost('id') ?? 0);
        $estatus = trim((string)($this->request->getPost('estatus') ?? ''));
        if ($id <= 0 || $estatus === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
        }
        try {
            $model = new OrdenProduccionModel();
            if (!$model->updateEstatus($id, $estatus)) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo actualizar el estatus']);
            }
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function orden_json($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }
        try {
            $model = new OrdenProduccionModel();
            // Cargar solo datos básicos de orden_produccion primero
            $row = $model->getDetalleBasico($id);
            if (!$row) {
                // Diagnóstico adicional para entender por qué no se encuentra
                $db = \Config\Database::connect();
                $dbName = $db->query('SELECT DATABASE() AS db')->getRowArray()['db'] ?? '';
                $exists = $db->query('SELECT COUNT(*) AS c FROM orden_produccion WHERE id = ?', [$id])->getRowArray();
                $sample = $db->query('SELECT id, folio FROM orden_produccion ORDER BY id ASC LIMIT 10')->getResultArray();
                return $this->response->setStatusCode(404)->setJSON([
                    'error'      => 'Orden no encontrada',
                    'db'         => $dbName,
                    'countById'  => $exists['c'] ?? null,
                    'sample'     => $sample,
                ]);
            }
            return $this->response->setJSON($row);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Excepción en orden_json',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function orden_json_folio($folio = null)
    {
        $folio = trim((string)($folio ?? ''));
        if ($folio === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Folio inválido']);
        }
        try {
            $model = new OrdenProduccionModel();
            // Primero obtener la OP por folio (básico) para conocer el ID
            $basico = $model->getDetalleBasicoPorFolio($folio);
            if (!$basico) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Orden no encontrada por folio', 'folio' => $folio]);
            }
            // Con el ID, obtener el detalle completo con diseño
            $row = $model->getDetalle((int)$basico['id']);
            if (!$row) {
                // Fallback: devolver básico si por alguna razón el join no devuelve
                return $this->response->setJSON($basico);
            }
            return $this->response->setJSON($row);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Excepción en orden_json_folio',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // -------- Asignaciones de tarea --------
    public function asignaciones($opId = null)
    {
        $opId = (int)($opId ?? 0);
        if ($opId <= 0) return $this->response->setStatusCode(400)->setJSON(['error'=>'ID inválido']);
        $asigModel = new AsignacionTareaModel();
        $empModel  = new EmpleadoModel();
        $asignadas = $asigModel->listarPorOP($opId);
        $empleados = $empModel->listarDisponiblesParaOP($opId);
        return $this->response->setJSON([
            'opId'      => $opId,
            'asignadas' => $asignadas,
            'empleados' => $empleados,
        ]);
    }

    public function asignaciones_agregar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') { return $this->response->setJSON(['ok'=>true]); }
        if ($method !== 'post') { return $this->response->setStatusCode(405)->setJSON(['error'=>'Método no permitido']); }
        $opId = (int)($this->request->getPost('opId') ?? 0);
        $empleadoId = (int)($this->request->getPost('empleadoId') ?? 0);
        $desde = $this->request->getPost('desde') ?: null;
        $hasta = $this->request->getPost('hasta') ?: null;
        $ruta  = $this->request->getPost('rutaOperacionId') ? (int)$this->request->getPost('rutaOperacionId') : null;
        if ($opId<=0 || $empleadoId<=0) return $this->response->setStatusCode(400)->setJSON(['error'=>'Parámetros inválidos']);
        $asigModel = new AsignacionTareaModel();
        if (!$asigModel->agregar($opId, $empleadoId, $desde, $hasta, $ruta)) {
            return $this->response->setStatusCode(500)->setJSON(['error'=>'No se pudo asignar']);
        }
        return $this->response->setJSON(['ok'=>true]);
    }

    public function asignaciones_agregar_multiple()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') { return $this->response->setJSON(['ok'=>true]); }
        if ($method !== 'post') { return $this->response->setStatusCode(405)->setJSON(['error'=>'Método no permitido']); }
        $opId = (int)($this->request->getPost('opId') ?? 0);
        $empleados = $this->request->getPost('empleados'); // array de IDs
        $desde = $this->request->getPost('desde') ?: null;
        $hasta = $this->request->getPost('hasta') ?: null;
        $ruta  = $this->request->getPost('rutaOperacionId') ? (int)$this->request->getPost('rutaOperacionId') : null;
        if ($opId<=0 || !is_array($empleados) || empty($empleados)) {
            return $this->response->setStatusCode(400)->setJSON(['error'=>'Parámetros inválidos']);
        }
        $asigModel = new AsignacionTareaModel();
        $ok = 0; $dup = 0; $fail = 0;
        foreach ($empleados as $eid) {
            $eid = (int)$eid;
            if ($eid<=0) { $fail++; continue; }
            if ($asigModel->existeAsignacion($opId, $eid)) { $dup++; continue; }
            $res = $asigModel->agregar($opId, $eid, $desde, $hasta, $ruta);
            if ($res) $ok++; else $fail++;
        }
        return $this->response->setJSON(['ok'=>true, 'asignados'=>$ok, 'duplicados'=>$dup, 'fallidos'=>$fail]);
    }

    public function asignaciones_eliminar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') { return $this->response->setJSON(['ok'=>true]); }
        if ($method !== 'post') { return $this->response->setStatusCode(405)->setJSON(['error'=>'Método no permitido']); }
        $asignacionId = (int)($this->request->getPost('asignacionId') ?? 0);
        if ($asignacionId<=0) return $this->response->setStatusCode(400)->setJSON(['error'=>'Parámetro inválido']);
        $asigModel = new AsignacionTareaModel();
        if (!$asigModel->eliminar($asignacionId)) {
            return $this->response->setStatusCode(500)->setJSON(['error'=>'No se pudo eliminar la asignación']);
        }
        return $this->response->setJSON(['ok'=>true]);
    }

    // Búsqueda remota (Select2) de empleados disponibles para una OP
    public function empleados_buscar_disponibles($opId = null)
    {
        $opId = (int)($opId ?? 0);
        if ($opId <= 0) return $this->response->setStatusCode(400)->setJSON(['results'=>[]]);
        $term = trim((string)($this->request->getGet('term') ?? ''));
        $empModel = new EmpleadoModel();
        $rows = $empModel->buscarDisponiblesParaOP($opId, $term, 20);
        $results = array_map(function($r){
            $text = ($r['noEmpleado'] ? ('['.$r['noEmpleado'].'] ') : '') . $r['nombre'] . ' ' . ($r['apellido'] ?? '');
            return ['id' => (int)$r['id'], 'text' => $text];
        }, $rows);
        return $this->response->setJSON(['results'=>$results]);
    }

    public function tareas_empleado_json($empleadoId = null)
    {
        try {
            // Permitir query param ?empleadoId=8
            $empleadoId = (int)($this->request->getGet('empleadoId') ?? $empleadoId ?? 0);
            if ($empleadoId <= 0) {
                $empModel = new EmpleadoModel();
                // 1) por idusuario
                $userId = (int)(session()->get('user_id') ?? 0);
                if ($userId > 0 && $empleadoId <= 0) {
                    $emp = $empModel->where('idusuario', $userId)->select('id').first();
                    if ($emp && isset($emp['id'])) { $empleadoId = (int)$emp['id']; }
                }
                // 2) por email
                $email = (string)(session()->get('email') ?? '');
                if ($empleadoId <= 0 && $email !== '') {
                    $emp = $empModel->where('email', $email)->select('id').first();
                    if ($emp && isset($emp['id'])) { $empleadoId = (int)$emp['id']; }
                }
                // 3) por user_name contra noEmpleado o nombre
                $uname = (string)(session()->get('user_name') ?? '');
                if ($empleadoId <= 0 && $uname !== '') {
                    $emp = $empModel->groupStart()
                            ->where('noEmpleado', $uname)
                            ->orWhere('nombre', $uname)
                        ->groupEnd()->select('id').first();
                    if ($emp && isset($emp['id'])) { $empleadoId = (int)$emp['id']; }
                }
            }
            if ($empleadoId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'No se pudo resolver el empleado actual']);
            }
            $rows = (new AsignacionTareaModel())->listarPorEmpleado($empleadoId);
            return $this->response->setJSON(['empleadoId' => $empleadoId, 'items' => $rows]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al obtener tareas', 'message' => $e->getMessage()]);
        }
    }
}

