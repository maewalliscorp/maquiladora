<?php namespace App\Controllers;

use App\Models\OrdenProduccionModel;
use App\Models\AsignacionTareaModel;
use App\Models\EmpleadoModel;
use App\Models\TiempoTrabajoModel;

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
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        // Aceptar id como 'id' u 'opId'
        $id = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? $this->request->getPost('opId') ?? $this->request->getVar('opId') ?? 0);
        // Aceptar estatus como 'estatus' o 'status'
        $estatus = trim((string)($this->request->getPost('estatus') ?? $this->request->getVar('estatus') ?? $this->request->getPost('status') ?? $this->request->getVar('status') ?? ''));
        if ($id <= 0 || $estatus === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
        }
        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // Actualizar estatus de la OP con transacción explícita
            $okUpd = $db->table('orden_produccion')->where('id', $id)->update(['status' => $estatus]);
            if (!$okUpd) {
                $db->transRollback();
                return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo actualizar el estatus']);
            }

            $insId = null; $repId = null;
            // Si cambia a "En proceso", generar inspección y reproceso (si no existen)
            if (strcasecmp($estatus, 'En proceso') === 0) {
                // Verificar si ya existe inspección para esta OP
                $rowExist = null;
                try {
                    $rowExist = $db->query('SELECT id FROM inspeccion WHERE ordenProduccionId = ? LIMIT 1', [$id])->getRowArray();
                } catch (\Throwable $e) {
                    try { $rowExist = $db->query('SELECT id FROM Inspeccion WHERE ordenProduccionId = ? LIMIT 1', [$id])->getRowArray(); } catch (\Throwable $e2) { $rowExist = null; }
                }
                if ($rowExist && isset($rowExist['id'])) {
                    $insId = (int)$rowExist['id'];
                } else {
                    // Insertar inspección base
                    $rowIns = [
                        'ordenProduccionId' => $id,
                        'puntoInspeccionId' => null,
                        'inspectorId'       => null,
                        'fecha'             => null,
                        'resultado'         => null,
                        'observaciones'     => null,
                    ];
                    $db->table('inspeccion')->insert($rowIns);
                    $insId = (int)$db->insertID();
                    if ($insId === 0) { try { $db->table('Inspeccion')->insert($rowIns); $insId = (int)$db->insertID(); } catch (\Throwable $e) { $insId = 0; } }
                    if ($insId <= 0) { throw new \Exception('No se pudo crear la inspección inicial'); }
                }

                // Verificar o crear reproceso para esa inspección
                $rowRepExist = null;
                if ($insId > 0) {
                    try {
                        $rowRepExist = $db->query('SELECT id FROM reproceso WHERE inspeccionId = ? LIMIT 1', [$insId])->getRowArray();
                    } catch (\Throwable $e) {
                        try { $rowRepExist = $db->query('SELECT id FROM Reproceso WHERE inspeccionId = ? LIMIT 1', [$insId])->getRowArray(); } catch (\Throwable $e2) { $rowRepExist = null; }
                    }
                    if ($rowRepExist && isset($rowRepExist['id'])) {
                        $repId = (int)$rowRepExist['id'];
                    } else {
                        $rowRep = [
                            'inspeccionId' => $insId,
                            'accion'       => null,
                            'cantidad'     => null,
                            'fecha'        => null,
                        ];
                        $db->table('reproceso')->insert($rowRep);
                        $repId = (int)$db->insertID();
                        if ($repId === 0) { try { $db->table('Reproceso')->insert($rowRep); $repId = (int)$db->insertID(); } catch (\Throwable $e) { $repId = 0; } }
                        if ($repId <= 0) { throw new \Exception('No se pudo crear el reproceso inicial'); }
                    }
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }

            return $this->response->setJSON(['ok' => true, 'id' => $id, 'status' => $estatus, 'inspeccionId' => $insId, 'reprocesoId' => $repId]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    /** Eliminar una OP y dependencias (asignaciones, inspeccion, reproceso) */
    public function orden_eliminar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') { return $this->response->setJSON(['ok'=>true]); }
        if ($method !== 'post') { return $this->response->setStatusCode(405)->setJSON(['error'=>'Método no permitido']); }
        $id = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? $this->request->getPost('opId') ?? 0);
        if ($id <= 0) { return $this->response->setStatusCode(400)->setJSON(['error'=>'ID inválido']); }
        $db = \Config\Database::connect();
        try {
            $db->transStart();

            // Obtener ordenCompraId de la OP
            $ocId = null;
            try {
                $rowOP = $db->query('SELECT ordenCompraId FROM orden_produccion WHERE id = ?', [$id])->getRowArray();
                if ($rowOP && isset($rowOP['ordenCompraId'])) { $ocId = (int)$rowOP['ordenCompraId']; }
            } catch (\Throwable $e) {
                try { $rowOP = $db->query('SELECT ordenCompraId FROM OrdenProduccion WHERE id = ?', [$id])->getRowArray(); if ($rowOP && isset($rowOP['ordenCompraId'])) { $ocId = (int)$rowOP['ordenCompraId']; } } catch (\Throwable $e2) {}
            }

            // Obtener IDs de inspección ligados a la OP
            $insIds = [];
            try {
                $rows = $db->query('SELECT id FROM inspeccion WHERE ordenProduccionId = ?', [$id])->getResultArray();
                foreach ($rows as $r) { if (isset($r['id'])) $insIds[] = (int)$r['id']; }
            } catch (\Throwable $e) {
                try {
                    $rows = $db->query('SELECT id FROM Inspeccion WHERE ordenProduccionId = ?', [$id])->getResultArray();
                    foreach ($rows as $r) { if (isset($r['id'])) $insIds[] = (int)$r['id']; }
                } catch (\Throwable $e2) { /* ignore */ }
            }

            if (!empty($insIds)) {
                // Borrar reprocesos por inspeccionId
                try { $db->table('reproceso')->whereIn('inspeccionId', $insIds)->delete(); } catch (\Throwable $e) {
                    try { $db->table('Reproceso')->whereIn('inspeccionId', $insIds)->delete(); } catch (\Throwable $e2) { /* ignore */ }
                }
            }
            // Borrar inspecciones por OP
            try { $db->table('inspeccion')->where('ordenProduccionId', $id)->delete(); } catch (\Throwable $e) {
                try { $db->table('Inspeccion')->where('ordenProduccionId', $id)->delete(); } catch (\Throwable $e2) { /* ignore */ }
            }

            // Borrar asignaciones de tarea por OP
            try { $db->table('asignacion_tarea')->where('ordenProduccionId', $id)->delete(); } catch (\Throwable $e) { /* ignore si no existe */ }

            // Borrar la OP
            $okDel = false;
            try { $okDel = (bool)$db->table('orden_produccion')->where('id', $id)->delete(); } catch (\Throwable $e) { $okDel = false; }
            if (!$okDel) {
                try { $okDel = (bool)$db->table('OrdenProduccion')->where('id', $id)->delete(); } catch (\Throwable $e2) { $okDel = false; }
            }
            if (!$okDel) { throw new \Exception('No se pudo eliminar la Orden de Producción'); }

            // Si había orden_compra ligada, eliminarla también
            if ($ocId && $ocId > 0) {
                $okOc = false;
                try { $okOc = (bool)$db->table('orden_compra')->where('id', $ocId)->delete(); } catch (\Throwable $e) { $okOc = false; }
                if (!$okOc) {
                    try { $okOc = (bool)$db->table('OrdenCompra')->where('id', $ocId)->delete(); } catch (\Throwable $e2) { $okOc = false; }
                }
                // No forzar excepción si no existe, pero registrar fallo lógico
            }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }
            return $this->response->setJSON(['ok'=>true, 'id'=>$id]);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON(['error'=>'Error al eliminar: '.$e->getMessage()]);
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
            
            // Usar consulta directa para evitar caché del modelo
            $db = \Config\Database::connect();
            $sql = "SELECT at.id,
                           at.ordenProduccionId AS opId,
                           at.rutaOperacionId,
                           at.asignadoDesde,
                           at.asignadoHasta,
                           op.folio,
                           op.status
                    FROM asignacion_tarea at
                    JOIN orden_produccion op ON op.id = at.ordenProduccionId
                    WHERE at.empleadoId = ?
                    ORDER BY at.asignadoDesde IS NULL, at.asignadoDesde ASC, at.id DESC";
            $rows = $db->query($sql, [$empleadoId])->getResultArray();
            
            // Verificar el estatus directamente desde la BD para cada orden
            // Usar una nueva conexión para evitar caché y forzar lectura fresca
            foreach ($rows as &$row) {
                $estatusOriginal = $row['status'] ?? '';
                $dbNueva = \Config\Database::connect();
                // Forzar una consulta fresca sin caché - usar SQL directo
                $sqlStatus = "SELECT status FROM orden_produccion WHERE id = ? LIMIT 1";
                $statusRow = $dbNueva->query($sqlStatus, [$row['opId']])->getRowArray();
                if ($statusRow && isset($statusRow['status'])) {
                    $estatusObtenido = trim($statusRow['status']);
                    // Siempre sobrescribir con el estatus de la BD
                    $row['status'] = $estatusObtenido;
                    // Log para debug
                    log_message('debug', "OP {$row['opId']} - Estatus obtenido de BD: '{$estatusObtenido}' (original del JOIN: '{$estatusOriginal}')");
                } else {
                    // Si no se encontró, mantener el original pero loguear
                    log_message('warning', "OP {$row['opId']} - No se pudo obtener estatus de BD, usando: '{$estatusOriginal}'");
                }
            }
            
            return $this->response->setJSON(['empleadoId' => $empleadoId, 'items' => $rows]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al obtener tareas', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Iniciar tiempo de trabajo para una orden de producción
     */
    public function tiempo_trabajo_iniciar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        try {
            // Obtener ordenProduccionId
            $ordenProduccionId = (int)($this->request->getPost('ordenProduccionId') ?? $this->request->getPost('id') ?? 0);
            if ($ordenProduccionId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'ID de orden de producción inválido']);
            }

            // Obtener empleadoId (similar a tareas_empleado_json)
            $empleadoId = (int)($this->request->getPost('empleadoId') ?? 0);
            if ($empleadoId <= 0) {
                $empModel = new EmpleadoModel();
                // 1) por idusuario
                $userId = (int)(session()->get('user_id') ?? 0);
                if ($userId > 0 && $empleadoId <= 0) {
                    $emp = $empModel->where('idusuario', $userId)->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int)$emp['id'];
                    }
                }
                // 2) por email
                $email = (string)(session()->get('email') ?? '');
                if ($empleadoId <= 0 && $email !== '') {
                    $emp = $empModel->where('email', $email)->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int)$emp['id'];
                    }
                }
                // 3) por user_name contra noEmpleado o nombre
                $uname = (string)(session()->get('user_name') ?? '');
                if ($empleadoId <= 0 && $uname !== '') {
                    $emp = $empModel->groupStart()
                            ->where('noEmpleado', $uname)
                            ->orWhere('nombre', $uname)
                        ->groupEnd()->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int)$emp['id'];
                    }
                }
            }

            if ($empleadoId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'No se pudo resolver el empleado actual']);
            }

            // Verificar si ya existe un registro activo (sin finalizar)
            $tiempoModel = new TiempoTrabajoModel();
            $activo = $tiempoModel->obtenerActivo($empleadoId, $ordenProduccionId);
            if ($activo) {
                return $this->response->setJSON([
                    'ok' => true,
                    'id' => (int)$activo['id'],
                    'yaExiste' => true,
                    'inicio' => $activo['inicio'] ?? null,
                ]);
            }

            // Crear nuevo registro
            $id = $tiempoModel->iniciar($empleadoId, $ordenProduccionId);
            if ($id === false) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo iniciar el tiempo de trabajo']);
            }

            return $this->response->setJSON([
                'ok' => true,
                'id' => $id,
                'empleadoId' => $empleadoId,
                'ordenProduccionId' => $ordenProduccionId,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al iniciar tiempo de trabajo',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Finalizar tiempo de trabajo para una orden de producción
     */
    public function tiempo_trabajo_finalizar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        try {
            // Opción 1: Por ID del registro de tiempo_trabajo
            $tiempoTrabajoId = (int)($this->request->getPost('tiempoTrabajoId') ?? $this->request->getPost('id') ?? 0);

            // Opción 2: Por empleadoId y ordenProduccionId (buscar el activo)
            if ($tiempoTrabajoId <= 0) {
                $ordenProduccionId = (int)($this->request->getPost('ordenProduccionId') ?? 0);
                $empleadoId = (int)($this->request->getPost('empleadoId') ?? 0);

                if ($ordenProduccionId <= 0 || $empleadoId <= 0) {
                    // Intentar obtener empleadoId de la sesión
                    if ($empleadoId <= 0) {
                        $empModel = new EmpleadoModel();
                        $userId = (int)(session()->get('user_id') ?? 0);
                        if ($userId > 0) {
                            $emp = $empModel->where('idusuario', $userId)->select('id')->first();
                            if ($emp && isset($emp['id'])) {
                                $empleadoId = (int)$emp['id'];
                            }
                        }
                    }

                    if ($empleadoId <= 0 || $ordenProduccionId <= 0) {
                        return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
                    }
                }

                $tiempoModel = new TiempoTrabajoModel();
                $activo = $tiempoModel->obtenerActivo($empleadoId, $ordenProduccionId);
                if (!$activo || !isset($activo['id'])) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'No se encontró un registro activo para finalizar']);
                }
                $tiempoTrabajoId = (int)$activo['id'];
            }

            if ($tiempoTrabajoId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'ID de tiempo de trabajo inválido']);
            }

            // Obtener el registro antes de finalizar para conocer empleadoId y ordenProduccionId
            $tiempoModel = new TiempoTrabajoModel();
            $registroAntes = $tiempoModel->find($tiempoTrabajoId);
            if (!$registroAntes) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Registro de tiempo de trabajo no encontrado']);
            }

            $empleadoId = (int)($registroAntes['empleadoId'] ?? 0);
            $ordenProduccionId = (int)($registroAntes['ordenProduccionId'] ?? 0);

            // Finalizar el registro
            $resultado = $tiempoModel->finalizar($tiempoTrabajoId);
            if (!$resultado) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo finalizar el tiempo de trabajo']);
            }

            // Obtener el registro finalizado para devolver los datos
            $registro = $tiempoModel->find($tiempoTrabajoId);

            // Obtener el puesto del empleado
            $empModel = new EmpleadoModel();
            $empleado = $empModel->find($empleadoId);
            $puesto = $empleado && isset($empleado['puesto']) ? trim((string)$empleado['puesto']) : '';

            // Verificar si todos los empleados de ese tipo han finalizado
            $todosFinalizados = false;
            $nuevoEstatus = null;
            $debugInfo = [];

            if (!empty($puesto) && $ordenProduccionId > 0) {
                // Normalizar el puesto (comparar sin importar mayúsculas/minúsculas)
                $puestoLower = strtolower(trim($puesto));
                $debugInfo['puesto'] = $puesto;
                $debugInfo['puestoLower'] = $puestoLower;
                
                if ($puestoLower === 'corte') {
                    $todosFinalizados = $tiempoModel->todosHanFinalizado($ordenProduccionId, 'Corte');
                    $debugInfo['tipoVerificado'] = 'Corte';
                    if ($todosFinalizados) {
                        $nuevoEstatus = 'Corte finalizado';
                    }
                } elseif ($puestoLower === 'empleado') {
                    $todosFinalizados = $tiempoModel->todosHanFinalizado($ordenProduccionId, 'Empleado');
                    $debugInfo['tipoVerificado'] = 'Empleado';
                    $debugInfo['todosFinalizados'] = $todosFinalizados;
                    
                    // Información adicional de debug
                    $asigModel = new AsignacionTareaModel();
                    $asignaciones = $asigModel->listarPorOP($ordenProduccionId);
                    $empleadosAsignados = array_filter($asignaciones, function($a) {
                        return isset($a['puesto']) && strtolower(trim($a['puesto'])) === 'empleado';
                    });
                    $debugInfo['empleadosAsignados'] = count($empleadosAsignados);
                    $debugInfo['asignaciones'] = array_map(function($a) {
                        return [
                            'empleadoId' => $a['empleadoId'] ?? null,
                            'nombre' => ($a['nombre'] ?? '') . ' ' . ($a['apellido'] ?? ''),
                            'puesto' => $a['puesto'] ?? null
                        ];
                    }, $empleadosAsignados);
                    
                    // Verificar registros de tiempo_trabajo
                    $db = \Config\Database::connect();
                    $sqlTiempos = "SELECT tt.id, tt.empleadoId, tt.inicio, tt.fin, e.nombre, e.puesto
                                  FROM tiempo_trabajo tt
                                  INNER JOIN empleado e ON e.id = tt.empleadoId
                                  WHERE tt.ordenProduccionId = ? AND LOWER(TRIM(e.puesto)) = 'empleado'";
                    $tiempos = $db->query($sqlTiempos, [$ordenProduccionId])->getResultArray();
                    $debugInfo['registrosTiempo'] = count($tiempos);
                    $debugInfo['tiempos'] = array_map(function($t) {
                        return [
                            'id' => $t['id'] ?? null,
                            'empleadoId' => $t['empleadoId'] ?? null,
                            'nombre' => $t['nombre'] ?? '',
                            'inicio' => $t['inicio'] ?? null,
                            'fin' => $t['fin'] ?? null,
                            'finalizado' => !empty($t['fin'])
                        ];
                    }, $tiempos);
                    
                    if ($todosFinalizados) {
                        $nuevoEstatus = 'Completada';
                    }
                } else {
                    $debugInfo['tipoVerificado'] = 'desconocido';
                    $debugInfo['todosFinalizados'] = false;
                }
            } else {
                $debugInfo['error'] = 'Puesto vacío o ordenProduccionId inválido';
                $debugInfo['puesto'] = $puesto;
                $debugInfo['ordenProduccionId'] = $ordenProduccionId;
            }

            // Actualizar el estatus de la OP si todos han finalizado
            $estatusActualizado = false;
            $estatusAnterior = null;
            if ($todosFinalizados && $nuevoEstatus !== null && $ordenProduccionId > 0) {
                try {
                    $db = \Config\Database::connect();
                    
                    // Obtener el estatus actual antes de actualizar
                    $opActual = $db->table('orden_produccion')
                        ->where('id', $ordenProduccionId)
                        ->select('status')
                        ->get()
                        ->getRowArray();
                    $estatusAnterior = $opActual['status'] ?? null;
                    $debugInfo['estatusAnterior'] = $estatusAnterior;
                    
                    // Actualizar el estatus usando el método del modelo (igual que actualizarEstatus)
                    $db->transStart();
                    
                    // Usar el mismo método que actualizarEstatus para asegurar consistencia
                    $okUpd = $db->table('orden_produccion')->where('id', $ordenProduccionId)->update(['status' => $nuevoEstatus]);
                    
                    // Verificar cuántas filas se afectaron
                    $affectedRows = $db->affectedRows();
                    $debugInfo['affectedRows'] = $affectedRows;
                    $debugInfo['resultadoUpdate'] = $okUpd;
                    
                    if (!$okUpd || $affectedRows === 0) {
                        $db->transRollback();
                        log_message('error', "No se actualizó ninguna fila para OP {$ordenProduccionId}. Método update retornó: " . var_export($okUpd, true) . ", Filas afectadas: {$affectedRows}");
                        throw new \Exception("No se pudo actualizar el estatus. No se afectaron filas.");
                    }
                    
                    // Confirmar la transacción
                    $db->transComplete();
                    
                    if ($db->transStatus() === false) {
                        log_message('error', "Error en la transacción al actualizar estatus de OP {$ordenProduccionId}");
                        throw new \Exception("Error en la transacción al actualizar estatus");
                    }
                    
                    // Verificar que realmente se actualizó usando una nueva consulta SQL directa
                    // Esperar un momento para asegurar que la actualización se haya completado
                    usleep(500000); // 500ms
                    
                    // Usar una nueva conexión y consulta SQL directa para evitar caché
                    $dbNueva = \Config\Database::connect();
                    $sqlVerificar = "SELECT status FROM orden_produccion WHERE id = ? LIMIT 1";
                    $opDespues = $dbNueva->query($sqlVerificar, [$ordenProduccionId])->getRowArray();
                    $estatusDespues = $opDespues['status'] ?? null;
                    
                    // Log para debug
                    log_message('debug', "Actualización de estatus OP {$ordenProduccionId}: Anterior='{$estatusAnterior}', Nuevo='{$nuevoEstatus}', Verificado='{$estatusDespues}', Filas afectadas={$affectedRows}");
                    
                    $estatusActualizado = ($estatusDespues === $nuevoEstatus);
                    $debugInfo['estatusActualizado'] = $estatusActualizado;
                    $debugInfo['nuevoEstatus'] = $nuevoEstatus;
                    $debugInfo['estatusDespues'] = $estatusDespues;
                    $debugInfo['resultadoUpdate'] = $resultado;
                    
                    if (!$estatusActualizado) {
                        log_message('error', "No se pudo actualizar estatus de OP {$ordenProduccionId}. Anterior: {$estatusAnterior}, Esperado: {$nuevoEstatus}, Actual: {$estatusDespues}");
                    }
                } catch (\Throwable $e) {
                    // Log el error pero no fallar la respuesta
                    log_message('error', 'Error al actualizar estatus de OP: ' . $e->getMessage());
                    $debugInfo['errorActualizacion'] = $e->getMessage();
                    $debugInfo['errorTrace'] = $e->getTraceAsString();
                }
            } else {
                $debugInfo['noActualizado'] = 'Condiciones no cumplidas';
                $debugInfo['todosFinalizados'] = $todosFinalizados;
                $debugInfo['nuevoEstatus'] = $nuevoEstatus;
                $debugInfo['ordenProduccionId'] = $ordenProduccionId;
            }

            return $this->response->setJSON([
                'ok' => true,
                'id' => $tiempoTrabajoId,
                'inicio' => $registro['inicio'] ?? null,
                'fin' => $registro['fin'] ?? null,
                'horas' => $registro['horas'] ?? null,
                'todosFinalizados' => $todosFinalizados,
                'nuevoEstatus' => $nuevoEstatus,
                'estatusActualizado' => $estatusActualizado,
                'debug' => $debugInfo,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al finalizar tiempo de trabajo',
                'message' => $e->getMessage(),
            ]);
        }
    }
}

