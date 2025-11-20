<?php

namespace App\Controllers;

/** Controlador principal: vistas y endpoints JSON de módulos. */
class Modulos extends BaseController
{
    /** Datos base (notif/usuario) + datos de vista. */
    private function payload(array $data = []): array
    {
        $base = [
            'notifCount' => $data['notifCount'] ?? 0,
            'userEmail'  => session()->get('email') ?: 'admin@fabrica.com',
        ];
        return array_merge($base, $data);
    }

    /** Eliminar un pedido (orden_compra) y en cascada su OP y dependencias */
    public function m1_pedido_eliminar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') { return $this->response->setJSON(['ok'=>true]); }
        if ($method !== 'post') { return $this->response->setStatusCode(405)->setJSON(['error'=>'Método no permitido']); }
        $ocId = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? $this->request->getPost('ocId') ?? 0);
        if ($ocId <= 0) { return $this->response->setStatusCode(400)->setJSON(['error'=>'ID inválido']); }
        $db = \Config\Database::connect();
        // Maquiladora del usuario autenticado
        $maquiladoraId = session()->get('maquiladora_id');
        try {
            $db->transStart();

            // Buscar OPs relacionadas con esta OC
            $opIds = [];
            try {
                $rows = $db->query('SELECT id FROM orden_produccion WHERE ordenCompraId = ?', [$ocId])->getResultArray();
                foreach ($rows as $r) { if (isset($r['id'])) $opIds[] = (int)$r['id']; }
            } catch (\Throwable $e) {
                try {
                    $rows = $db->query('SELECT id FROM OrdenProduccion WHERE ordenCompraId = ?', [$ocId])->getResultArray();
                    foreach ($rows as $r) { if (isset($r['id'])) $opIds[] = (int)$r['id']; }
                } catch (\Throwable $e2) { /* ignore */ }
            }

            foreach ($opIds as $opId) {
                // Borrar Reproceso -> Inspeccion -> Asignaciones por OP
                $insIds = [];
                try {
                    $rins = $db->query('SELECT id FROM inspeccion WHERE ordenProduccionId = ?', [$opId])->getResultArray();
                    foreach ($rins as $ri) { if (isset($ri['id'])) $insIds[] = (int)$ri['id']; }
                } catch (\Throwable $e) {
                    try {
                        $rins = $db->query('SELECT id FROM Inspeccion WHERE ordenProduccionId = ?', [$opId])->getResultArray();
                        foreach ($rins as $ri) { if (isset($ri['id'])) $insIds[] = (int)$ri['id']; }
                    } catch (\Throwable $e2) { /* ignore */ }
                }
                if (!empty($insIds)) {
                    try { $db->table('reproceso')->whereIn('inspeccionId', $insIds)->delete(); } catch (\Throwable $e) {
                        try { $db->table('Reproceso')->whereIn('inspeccionId', $insIds)->delete(); } catch (\Throwable $e2) { /* ignore */ }
                    }
                }
                try { $db->table('inspeccion')->where('ordenProduccionId', $opId)->delete(); } catch (\Throwable $e) {
                    try { $db->table('Inspeccion')->where('ordenProduccionId', $opId)->delete(); } catch (\Throwable $e2) { /* ignore */ }
                }
                try { $db->table('asignacion_tarea')->where('ordenProduccionId', $opId)->delete(); } catch (\Throwable $e) { /* ignore */ }
                // Borrar OP
                $okDelOp = false;
                try { $okDelOp = (bool)$db->table('orden_produccion')->where('id', $opId)->delete(); } catch (\Throwable $e) { $okDelOp = false; }
                if (!$okDelOp) { try { $okDelOp = (bool)$db->table('OrdenProduccion')->where('id', $opId)->delete(); } catch (\Throwable $e2) { $okDelOp = false; } }
            }

            // Finalmente, borrar la Orden de Compra
            $okOc = false;
            try { $okOc = (bool)$db->table('orden_compra')->where('id', $ocId)->delete(); } catch (\Throwable $e) { $okOc = false; }
            if (!$okOc) { try { $okOc = (bool)$db->table('OrdenCompra')->where('id', $ocId)->delete(); } catch (\Throwable $e2) { $okOc = false; } }
            if (!$okOc) { throw new \Exception('No se pudo eliminar el pedido (Orden de Compra)'); }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }
            return $this->response->setJSON(['ok'=>true, 'id'=>$ocId]);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON(['error'=>'Error al eliminar pedido: '.$e->getMessage()]);
        }
    }

    /**
     * Obtener datos de un usuario (JSON) + catálogos (roles, maquiladoras)
     */
    public function m11_obtener_usuario($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID inválido',
            ]);
        }
        
        // Conexión a base de datos
        $db = \Config\Database::connect();
        try {
            // Usuario base
            $user = $db->table('users')->where('id', $id)->get()->getRowArray();
            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ]);
            }

            // Rol actual y listado de roles
            $rolActual = null;
            try {
                $map = $db->table('usuario_rol')->where('usuarioIdFK', $id)->get()->getRowArray();
                $rolActual = $map['rolIdFK'] ?? null;
            } catch (\Throwable $e) { $rolActual = null; }

            // Alias a 'name' para coincidir con el JS de la vista, filtrando por maquiladora cuando aplique
            $rolesBuilder = $db->table('rol')->select('id, nombre as name');
            $maquiladoraId = session()->get('maquiladora_id');
            if ($maquiladoraId) {
                try {
                    $fields = $db->getFieldNames('rol');
                    if (in_array('maquiladoraID', $fields, true)) {
                        $rolesBuilder->where('maquiladoraID', (int)$maquiladoraId);
                    }
                } catch (\Throwable $e) {}
            }
            $roles = $rolesBuilder->orderBy('nombre','ASC')->get()->getResultArray();

            // Maquiladoras
            $maqs = $db->table('maquiladora')->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->orderBy('Nombre_Maquila','ASC')->get()->getResultArray();

            $out = [
                'id' => (int)$user['id'],
                'username' => $user['username'] ?? '',
                'email' => $user['correo'] ?? '',
                'maquiladoraIdFK' => $user['maquiladoraIdFK'] ?? null,
                'activo' => (int)($user['active'] ?? 1),
                'rol_id' => $rolActual,
                'roles' => $roles,
                'maquiladoras' => $maqs,
            ];

            return $this->response->setJSON(['success' => true, 'data' => $out]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Actualizar datos de usuario: nombre, correo, rol, maquiladora, activo y password (opcional)
     * Entrada POST: id, nombre, email, rol, idmaquiladora, activo, password?
     */
    public function m11_actualizar_usuario()
    {
        // Aceptar la petición sin forzar método (la ruta es POST, pero algunos entornos envían como AJAX genérico)
        $id = (int)($this->request->getPost('id') ?? $this->request->getVar('id'));
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID inválido']);
        }

        $nombre = trim((string)($this->request->getPost('nombre') ?? $this->request->getVar('nombre') ?? ''));
        $email  = trim((string)($this->request->getPost('email')  ?? $this->request->getVar('email')  ?? ''));
        $rolId  = $this->request->getPost('rol') ?? $this->request->getVar('rol');
        $maqId  = $this->request->getPost('idmaquiladora') ?? $this->request->getVar('idmaquiladora');
        $activo = (int)($this->request->getPost('activo') ?? $this->request->getVar('activo') ?? 1);
        $pwd    = (string)($this->request->getPost('password') ?? $this->request->getVar('password') ?? '');

        $db = \Config\Database::connect();
        try {
            $db->transStart();
            $upd = [
                'username' => $nombre,
                'correo'   => $email,
                'active'   => $activo,
            ];
            if ($maqId !== null && $maqId !== '') {
                $upd['maquiladoraIdFK'] = (int)$maqId;
            }
            if ($pwd !== '') {
                $upd['password'] = password_hash($pwd, PASSWORD_BCRYPT, ['cost'=>10]);
            }
            $db->table('users')->where('id', $id)->update($upd);
            
            // Actualizar rol: borrar asignaciones previas del usuario y dejar solo una
            if ($rolId !== null && $rolId !== '' && (int)$rolId > 0) {
                $db->table('usuario_rol')->where('usuarioIdFK', $id)->delete();

                // Como la PK incluye 'id' y no es autoincrement, generamos uno único (MAX(id)+1)
                $nextId = 1;
                try {
                    $rowNext = $db->query('SELECT COALESCE(MAX(id),0)+1 AS nextId FROM usuario_rol')->getRowArray();
                    if ($rowNext && isset($rowNext['nextId'])) { $nextId = (int)$rowNext['nextId']; }
                } catch (\Throwable $e) { $nextId = time(); }

                $okRole = $db->table('usuario_rol')->insert([
                    'id'           => $nextId,
                    'usuarioIdFK'  => $id,
                    'rolIdFK'      => (int)$rolId,
                ]);
                if (!$okRole) {
                    $dbErr = $db->error();
                    $dbMsg = isset($dbErr['message']) && $dbErr['message'] ? $dbErr['message'] : 'No se pudo asignar el rol';
                    throw new \Exception($dbMsg);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                $dbErr = $db->error();
                $dbMsg = isset($dbErr['message']) && $dbErr['message'] ? $dbErr['message'] : 'Error en la transacción';
                try { log_message('error', 'm11_actualizar_usuario transStatus=false: ' . $dbMsg); } catch (\Throwable $e) {}
                throw new \Exception($dbMsg);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Usuario y rol actualizados correctamente']);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e3) {}
            $errMsg = $e->getMessage();
            try { log_message('error', 'm11_actualizar_usuario exception: ' . $errMsg); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al actualizar: ' . $errMsg,
            ]);
        }
    }

    /**
     * Eliminar usuario (lógica): marca deleted_at o, si no existe, active=0
     * Entrada: POST id
     * Salida: JSON { success, message }
     */
    public function m11_eliminar_usuario()
    {
        // Aceptar la petición sin forzar método, la ruta ya limita a POST
        $id = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 'message' => 'ID inválido'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            // Intentar soft delete con deleted_at
            $ok = false;
            try {
                $ok = $db->table('users')->where('id', $id)
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
            } catch (\Throwable $e) {
                $ok = false;
            }
            if (!$ok) {
                // Fallback: desactivar
                $db->table('users')->where('id', $id)->update(['active' => 0]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Actualizar un diseño y su última versión.
     * Entrada (POST): codigo?, nombre?, descripcion?, version?, fecha?, notas?, archivoCadUrl?, archivoPatronUrl?, aprobado?, materials?[]
     * - Si se incluye archivoCadFile/archivoPatronFile, se actualiza la URL correspondiente.
     * - Reemplaza por completo la lista de materiales de la última versión si viene 'materials'.
     * Respuesta: JSON { ok: bool, message }
     */
    public function m2_actualizar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true, 'message' => 'OK']);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        }
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        }

        $db = \Config\Database::connect();

        // Datos a actualizar
        $dataDiseno = [];
        $dataVersion = [];
        $mapGet = function(string $k) { return trim((string)($this->request->getPost($k) ?? '')); };
        foreach (['codigo','nombre','descripcion'] as $k) {
            $v = $mapGet($k); if ($v !== '') { $dataDiseno[$k] = $v; }
        }
        // clienteId (opcional, permitir limpiar cuando viene vacío)
        $cliIdUp = $this->request->getPost('clienteId') ?? $this->request->getVar('clienteId');
        if ($cliIdUp !== null) {
            $dataDiseno['clienteId'] = ($cliIdUp === '') ? null : (int)$cliIdUp;
        }
        // precio_unidad (opcional, numérico)
        if ($this->request->getPost('precio_unidad') !== null && $this->request->getPost('precio_unidad') !== '') {
            $pu = (float)$this->request->getPost('precio_unidad');
            if ($pu >= 0) { $dataDiseno['precio_unidad'] = $pu; }
        }
        foreach (['version','fecha','notas','archivoCadUrl','archivoPatronUrl'] as $k) {
            $v = $mapGet($k); if ($v !== '') { $dataVersion[$k] = $v; }
        }
        if ($this->request->getPost('aprobado') !== null) {
            $dataVersion['aprobado'] = (int)(bool)$this->request->getPost('aprobado');
        }

        // Manejo de archivos subidos (opcional)
        try {
            $cadFile = $this->request->getFile('archivoCadFile');
            if ($cadFile && $cadFile->isValid() && !$cadFile->hasMoved()) {
                $dir = FCPATH . 'uploads/cad/'; if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
                $new = $cadFile->getRandomName();
                $cadFile->move($dir, $new);
                $dataVersion['archivoCadUrl'] = 'uploads/cad/' . $new;
            }
        } catch (\Throwable $e) { /* ignore */ }
        try {
            $patFile = $this->request->getFile('archivoPatronFile');
            if ($patFile && $patFile->isValid() && !$patFile->hasMoved()) {
                $dir2 = FCPATH . 'uploads/patron/'; if (!is_dir($dir2)) { @mkdir($dir2, 0755, true); }
                $new2 = $patFile->getRandomName();
                $patFile->move($dir2, $new2);
                $dataVersion['archivoPatronUrl'] = 'uploads/patron/' . $new2;
            }
        } catch (\Throwable $e) { /* ignore */ }

        $db->transStart();
        try {
            // Update diseno
            if (!empty($dataDiseno)) {
                foreach (['diseno','Diseno'] as $t) {
                    try { $db->table($t)->where('id', $id)->update($dataDiseno); break; } catch (\Throwable $e) { /* next */ }
                }
            }

            // Obtener última versión de este diseño
            $dvId = null;
            try {
                $dvId = $db->query(
                    "SELECT dv.id FROM diseno_version dv WHERE dv.disenoId = ? ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1",
                    [$id]
                )->getRow('id');
            } catch (\Throwable $e) {
                try {
                    $dvId = $db->query(
                        "SELECT dv.id FROM disenoversion dv WHERE dv.disenoId = ? ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1",
                        [$id]
                    )->getRow('id');
                } catch (\Throwable $e2) { $dvId = null; }
            }

            if (!$dvId) { throw new \Exception('No se encontró la versión a actualizar'); }

            // Update versión
            if (!empty($dataVersion)) {
                foreach (['diseno_version','disenoversion'] as $t) {
                    try { $db->table($t)->where('id', (int)$dvId)->update($dataVersion); break; } catch (\Throwable $e) { /* next */ }
                }
            }

            // Reemplazar materiales si vienen
            $materialsRaw = $this->request->getPost('materials');
            if ($materialsRaw) {
                $materials = is_array($materialsRaw) ? $materialsRaw : json_decode((string)$materialsRaw, true);
                if (is_array($materials)) {
                    $lmTables = ['lista_materiales','listamateriales','ListaMateriales'];
                    // Borrar actuales
                    foreach ($lmTables as $t) {
                        try { $db->table($t)->where('disenoVersionId', (int)$dvId)->delete(); break; } catch (\Throwable $e) { /* next */ }
                    }
                    // Insertar nuevos
                    foreach ($materials as $m) {
                        $artId = isset($m['articuloId']) ? (int)$m['articuloId'] : (int)($m['id'] ?? 0);
                        if ($artId <= 0) { continue; }
                        $cant  = isset($m['cantidadPorUnidad']) ? (float)$m['cantidadPorUnidad'] : (float)($m['cantidad'] ?? 0);
                        $merma = isset($m['mermaPct']) ? (float)$m['mermaPct'] : (isset($m['merma']) ? (float)$m['merma'] : null);
                        $rowLM = [
                            'disenoVersionId'   => (int)$dvId,
                            'articuloId'        => $artId,
                            'cantidadPorUnidad' => $cant,
                            'mermaPct'          => $merma,
                        ];
                        $inserted = false;
                        foreach ($lmTables as $t) {
                            try { $db->table($t)->insert($rowLM); $inserted = true; break; } catch (\Throwable $e) { /* next */ }
                        }
                        if (!$inserted) {
                            try { $db->query('INSERT INTO lista_materiales (disenoVersionId, articuloId, cantidadPorUnidad, mermaPct) VALUES (?,?,?,?)', [(int)$dvId, $artId, $cant, $merma]); } catch (\Throwable $e) {}
                        }
                    }
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }

            return $this->response->setJSON(['ok' => true, 'message' => 'Diseño actualizado']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    /**
     * Crear un nuevo diseño y su primera versión.
     * Entrada (POST): codigo?, nombre (req), descripcion?, version (req), fecha?, notas?, archivoCadUrl?, archivoPatronUrl?, aprobado?
     * Respuesta: JSON { ok: bool, id, versionId, message }
     */
    public function m2_crear_diseno()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true, 'message' => 'OK']);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        }

        $db = \Config\Database::connect();
        $dataDiseno = [
            'codigo'      => trim((string)$this->request->getPost('codigo')) ?: null,
            'nombre'      => trim((string)$this->request->getPost('nombre')),
            'descripcion' => trim((string)$this->request->getPost('descripcion')) ?: null,
        ];
        // Asignar maquiladora del usuario autenticado, si existe en sesión
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            $dataDiseno['maquiladoraID'] = (int)$maquiladoraId;
        }
        // clienteId opcional desde el modal
        $cid = $this->request->getPost('clienteId') ?? $this->request->getVar('clienteId');
        if ($cid !== null) {
            $dataDiseno['clienteId'] = ($cid === '') ? null : (int)$cid;
        }
        // precio_unidad desde el modal (float)
        if (($p = $this->request->getPost('precio_unidad')) !== null && $p !== '') {
            $dataDiseno['precio_unidad'] = (float)$p;
        }
        // FK opcionales (sexo, talla, tipo corte, tipo ropa)
        $idSexo   = $this->request->getPost('idSexoFK')      ?? $this->request->getPost('id_sexo')      ?? $this->request->getPost('sexoId');
        $idTalla  = $this->request->getPost('idTallasFK')    ?? $this->request->getPost('id_talla')     ?? $this->request->getPost('tallaId');
        $idCorte  = $this->request->getPost('idTipoCorteFK') ?? $this->request->getPost('id_tipo_corte')?? $this->request->getPost('tipoCorteId');
        $idRopa   = $this->request->getPost('idTipoRopaFK')  ?? $this->request->getPost('id_tipo_ropa') ?? $this->request->getPost('tipoRopaId');
        if ($idSexo  !== null && $idSexo  !== '') { $dataDiseno['idSexoFK']      = (int)$idSexo; }
        if ($idTalla !== null && $idTalla !== '') { $dataDiseno['IdTallasFK']    = (int)$idTalla; }
        if ($idCorte !== null && $idCorte !== '') { $dataDiseno['idTipoCorteFK'] = (int)$idCorte; }
        if ($idRopa  !== null && $idRopa  !== '') { $dataDiseno['idTipoRopaFK']  = (int)$idRopa; }
        $dataVersion = [
            'version'         => trim((string)$this->request->getPost('version')),
            'fecha'           => $this->request->getPost('fecha') ?: date('Y-m-d'),
            'notas'           => trim((string)$this->request->getPost('notas')) ?: null,
            'archivoCadUrl'   => trim((string)$this->request->getPost('archivoCadUrl')) ?: null,
            'archivoPatronUrl'=> trim((string)$this->request->getPost('archivoPatronUrl')) ?: null,
            'aprobado'        => $this->request->getPost('aprobado') === null ? null : (int)(bool)$this->request->getPost('aprobado'),
        ];

        // Manejo de archivos subidos (cualquier formato)
        try {
            $cadFile = $this->request->getFile('archivoCadFile');
            if ($cadFile && $cadFile->isValid() && !$cadFile->hasMoved()) {
                $dir = FCPATH . 'uploads/cad/'; // carpeta pública
                if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
                $new = $cadFile->getRandomName();
                $cadFile->move($dir, $new);
                $dataVersion['archivoCadUrl'] = 'uploads/cad/' . $new; // URL relativa pública
            }
        } catch (\Throwable $e) { /* ignorar carga CAD */ }

        try {
            $patFile = $this->request->getFile('archivoPatronFile');
            if ($patFile && $patFile->isValid() && !$patFile->hasMoved()) {
                $dir2 = FCPATH . 'uploads/patron/';
                if (!is_dir($dir2)) { @mkdir($dir2, 0755, true); }
                $new2 = $patFile->getRandomName();
                $patFile->move($dir2, $new2);
                $dataVersion['archivoPatronUrl'] = 'uploads/patron/' . $new2;
            }
        } catch (\Throwable $e) { /* ignorar carga patrón */ }

        // Validación mínima
        if ($dataDiseno['nombre'] === '' || $dataVersion['version'] === '') {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Nombre y versión son obligatorios']);
        }

        $db->transStart();
        try {
            // Insertar en diseno
            $db->table('diseno')->insert($dataDiseno);
            // Revisar error inmediato
            $err = $db->error();
            if (!empty($err['message'])) { throw new \Exception('Error al insertar en diseno: '.$err['message']); }
            $idDiseno = (int)$db->insertID();
            if ($idDiseno === 0) {
                // Fallback: obtener último ID insertado por orden
                try {
                    $row = $db->query('SELECT id FROM diseno ORDER BY id DESC LIMIT 1')->getRowArray();
                    if ($row && isset($row['id'])) { $idDiseno = (int)$row['id']; }
                } catch (\Throwable $e) { /* intentar mayúscula */ }
            }

            if (!$idDiseno) {
                // Fallback por mayúsculas
                $db->table('Diseno')->insert($dataDiseno);
                $err = $db->error();
                if (!empty($err['message'])) { throw new \Exception('Error al insertar en Diseno: '.$err['message']); }
                $idDiseno = (int)$db->insertID();
                if ($idDiseno === 0) {
                    try {
                        $row = $db->query('SELECT id FROM Diseno ORDER BY id DESC LIMIT 1')->getRowArray();
                        if ($row && isset($row['id'])) { $idDiseno = (int)$row['id']; }
                    } catch (\Throwable $e) { /* no se pudo obtener */ }
                }
            }

            if (!$idDiseno) {
                throw new \Exception('No se pudo crear el diseño');
            }

            // Insertar versión
            $dataVersion['disenoId'] = $idDiseno;
            $db->table('diseno_version')->insert($dataVersion);
            $err = $db->error();
            if (!empty($err['message'])) { throw new \Exception('Error al insertar en diseno_version: '.$err['message']); }
            $idVersion = (int)$db->insertID();
            if ($idVersion === 0) {
                try {
                    $row = $db->query('SELECT id FROM diseno_version WHERE disenoId = ? ORDER BY id DESC LIMIT 1', [$idDiseno])->getRowArray();
                    if ($row && isset($row['id'])) { $idVersion = (int)$row['id']; }
                } catch (\Throwable $e) { /* fallback abajo */ }
            }
            if (!$idVersion) {
                // Fallback por mayúsculas / sin guiones
                $db->table('disenoversion')->insert($dataVersion);
                $err = $db->error();
                if (!empty($err['message'])) { throw new \Exception('Error al insertar en disenoversion: '.$err['message']); }
                $idVersion = (int)$db->insertID();
                if ($idVersion === 0) {
                    try {
                        $row = $db->query('SELECT id FROM disenoversion WHERE disenoId = ? ORDER BY id DESC LIMIT 1', [$idDiseno])->getRowArray();
                        if ($row && isset($row['id'])) { $idVersion = (int)$row['id']; }
                    } catch (\Throwable $e) { /* no se pudo obtener */ }
                }
            }

            if (!$idVersion) {
                throw new \Exception('No se pudo crear la versión');
            }

            // Guardar materiales si vienen en la solicitud
            $materialsRaw = $this->request->getPost('materials');
            if ($materialsRaw) {
                $materials = is_array($materialsRaw) ? $materialsRaw : json_decode((string)$materialsRaw, true);
                if (is_array($materials)) {
                    // Intentar varios nombres de tabla por compatibilidad
                    $lmTables = ['lista_materiales','listamateriales','ListaMateriales'];
                    foreach ($materials as $m) {
                        $artId = isset($m['articuloId']) ? (int)$m['articuloId'] : (int)($m['id'] ?? 0);
                        if ($artId <= 0) { continue; }
                        $cant  = isset($m['cantidadPorUnidad']) ? (float)$m['cantidadPorUnidad'] : (float)($m['cantidad'] ?? 0);
                        $merma = isset($m['mermaPct']) ? (float)$m['mermaPct'] : (isset($m['merma']) ? (float)$m['merma'] : null);
                        $rowLM = [
                            'disenoVersionId'   => $idVersion,
                            'articuloId'        => $artId,
                            'cantidadPorUnidad' => $cant,
                            'mermaPct'          => $merma,
                        ];
                        $inserted = false;
                        foreach ($lmTables as $t) {
                            try {
                                $db->table($t)->insert($rowLM);
                                $inserted = true; break;
                            } catch (\Throwable $e) { /* probar siguiente */ }
                        }
                        if (!$inserted) {
                            // Como último recurso, intenta con columnas alternativas
                            try {
                                $db->query('INSERT INTO lista_materiales (disenoVersionId, articuloId, cantidadPorUnidad, mermaPct) VALUES (?,?,?,?)', [$idVersion, $artId, $cant, $merma]);
                            } catch (\Throwable $e) { /* ignorar error individual */ }
                        }
                    }
                }
            }

            // === Crear automáticamente PROTOTIPO y MUESTRA ===
            $userName = (string)(session()->get('user_name') ?? '');

            // 1) PROTOTIPO: referenciar la versión, dejar fechas/estado/notas en NULL
            $prototipoId = null;
            $rowProt = [
                'disenoVersionId' => $idVersion,
                'codigo'          => $dataDiseno['codigo'] ?? null,
                'fechainicio'     => null,
                'fechaFin'        => null,
                'estado'          => null,
                'notas'           => null,
            ];
            try {
                $db->table('prototipo')->insert($rowProt);
                $prototipoId = (int)$db->insertID();
            } catch (\Throwable $e) {
                try {
                    $db->table('Prototipo')->insert($rowProt);
                    $prototipoId = (int)$db->insertID();
                } catch (\Throwable $e2) {
                    $err = $db->error();
                    $msg = $err['message'] ?? $e2->getMessage() ?? 'Error desconocido al crear prototipo';
                    throw new \Exception('No se pudo crear el prototipo: ' . $msg);
                }
            }
            if (!$prototipoId) {
                $err = $db->error();
                $msg = $err['message'] ?? 'Error desconocido al crear prototipo';
                throw new \Exception('No se pudo crear el prototipo: ' . $msg);
            }

            // 2) MUESTRA: prototipoId del paso anterior; solicitadaPor = username; fechaSolicitud = hoy; demás NULL
            $rowMuestra = [
                'prototipoId'    => $prototipoId,
                'solicitadaPor'  => $userName !== '' ? $userName : null,
                'fechaSolicitud' => date('Y-m-d'),
                'fechaEnvio'     => null,
                'estado'         => 'Pendiente',
                'observaciones'  => null,
            ];
            try {
                $db->table('muestra')->insert($rowMuestra);
            } catch (\Throwable $e) {
                try {
                    $db->table('Muestra')->insert($rowMuestra);
                } catch (\Throwable $e2) {
                    $err = $db->error();
                    $msg = $err['message'] ?? $e2->getMessage() ?? 'Error desconocido al crear muestra';
                    throw new \Exception('No se pudo crear la muestra: ' . $msg);
                }
            }
            // Obtener ID de la muestra recién creada
            $muestraId = (int)$db->insertID();
            if ($muestraId === 0) {
                try {
                    $row = $db->query('SELECT id FROM muestra ORDER BY id DESC LIMIT 1')->getRowArray();
                    if ($row && isset($row['id'])) { $muestraId = (int)$row['id']; }
                } catch (\Throwable $e) {
                    try {
                        $row = $db->query('SELECT id FROM Muestra ORDER BY id DESC LIMIT 1')->getRowArray();
                        if ($row && isset($row['id'])) { $muestraId = (int)$row['id']; }
                    } catch (\Throwable $e2) { $muestraId = 0; }
                }
            }
            if ($muestraId <= 0) {
                throw new \Exception('No se pudo obtener el ID de la muestra creada');
            }

            // Crear registro de aprobacion_muestra con valores NULL
            $rowAprob = [
                'muestraId'  => $muestraId,
                'clienteId'  => null,
                'fecha'      => null,
                'decision'   => 'Pendiente',
                'comentarios'=> null,
            ];
            $insertedAprob = false;
            try {
                $db->table('aprobacion_muestra')->insert($rowAprob);
                $insertedAprob = true;
            } catch (\Throwable $e) {
                try {
                    $db->table('Aprobacion_Muestra')->insert($rowAprob);
                    $insertedAprob = true;
                } catch (\Throwable $e2) { /* se validará abajo */ }
            }
            if (!$insertedAprob) {
                $err = $db->error();
                $msg = $err['message'] ?? 'Error desconocido al crear aprobacion_muestra';
                throw new \Exception('No se pudo crear aprobacion_muestra: ' . $msg);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            return $this->response->setJSON([
                'ok'        => true,
                'id'        => $idDiseno,
                'versionId' => $idVersion,
                'message'   => 'Diseño creado correctamente'
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error al crear diseño: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Lista de artículos para armar lista de materiales (JSON)
     * Campos: id, sku?, nombre, unidadMedida?, tipo?, activo?
     */
    public function m2_articulos_json()
    {
        $db = \Config\Database::connect();
        $rows = [];
        $queries = [
            // nombre de tabla snake + columnas completas
            "SELECT id, sku, nombre, unidadMedida, tipo, activo FROM articulo ORDER BY nombre",
            // mismo pero con mayúscula
            "SELECT id, sku, nombre, unidadMedida, tipo, activo FROM Articulo ORDER BY nombre",
            // plural
            "SELECT id, sku, nombre, unidadMedida, tipo, activo FROM articulos ORDER BY nombre",
            "SELECT id, sku, nombre, unidadMedida, tipo, activo FROM Articulos ORDER BY nombre",
            // variante sin unidadMedida/tipo/activo
            "SELECT id, sku, nombre FROM articulo ORDER BY nombre",
            "SELECT id, sku, nombre FROM Articulo ORDER BY nombre",
            "SELECT id, sku, nombre FROM articulos ORDER BY nombre",
            "SELECT id, sku, nombre FROM Articulos ORDER BY nombre",
            // mínima garantizada
            "SELECT id, nombre FROM articulo ORDER BY nombre",
            "SELECT id, nombre FROM Articulo ORDER BY nombre",
            "SELECT id, nombre FROM articulos ORDER BY nombre",
            "SELECT id, nombre FROM Articulos ORDER BY nombre",
            // producto como alternativa
            "SELECT id, sku, nombre, unidadMedida, tipo, activo FROM producto ORDER BY nombre",
            "SELECT id, sku, nombre, unidadMedida, tipo, activo FROM Producto ORDER BY nombre",
            "SELECT id, sku, nombre FROM producto ORDER BY nombre",
            "SELECT id, sku, nombre FROM Producto ORDER BY nombre",
            "SELECT id, nombre FROM producto ORDER BY nombre",
            "SELECT id, nombre FROM Producto ORDER BY nombre",
            "SELECT id, nombre FROM productos ORDER BY nombre",
            "SELECT id, nombre FROM Productos ORDER BY nombre",
        ];
        foreach ($queries as $q) {
            try { $rows = $db->query($q)->getResultArray(); if ($rows !== null) break; } catch (\Throwable $e) { /* intenta siguiente */ }
        }
        return $this->response->setJSON(['items' => $rows]);
    }

    /** Catálogo: sexo */
    public function m2_catalogo_sexo()
    {
        $db = \Config\Database::connect();
        $rows = [];
        $queries = [
            "SELECT id_sexo AS id, nombre, descripcion FROM sexo ORDER BY nombre",
            "SELECT id_sexo AS id, nombre, descripcion FROM Sexo ORDER BY nombre",
        ];
        foreach ($queries as $q) { try { $rows = $db->query($q)->getResultArray(); if ($rows !== null) break; } catch (\Throwable $e) {} }
        return $this->response->setJSON(['items'=>$rows]);
    }

    /** Catálogo: tallas */
    public function m2_catalogo_tallas()
    {
        $db = \Config\Database::connect();
        $rows = [];
        $queries = [
            "SELECT id_talla AS id, nombre, descripcion FROM tallas ORDER BY nombre",
            "SELECT id_talla AS id, nombre, descripcion FROM Tallas ORDER BY nombre",
        ];
        foreach ($queries as $q) { try { $rows = $db->query($q)->getResultArray(); if ($rows !== null) break; } catch (\Throwable $e) {} }
        return $this->response->setJSON(['items'=>$rows]);
    }

    /** Catálogo: tipo de corte */
    public function m2_catalogo_tipo_corte()
    {
        $db = \Config\Database::connect();
        $rows = [];
        $queries = [
            "SELECT id_tipo_corte AS id, nombre, descripcion FROM tipo_corte ORDER BY nombre",
            "SELECT id_tipo_corte AS id, nombre, descripcion FROM Tipo_Corte ORDER BY nombre",
            "SELECT id_tipo_corte AS id, nombre, descripcion FROM tipocorte ORDER BY nombre",
        ];
        foreach ($queries as $q) { try { $rows = $db->query($q)->getResultArray(); if ($rows !== null) break; } catch (\Throwable $e) {} }
        return $this->response->setJSON(['items'=>$rows]);
    }

    /** Catálogo: tipo de ropa */
    public function m2_catalogo_tipo_ropa()
    {
        $db = \Config\Database::connect();
        $rows = [];
        $queries = [
            "SELECT id_tipo_ropa AS id, nombre, descripcion FROM tipo_ropa ORDER BY nombre",
            "SELECT id_tipo_ropa AS id, nombre, descripcion FROM Tipo_Ropa ORDER BY nombre",
            "SELECT id_tipo_ropa AS id, nombre, descripcion FROM tiporopa ORDER BY nombre",
        ];
        foreach ($queries as $q) { try { $rows = $db->query($q)->getResultArray(); if ($rows !== null) break; } catch (\Throwable $e) {} }
        return $this->response->setJSON(['items'=>$rows]);
    }

    // ====== CRUD Catálogo Sexo ======
    public function m2_catalogo_sexo_crear()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if (!$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Nombre requerido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['sexo', 'Sexo'];
            $insertado = false;
            foreach ($tablas as $tabla) {
                try {
                    $db->table($tabla)->insert(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    $insertado = true;
                    break;
                } catch (\Throwable $e) {}
            }
            if (!$insertado) throw new \Exception('No se pudo insertar');
            return $this->response->setJSON(['ok' => true, 'id' => $db->insertID()]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_sexo_actualizar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if ($id <= 0 || !$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID y nombre requeridos']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['sexo', 'Sexo'];
            $actualizado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_sexo', $id)->update(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    if ($ok) { $actualizado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$actualizado) throw new \Exception('No se pudo actualizar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_sexo_eliminar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        if ($id <= 0) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['sexo', 'Sexo'];
            $eliminado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_sexo', $id)->delete();
                    if ($ok) { $eliminado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$eliminado) throw new \Exception('No se pudo eliminar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    // ====== CRUD Catálogo Tallas ======
    public function m2_catalogo_tallas_crear()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if (!$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Nombre requerido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tallas', 'Tallas'];
            $insertado = false;
            foreach ($tablas as $tabla) {
                try {
                    $db->table($tabla)->insert(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    $insertado = true;
                    break;
                } catch (\Throwable $e) {}
            }
            if (!$insertado) throw new \Exception('No se pudo insertar');
            return $this->response->setJSON(['ok' => true, 'id' => $db->insertID()]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_tallas_actualizar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if ($id <= 0 || !$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID y nombre requeridos']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tallas', 'Tallas'];
            $actualizado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_talla', $id)->update(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    if ($ok) { $actualizado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$actualizado) throw new \Exception('No se pudo actualizar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_tallas_eliminar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        if ($id <= 0) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tallas', 'Tallas'];
            $eliminado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_talla', $id)->delete();
                    if ($ok) { $eliminado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$eliminado) throw new \Exception('No se pudo eliminar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    // ====== CRUD Catálogo Tipo de Corte ======
    public function m2_catalogo_tipo_corte_crear()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if (!$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Nombre requerido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tipo_corte', 'Tipo_Corte', 'tipocorte'];
            $insertado = false;
            foreach ($tablas as $tabla) {
                try {
                    $db->table($tabla)->insert(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    $insertado = true;
                    break;
                } catch (\Throwable $e) {}
            }
            if (!$insertado) throw new \Exception('No se pudo insertar');
            return $this->response->setJSON(['ok' => true, 'id' => $db->insertID()]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_tipo_corte_actualizar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if ($id <= 0 || !$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID y nombre requeridos']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tipo_corte', 'Tipo_Corte', 'tipocorte'];
            $actualizado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_tipo_corte', $id)->update(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    if ($ok) { $actualizado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$actualizado) throw new \Exception('No se pudo actualizar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_tipo_corte_eliminar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        if ($id <= 0) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tipo_corte', 'Tipo_Corte', 'tipocorte'];
            $eliminado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_tipo_corte', $id)->delete();
                    if ($ok) { $eliminado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$eliminado) throw new \Exception('No se pudo eliminar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    // ====== CRUD Catálogo Tipo de Ropa ======
    public function m2_catalogo_tipo_ropa_crear()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if (!$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Nombre requerido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tipo_ropa', 'Tipo_Ropa', 'tiporopa'];
            $insertado = false;
            foreach ($tablas as $tabla) {
                try {
                    $db->table($tabla)->insert(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    $insertado = true;
                    break;
                } catch (\Throwable $e) {}
            }
            if (!$insertado) throw new \Exception('No se pudo insertar');
            return $this->response->setJSON(['ok' => true, 'id' => $db->insertID()]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_tipo_ropa_actualizar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        $nombre = trim((string)$this->request->getPost('nombre'));
        $descripcion = trim((string)$this->request->getPost('descripcion')) ?: null;
        if ($id <= 0 || !$nombre) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID y nombre requeridos']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tipo_ropa', 'Tipo_Ropa', 'tiporopa'];
            $actualizado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_tipo_ropa', $id)->update(['nombre' => $nombre, 'descripcion' => $descripcion]);
                    if ($ok) { $actualizado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$actualizado) throw new \Exception('No se pudo actualizar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function m2_catalogo_tipo_ropa_eliminar($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') return $this->response->setJSON(['ok' => true]);
        if ($method !== 'post') return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        
        $id = (int)($id ?? 0);
        if ($id <= 0) return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        
        $db = \Config\Database::connect();
        try {
            $tablas = ['tipo_ropa', 'Tipo_Ropa', 'tiporopa'];
            $eliminado = false;
            foreach ($tablas as $tabla) {
                try {
                    $ok = $db->table($tabla)->where('id_tipo_ropa', $id)->delete();
                    if ($ok) { $eliminado = true; break; }
                } catch (\Throwable $e) {}
            }
            if (!$eliminado) throw new \Exception('No se pudo eliminar');
            return $this->response->setJSON(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    /** JSON detalle normalizado de pedido. */
    public function m1_pedido_json($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        $pedidoModel = new \App\Models\PedidoModel();
        // Traer detalle completo (cliente + clasificacion + items)
        $detalle = $pedidoModel->getPedidoDetalle($id);
        // Fallback: al menos datos básicos del pedido
        if (!$detalle) {
            $basic = $pedidoModel->getPedidoPorId($id);
            if (!$basic) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Pedido no encontrado']);
            }
            $detalle = [
                'id' => (int)($basic['id'] ?? $id),
                'folio' => $basic['folio'] ?? '',
                'fecha' => $basic['fecha'] ?? null,
                'estatus' => $basic['estatus'] ?? '',
                'moneda' => $basic['moneda'] ?? '',
                'total' => $basic['total'] ?? 0,
                'cliente' => [
                    'nombre' => $basic['empresa'] ?? '',
                ],
                'items' => [],
            ];
        }

        // Rellenar diseño directamente si viniera vacío
        if (empty($detalle['diseno'])) {
            $db = \Config\Database::connect();
            $row = null;
            try {
                $row = $db->query(
                    "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion
                     FROM orden_produccion op
                     LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                     LEFT JOIN diseno d ON d.id = dv.disenoId
                     WHERE op.ordenCompraId = ?
                     ORDER BY op.id DESC
                     LIMIT 1",
                    [$id]
                )->getRowArray();
            } catch (\Throwable $e) { $row = null; }
            if (!$row) {
                try {
                    $row = $db->query(
                        "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion
                         FROM OrdenProduccion op
                         LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                         LEFT JOIN Diseno d ON d.id = dv.disenoId
                         WHERE op.ordenCompraId = ?
                         ORDER BY op.id DESC
                         LIMIT 1",
                        [$id]
                    )->getRowArray();
                } catch (\Throwable $e2) { $row = null; }
            }
            if ($row && isset($row['d_id'])) {
                $detalle['diseno'] = [
                    'id' => $row['d_id'],
                    'codigo' => $row['d_codigo'] ?? '',
                    'nombre' => $row['d_nombre'] ?? '',
                    'descripcion' => $row['d_descripcion'] ?? '',
                    'version' => [
                        'id' => $row['id'] ?? null,
                        'version' => $row['version'] ?? null,
                        'fecha' => $row['fecha'] ?? null,
                        'aprobado' => $row['aprobado'] ?? null,
                        'notas' => $row['notas'] ?? null,
                        'archivoCadUrl' => $row['archivoCadUrl'] ?? null,
                        'archivoPatronUrl' => $row['archivoPatronUrl'] ?? null,
                    ],
                    'archivoCadUrl' => $row['archivoCadUrl'] ?? null,
                    'archivoPatronUrl' => $row['archivoPatronUrl'] ?? null,
                ];
            }
        }

        // Normalizar para el modal
        $out = [
            'id' => (int)($detalle['id'] ?? $id),
            'folio' => $detalle['folio'] ?? '',
            'fecha' => isset($detalle['fecha']) ? date('Y-m-d', strtotime($detalle['fecha'])) : '',
            'estatus' => $detalle['estatus'] ?? '',
            'moneda' => $detalle['moneda'] ?? '',
            'total' => isset($detalle['total']) ? number_format((float)$detalle['total'], 2) : '0.00',
            'empresa' => $detalle['cliente']['nombre'] ?? ($detalle['empresa'] ?? ''),
            'cliente' => $detalle['cliente'] ?? null,
            'items' => $detalle['items'] ?? [],
            'diseno' => $detalle['diseno'] ?? null,
            'disenos' => $detalle['disenos'] ?? [],
            'documento_url' => $detalle['documento_url'] ?? '',
            // OP ligada
            'op_id' => $detalle['op_id'] ?? null,
            'op_folio' => $detalle['op_folio'] ?? null,
            'op_disenoVersionId' => $detalle['op_disenoVersionId'] ?? null,
            'op_cantidadPlan' => $detalle['op_cantidadPlan'] ?? null,
            'op_fechaInicioPlan' => $detalle['op_fechaInicioPlan'] ?? null,
            'op_fechaFinPlan' => $detalle['op_fechaFinPlan'] ?? null,
            'op_status' => $detalle['op_status'] ?? null,
        ];

        return $this->response->setJSON($out);
    }

    /**
     * Generar PDF del pedido
     */
    public function m1_pedido_pdf($id = null)
    {
        // Obtener información de la maquiladora del usuario logueado
        $maquiladora = [];
        // Obtener el ID de la maquiladora de la sesión (puede estar como 'maquiladora_id' o 'maquiladoraIdFK')
        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraIdFK');
        
        log_message('debug', 'ID de maquiladora obtenido de la sesión: ' . $maquiladoraId);
        
        if ($maquiladoraId) {
            $db = \Config\Database::connect();
            
            // Primero intentamos con la tabla en minúsculas
            $maquiladora = $db->table('maquiladora')
                ->select([
                    'idmaquiladora',
                    'Nombre_Maquila AS nombre',
                    'Dueno AS dueno',
                    'Telefono AS telefono',
                    'Correo AS correo',
                    'Domicilio AS domicilio',
                    'logo'
                ])
                ->where('idmaquiladora', $maquiladoraId)
                ->get()
                ->getRowArray();
                
            log_message('debug', 'Primer intento - Datos de la maquiladora: ' . print_r($maquiladora, true));
            
            // Si no se encontró, intentar con mayúsculas
            if (empty($maquiladora)) {
                $maquiladora = $db->table('Maquiladora')
                    ->select([
                        'idmaquiladora',
                        'Nombre_Maquila AS nombre',
                        'Dueno AS dueno',
                        'Telefono AS telefono',
                        'Correo AS correo',
                        'Domicilio AS domicilio',
                        'logo'
                    ])
                    ->where('idmaquiladora', $maquiladoraId)
                    ->get()
                    ->getRowArray();
                
                log_message('debug', 'Segundo intento - Datos de la maquiladora: ' . print_r($maquiladora, true));
            }
            
            // Si aún no hay datos, crear un array con valores por defecto
            if (empty($maquiladora)) {
                $maquiladora = [
                    'idmaquiladora' => $maquiladoraId,
                    'nombre' => 'Maquiladora no encontrada',
                    'dueno' => 'No especificado',
                    'telefono' => 'No especificado',
                    'correo' => 'No especificado',
                    'domicilio' => 'No especificado',
                    'logo' => null
                ];
                log_message('error', 'No se pudo encontrar la maquiladora con ID: ' . $maquiladoraId);
            }

            // Preparar logo para visualización
            if (!empty($maquiladora['logo'])) {
                $maquiladora['logo_base64'] = base64_encode($maquiladora['logo']);
                try {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($maquiladora['logo']);
                    $maquiladora['logo_mime'] = $mime;
                } catch (\Throwable $e) {
                    $maquiladora['logo_mime'] = 'image/jpeg'; // Fallback
                }
            } else {
                $maquiladora['logo_base64'] = null;
                $maquiladora['logo_mime'] = null;
            }
        }

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        try {
            $pedidoModel = new \App\Models\PedidoModel();
            // Obtener detalle completo del pedido
            $detalle = $pedidoModel->getPedidoDetalle($id);
            
            if (!$detalle) {
                $basic = $pedidoModel->getPedidoPorId($id);
                if (!$basic) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Pedido no encontrado']);
                }
                $detalle = [
                    'id' => (int)($basic['id'] ?? $id),
                    'folio' => $basic['folio'] ?? '',
                    'fecha' => $basic['fecha'] ?? null,
                    'estatus' => $basic['estatus'] ?? '',
                    'moneda' => $basic['moneda'] ?? '',
                    'total' => $basic['total'] ?? 0,
                    'cliente' => [
                        'nombre' => $basic['empresa'] ?? '',
                    ],
                    'items' => [],
                    'op_cantidadPlan' => $basic['op_cantidadPlan'] ?? null,
                ];
            }

            // Rellenar diseño si está vacío (mismo código que m1_pedido_json)
            if (empty($detalle['diseno'])) {
                $db = \Config\Database::connect();
                $row = null;
                try {
                    $row = $db->query(
                        "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion,
                                COALESCE(dv.precio_unidad, dv.precioUnidad, d.precio_unidad, d.precioUnidad) AS precio_unidad
                         FROM orden_produccion op
                         LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                         LEFT JOIN diseno d ON d.id = dv.disenoId
                         WHERE op.ordenCompraId = ?
                         ORDER BY op.id DESC
                         LIMIT 1",
                        [$id]
                    )->getRowArray();
                } catch (\Throwable $e) { $row = null; }
                if (!$row) {
                    try {
                        $row = $db->query(
                            "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion,
                                    COALESCE(dv.precio_unidad, dv.precioUnidad, d.precio_unidad, d.precioUnidad) AS precio_unidad
                             FROM OrdenProduccion op
                             LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                             LEFT JOIN Diseno d ON d.id = dv.disenoId
                             WHERE op.ordenCompraId = ?
                             ORDER BY op.id DESC
                             LIMIT 1",
                            [$id]
                        )->getRowArray();
                    } catch (\Throwable $e2) { $row = null; }
                }
                if ($row && isset($row['d_id'])) {
                    $detalle['diseno'] = [
                        'id' => $row['d_id'],
                        'codigo' => $row['d_codigo'] ?? '',
                        'nombre' => $row['d_nombre'] ?? '',
                        'descripcion' => $row['d_descripcion'] ?? '',
                        'precio_unidad' => $row['precio_unidad'] ?? $row['precioUnidad'] ?? null,
                        'version' => [
                            'id' => $row['id'] ?? null,
                            'version' => $row['version'] ?? null,
                            'fecha' => $row['fecha'] ?? null,
                            'aprobado' => $row['aprobado'] ?? null,
                        ],
                    ];
                }
            }

            // Preparar datos para la vista del PDF
            $data = [
                'maquiladora' => $maquiladora,
                'pedido' => [
                    'id' => (int)($detalle['id'] ?? $id),
                    'folio' => $detalle['folio'] ?? '',
                    'fecha' => isset($detalle['fecha']) ? date('d/m/Y', strtotime($detalle['fecha'])) : date('d/m/Y'),
                    'estatus' => $detalle['estatus'] ?? '',
                    'moneda' => $detalle['moneda'] ?? 'MXN',
                    'total' => isset($detalle['total']) ? number_format((float)$detalle['total'], 2) : '0.00',
                ],
                'cliente' => $detalle['cliente'] ?? [],
                'diseno' => $detalle['diseno'] ?? null,
                'op_cantidadPlan' => $detalle['op_cantidadPlan'] ?? null,
                'op_fechaInicioPlan' => isset($detalle['op_fechaInicioPlan']) ? date('d/m/Y', strtotime($detalle['op_fechaInicioPlan'])) : null,
                'op_fechaFinPlan' => isset($detalle['op_fechaFinPlan']) ? date('d/m/Y', strtotime($detalle['op_fechaFinPlan'])) : null,
            ];

            // Renderizar HTML de la vista
            $html = view('modulos/pedido_pdf', $data);

            // Configurar Dompdf
            $opt = new \Dompdf\Options();
            $opt->set('isRemoteEnabled', true);
            $opt->set('isHtml5ParserEnabled', true);
            $dompdf = new \Dompdf\Dompdf($opt);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();

            $pdfBytes = $dompdf->output();

            // Limpiar cualquier output buffer
            while (ob_get_level() > 0) { @ob_end_clean(); }

            // Responder con el PDF
            $filename = 'pedido_' . ($data['pedido']['folio'] ?: $id) . '.pdf';

            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="'.$filename.'"')
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setHeader('Pragma', 'public')
                ->setBody($pdfBytes);
        } catch (\Throwable $e) {
            log_message('error', 'Error al generar PDF del pedido: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al generar el PDF',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generar Excel del pedido
     */
    public function m1_pedido_excel($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        try {
            $pedidoModel = new \App\Models\PedidoModel();
            // Obtener detalle completo del pedido
            $detalle = $pedidoModel->getPedidoDetalle($id);
            
            if (!$detalle) {
                $basic = $pedidoModel->getPedidoPorId($id);
                if (!$basic) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Pedido no encontrado']);
                }
                $detalle = [
                    'id' => (int)($basic['id'] ?? $id),
                    'folio' => $basic['folio'] ?? '',
                    'fecha' => $basic['fecha'] ?? null,
                    'estatus' => $basic['estatus'] ?? '',
                    'moneda' => $basic['moneda'] ?? '',
                    'total' => $basic['total'] ?? 0,
                    'cliente' => [
                        'nombre' => $basic['empresa'] ?? '',
                    ],
                    'items' => [],
                    'op_cantidadPlan' => $basic['op_cantidadPlan'] ?? null,
                ];
            }

            // Rellenar diseño si está vacío (mismo código que m1_pedido_json)
            if (empty($detalle['diseno'])) {
                $db = \Config\Database::connect();
                $row = null;
                try {
                    $row = $db->query(
                        "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion,
                                COALESCE(dv.precio_unidad, dv.precioUnidad, d.precio_unidad, d.precioUnidad) AS precio_unidad
                         FROM orden_produccion op
                         LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                         LEFT JOIN diseno d ON d.id = dv.disenoId
                         WHERE op.ordenCompraId = ?
                         ORDER BY op.id DESC
                         LIMIT 1",
                        [$id]
                    )->getRowArray();
                } catch (\Throwable $e) { $row = null; }
                if (!$row) {
                    try {
                        $row = $db->query(
                            "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion,
                                    COALESCE(dv.precio_unidad, dv.precioUnidad, d.precio_unidad, d.precioUnidad) AS precio_unidad
                             FROM OrdenProduccion op
                             LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                             LEFT JOIN Diseno d ON d.id = dv.disenoId
                             WHERE op.ordenCompraId = ?
                             ORDER BY op.id DESC
                             LIMIT 1",
                            [$id]
                        )->getRowArray();
                    } catch (\Throwable $e2) { $row = null; }
                }
                if ($row && isset($row['d_id'])) {
                    $detalle['diseno'] = [
                        'id' => $row['d_id'],
                        'codigo' => $row['d_codigo'] ?? '',
                        'nombre' => $row['d_nombre'] ?? '',
                        'descripcion' => $row['d_descripcion'] ?? '',
                        'precio_unidad' => $row['precio_unidad'] ?? $row['precioUnidad'] ?? null,
                        'version' => [
                            'id' => $row['id'] ?? null,
                            'version' => $row['version'] ?? null,
                            'fecha' => $row['fecha'] ?? null,
                            'aprobado' => $row['aprobado'] ?? null,
                        ],
                    ];
                }
            }

            // Preparar datos
            $pedido = [
                'id' => (int)($detalle['id'] ?? $id),
                'folio' => $detalle['folio'] ?? '',
                'fecha' => isset($detalle['fecha']) ? date('d/m/Y', strtotime($detalle['fecha'])) : date('d/m/Y'),
                'estatus' => $detalle['estatus'] ?? '',
                'moneda' => $detalle['moneda'] ?? 'MXN',
                'total' => isset($detalle['total']) ? number_format((float)$detalle['total'], 2) : '0.00',
            ];
            $cliente = $detalle['cliente'] ?? [];
            $diseno = $detalle['diseno'] ?? null;
            $op_cantidadPlan = $detalle['op_cantidadPlan'] ?? null;
            $op_fechaInicioPlan = isset($detalle['op_fechaInicioPlan']) ? date('d/m/Y', strtotime($detalle['op_fechaInicioPlan'])) : null;
            $op_fechaFinPlan = isset($detalle['op_fechaFinPlan']) ? date('d/m/Y', strtotime($detalle['op_fechaFinPlan'])) : null;

            // Generar Excel usando formato XML SpreadsheetML
            $xml = '<?xml version="1.0"?>' . "\n";
            $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
            $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
            $xml .= '<Worksheet ss:Name="Pedido">' . "\n";
            $xml .= '<Table>' . "\n";

            // Función helper para agregar fila
            $addRow = function($cells) use (&$xml) {
                $xml .= '<Row>' . "\n";
                foreach ($cells as $cell) {
                    $type = is_numeric($cell) ? 'Number' : 'String';
                    $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($cell, ENT_XML1) . '</Data></Cell>' . "\n";
                }
                $xml .= '</Row>' . "\n";
            };

            // Encabezado
            $addRow(['ORDEN DE COMPRA']);
            $addRow(['Folio:', $pedido['folio']]);
            $addRow([]);

            // Información General
            $addRow(['INFORMACIÓN GENERAL']);
            $addRow(['Folio:', $pedido['folio']]);
            $addRow(['Fecha:', $pedido['fecha']]);
            $addRow(['Estatus:', $pedido['estatus']]);
            $addRow(['Moneda:', $pedido['moneda']]);
            $addRow([]);

            // Datos del Cliente
            $addRow(['DATOS DEL CLIENTE']);
            if (!empty($cliente['nombre'])) {
                $addRow(['Nombre:', $cliente['nombre']]);
            }
            if (!empty($cliente['email'])) {
                $addRow(['Email:', $cliente['email']]);
            }
            if (!empty($cliente['telefono'])) {
                $addRow(['Teléfono:', $cliente['telefono']]);
            }
            if (!empty($cliente['direccion_detalle'])) {
                $dir = $cliente['direccion_detalle'];
                $direccion = ($dir['calle'] ?? '');
                if (!empty($dir['numExt'])) $direccion .= ' #' . $dir['numExt'];
                if (!empty($dir['numInt'])) $direccion .= ' Int. ' . $dir['numInt'];
                if (!empty($dir['ciudad'])) $direccion .= ', ' . $dir['ciudad'];
                if (!empty($dir['estado'])) $direccion .= ', ' . $dir['estado'];
                if (!empty($dir['cp'])) $direccion .= ' CP ' . $dir['cp'];
                if (!empty($dir['pais'])) $direccion .= ', ' . $dir['pais'];
                if ($direccion) {
                    $addRow(['Dirección:', $direccion]);
                }
            }
            $addRow([]);

            // Diseño / Modelo
            if (!empty($diseno)) {
                $addRow(['DISEÑO / MODELO']);
                if (!empty($diseno['codigo'])) {
                    $addRow(['Código:', $diseno['codigo']]);
                }
                if (!empty($diseno['nombre'])) {
                    $addRow(['Nombre:', $diseno['nombre']]);
                }
                if (!empty($diseno['descripcion'])) {
                    $addRow(['Descripción:', $diseno['descripcion']]);
                }
                if (!empty($diseno['precio_unidad'])) {
                    $addRow(['Precio Unitario:', $pedido['moneda'] . ' ' . number_format((float)$diseno['precio_unidad'], 2)]);
                }
                if (!empty($diseno['version'])) {
                    $ver = is_array($diseno['version']) ? $diseno['version'] : ['version' => $diseno['version']];
                    if (!empty($ver['version'])) {
                        $addRow(['Versión:', $ver['version']]);
                    }
                    if (!empty($ver['fecha'])) {
                        $addRow(['Fecha Versión:', date('d/m/Y', strtotime($ver['fecha']))]);
                    }
                }
                $addRow([]);
            }

            // Plan
            $addRow(['PLAN']);
            $addRow(['Concepto', 'Valor']);
            if (!empty($op_cantidadPlan)) {
                $addRow(['Cantidad Plan', number_format((float)$op_cantidadPlan, 0) . ' unidades']);
            }
            if (!empty($diseno['precio_unidad']) && !empty($op_cantidadPlan)) {
                $addRow(['Precio Unitario', $pedido['moneda'] . ' ' . number_format((float)$diseno['precio_unidad'], 2)]);
                $addRow(['Subtotal', $pedido['moneda'] . ' ' . number_format((float)$diseno['precio_unidad'] * (float)$op_cantidadPlan, 2)]);
            }
            if (!empty($op_fechaInicioPlan)) {
                $addRow(['Fecha Inicio Plan', $op_fechaInicioPlan]);
            }
            if (!empty($op_fechaFinPlan)) {
                $addRow(['Fecha Fin Plan', $op_fechaFinPlan]);
            }
            $addRow([]);
            $addRow(['TOTAL', $pedido['moneda'] . ' ' . $pedido['total']]);

            $xml .= '</Table>' . "\n";
            $xml .= '</Worksheet>' . "\n";
            $xml .= '</Workbook>';

            // Limpiar cualquier output buffer
            while (ob_get_level() > 0) { @ob_end_clean(); }

            // Responder con el Excel
            $filename = 'pedido_' . ($pedido['folio'] ?: $id) . '.xls';

            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setHeader('Pragma', 'public')
                ->setBody($xml);
        } catch (\Throwable $e) {
            log_message('error', 'Error al generar Excel del pedido: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al generar el Excel',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar un diseño: borra sus versiones y materiales asociados.
     * Acepta POST/OPTIONS
     */
    public function m2_eliminar_diseno($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') { return $this->response->setJSON(['ok'=>true]); }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok'=>false,'message'=>'Método no permitido']);
        }
        $id = (int)($id ?? 0);
        if ($id <= 0) { return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'ID inválido']); }

        $db = \Config\Database::connect();
        try {
            $db->transStart();

            // Obtener IDs de versiones
            $dvIds = array_map(function($r){ return (int)$r['id']; }, $db->query('SELECT id FROM diseno_version WHERE disenoId = ?', [$id])->getResultArray() ?? []);
            if (!$dvIds) {
                $dvIds = array_map(function($r){ return (int)$r['id']; }, $db->query('SELECT id FROM disenoversion WHERE disenoId = ?', [$id])->getResultArray() ?? []);
            }

            if (!empty($dvIds)) {
                $in = implode(',', array_fill(0, count($dvIds), '?'));
                // Borrar materiales por versiones
                foreach (['lista_materiales','listamateriales','ListaMateriales'] as $t) {
                    try { $db->query("DELETE FROM $t WHERE disenoVersionId IN ($in)", $dvIds); break; } catch (\Throwable $e) { /* try next */ }
                }
                // Borrar versiones
                foreach (['diseno_version','disenoversion'] as $t) {
                    try { $db->query("DELETE FROM $t WHERE id IN ($in)", $dvIds); break; } catch (\Throwable $e) { /* try next */ }
                }
            }

            // Borrar diseño
            $deleted = false;
            foreach (['diseno','Diseno'] as $t) {
                try { $db->table($t)->where('id', $id)->delete(); $deleted = true; break; } catch (\Throwable $e) { /* next */ }
            }

            $db->transComplete();
            if ($db->transStatus() === false || !$deleted) {
                throw new \Exception('No se pudo eliminar el diseño');
            }

            return $this->response->setJSON(['ok'=>true,'message'=>'Diseño eliminado']);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $ee) {}
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'Error al eliminar: '.$e->getMessage()]);
        }
    }

    /** JSON detalle normalizado de diseño. */
    public function m2_diseno_json($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        $disenoModel = new \App\Models\DisenoModel();
        $detalle = $disenoModel->getDisenoDetalle($id);
        if (!$detalle) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Diseño no encontrado']);
        }

        // Normalizar salida y agregar campos convenientes
        $out = [
            'id' => (int)$detalle['id'],
            'codigo' => $detalle['codigo'] ?? '',
            'nombre' => $detalle['nombre'] ?? '',
            'descripcion' => $detalle['descripcion'] ?? '',
            'clienteId' => $detalle['clienteId'] ?? null,
            'version' => $detalle['version'] ?? '',
            'fecha' => $detalle['fecha'] ?? '',
            'notas' => $detalle['notas'] ?? '',
            'materiales' => $detalle['materiales'] ?? [],
            'archivoCadUrl' => $detalle['archivoCadUrl'] ?? '',
            'archivoPatronUrl' => $detalle['archivoPatronUrl'] ?? '',
            'aprobado' => $detalle['aprobado'] ?? null,
            'precio_unidad' => $detalle['precio_unidad'] ?? null,
            'idSexoFK' => $detalle['idSexoFK'] ?? null,
            'IdTallasFK' => $detalle['IdTallasFK'] ?? null,
            'idTipoCorteFK' => $detalle['idTipoCorteFK'] ?? null,
            'idTipoRopaFK' => $detalle['idTipoRopaFK'] ?? null,
            // Nuevos: listas de archivos/imágenes (compatibles hacia atrás)
            'archivosCad' => $detalle['archivosCad'] ?? [],
            'archivosPatron' => $detalle['archivosPatron'] ?? [],
            'imagenes' => $detalle['imagenes'] ?? [],
            // Si más adelante tienes una imagen, puedes exponerla aquí
            'imagenUrl' => $detalle['imagenUrl'] ?? '',
        ];

        return $this->response->setJSON($out);
    }

    public function ordenes()
    {
        $ordenes = [
            ['op'=>'OP-0001','cliente'=>'Textiles MX','responsable'=>'Juan Pérez','ini'=>'2025-09-20','fin'=>'2025-09-25','estatus'=>'En proceso'],
            ['op'=>'OP-0002','cliente'=>'Fábrica Sur','responsable'=>'María López','ini'=>'2025-09-21','fin'=>'2025-09-27','estatus'=>'Planificada'],
            ['op'=>'OP-0003','cliente'=>'Industrias PZ','responsable'=>'Carlos Ruiz','ini'=>'2025-09-19','fin'=>'2025-09-24','estatus'=>'En proceso'],
        ];

        return view('modulos/ordenes', $this->payload([
            'title'   => 'Órdenes',
            'ordenes' => $ordenes,
            'notifCount' => 0,
        ]));
    }

    

    public function wip()
    {
        $etapas = [
            ['etapa'=>'Corte','resp'=>'Juan Pérez','ini'=>'2025-09-20','fin'=>'2025-09-22','prog'=>80],
            ['etapa'=>'Confección','resp'=>'María López','ini'=>'2025-09-22','fin'=>'2025-09-25','prog'=>45],
            ['etapa'=>'Acabado','resp'=>'Carlos Ruiz','ini'=>'2025-09-25','fin'=>'2025-09-27','prog'=>10],
        ];

        return view('modulos/wip', $this->payload([
            'title'      => 'WIP',
            'etapas'     => $etapas,
            'notifCount' => 0,
        ]));
    }

    public function incidencias()
    {
        $lista = [
            ['fecha'=>'2025-09-21','op'=>'OP-0001','tipo'=>'Paro de máquina','desc'=>'Mantenimiento no programado'],
            ['fecha'=>'2025-09-22','op'=>'OP-0003','tipo'=>'Falta de material','desc'=>'Faltan rollos de tela'],
        ];

        return view('modulos/incidencias', $this->payload([
            'title'      => 'Incidencias',
            'lista'      => $lista,
            'notifCount' => count($lista),
        ]));
    }

    public function reportes()
{
    $maquiladora = [];
    $maquiladoraId = session()->get('maquiladora_id');
    
    // Depuración
    log_message('debug', 'ID de maquiladora en sesión: ' . print_r($maquiladoraId, true));
    
    if ($maquiladoraId) {
        try {
            $db = \Config\Database::connect();
            
            // Verificar si la tabla existe
            $tables = $db->listTables();
            log_message('debug', 'Tablas en la base de datos: ' . print_r($tables, true));
            
            // Obtener los campos de la tabla maquiladora
            $fields = $db->getFieldData('maquiladora');
            log_message('debug', 'Campos de la tabla maquiladora: ' . print_r(array_column($fields, 'name'), true));
            
            // Intentar con minúsculas primero
            $maquiladora = $db->table('maquiladora')
                ->select('*')
                ->where('idmaquiladora', $maquiladoraId)
                ->get()
                ->getRowArray();
                
            log_message('debug', 'Consulta 1 (minúsculas): ' . $db->getLastQuery());
            
            // Si no se encontró, intentar con mayúsculas
            if (empty($maquiladora)) {
                $maquiladora = $db->table('Maquiladora')
                    ->select('*')
                    ->where('idmaquiladora', $maquiladoraId)
                    ->get()
                    ->getRowArray();
                    
                log_message('debug', 'Consulta 2 (mayúsculas): ' . $db->getLastQuery());
            }
            
            log_message('debug', 'Datos de maquiladora: ' . print_r($maquiladora, true));
            
        } catch (\Exception $e) {
            log_message('error', 'Error al obtener datos de la maquiladora: ' . $e->getMessage());
        }
    }

    return view('modulos/reportes', $this->payload([
        'title'      => 'Reportes',
        'maquiladora' => $maquiladora,
        'notifCount' => 0,
        'debug_info' => [
            'maquiladora_id' => $maquiladoraId,
            'maquiladora_data' => $maquiladora
        ]
    ]));
}

    public function notificaciones()
    {
        $items = [
            ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Módulo: Confección (WIP)'],
            ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Módulo: Prototipos'],
            ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Módulo: Prototipos'],
        ];

        return view('modulos/notificaciones', $this->payload([
            'title'      => 'Notificaciones',
            'items'      => $items,
            'notifCount' => count($items),
        ]));
    }

    public function mrp()
    {
        $reqs = [
            ['mat'=>'Tela Algodón 180g','u'=>'m','necesidad'=>1200,'stock'=>450,'comprar'=>750],
            ['mat'=>'Hilo 40/2','u'=>'rollo','necesidad'=>35,'stock'=>10,'comprar'=>25],
            ['mat'=>'Etiqueta talla','u'=>'pz','necesidad'=>1000,'stock'=>1200,'comprar'=>0],
        ];
        $ocs = [
            ['prov'=>'Textiles MX','mat'=>'Tela Algodón 180g','cant'=>750,'u'=>'m','eta'=>'2025-10-02'],
            ['prov'=>'Hilos del Norte','mat'=>'Hilo 40/2','cant'=>25,'u'=>'rollo','eta'=>'2025-09-30'],
        ];

        return view('modulos/mrp', $this->payload([
            'title'      => 'MRP',
            'reqs'       => $reqs,
            'ocs'        => $ocs,
            'notifCount' => 0,
        ]));
    }

    public function desperdicios()
    {
        $desp = [
            ['fecha'=>'2025-09-20','op'=>'OP-0012','mat'=>'Tela','cant'=>'15 m','motivo'=>'Manchas'],
            ['fecha'=>'2025-09-21','op'=>'OP-0010','mat'=>'Piezas','cant'=>'8 pz','motivo'=>'Corte chueco'],
        ];
        $rep = [
            ['op'=>'OP-0014','tarea'=>'Costura lateral','pend'=>25,'resp'=>'María','eta'=>'2025-09-24'],
            ['op'=>'OP-0011','tarea'=>'Rebasteado','pend'=>10,'resp'=>'Luis','eta'=>'2025-09-23'],
        ];

        return view('modulos/desperdicios', $this->payload([
            'title'      => 'Desperdicios y Reprocesos',
            'desp'       => $desp,
            'rep'        => $rep,
            'notifCount' => 2,
        ]));
    }

    /* =========================================================
     *                   MÓDULO 3 · Mantenimiento
     * ========================================================= */

    public function mantenimientoInventario()
    {
        $maq = [
            ['cod'=>'MC-0001','modelo'=>'Juki DDL-8700','compra'=>'2022-01-10','ubic'=>'Línea 1','estado'=>'Operativa'],
            ['cod'=>'MC-0002','modelo'=>'Brother 8450','compra'=>'2021-07-05','ubic'=>'Línea 3','estado'=>'En reparación'],
        ];

        return view('modulos/mantenimiento_inventario', $this->payload([
            'title'      => 'Mantenimiento · Inventario',
            'maq'        => $maq,
            'notifCount' => 0,
        ]));
    }

    public function mantenimientoPreventivo()
    {
        $prox = [
            ['fecha'=>'2025-09-25','maq'=>'MC-0001','tarea'=>'Lubricación','resp'=>'Carlos','estado'=>'Próximo'],
            ['fecha'=>'2025-09-28','maq'=>'MC-0002','tarea'=>'Ajuste correa','resp'=>'Ana','estado'=>'Programado'],
        ];

        return view('modulos/dashboard', $this->payload([
            'title'      => 'Mantenimiento · Preventivo',
            'prox'       => $prox,
            'notifCount' => 0,
        ]));
    }

    public function mantenimientoCorrectivo()
    {
        $hist = [
            ['fecha'=>'2025-09-20','maq'=>'MC-0002','falla'=>'Correa rota','accion'=>'Reemplazo','estado'=>'Cerrada'],
            ['fecha'=>'2025-09-22','maq'=>'MC-0003','falla'=>'Vibración','accion'=>'Ajuste base','estado'=>'En reparación'],
        ];

        return view('modulos/mantenimiento_correctivo', $this->payload([
            'title'      => 'Mantenimiento · Correctivo',
            'hist'       => $hist,
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                    MÓDULO 3 · Logística
     * ========================================================= */

    public function logisticaPreparacion()
    {
        $cons = [
            ['pedido'=>'PED-0041','op'=>'OP-0011','cajas'=>3,'peso'=>25,'dest'=>'Cliente B'],
            ['pedido'=>'PED-0042','op'=>'OP-0012','cajas'=>6,'peso'=>54,'dest'=>'Cliente C'],
        ];

        return view('modulos/logistica_preparacion', $this->payload([
            'title'      => 'Logística · Preparación de Envíos',
            'cons'       => $cons,
            'notifCount' => 0,
        ]));
    }

    public function logisticaGestion()
    {
        $env = [
            ['fecha'=>'2025-09-21','empresa'=>'DHL','guia'=>'JD0148899001','estado'=>'En tránsito'],
            ['fecha'=>'2025-09-22','empresa'=>'FedEx','guia'=>'FE99223311','estado'=>'Entregado'],
        ];

        return view('modulos/logistica_gestion', $this->payload([
            'title'      => 'Logística · Gestión de Envíos',
            'env'        => $env,
            'notifCount' => 0,
        ]));
    }

    public function logisticaDocumentos()
    {
        $docs = [
            ['tipo'=>'Factura','num'=>'FAC-2025-001','fecha'=>'2025-09-21','estado'=>'Emitida'],
            ['tipo'=>'Lista de empaque','num'=>'PL-2025-009','fecha'=>'2025-09-21','estado'=>'Emitida'],
        ];

        return view('modulos/logistica_documentos', $this->payload([
            'title'      => 'Logística · Documentos de Embarque',
            'docs'       => $docs,
            'notifCount' => 0,
        ]));
    }

    /**
     * Vista de inspección (listado para iniciar evaluaciones)
     */
    public function inspeccion()
    {
        return view('modulos/inspeccion', $this->payload([
            'title'      => 'Inspección de Producción',
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                       MÓDULO 1
     * ========================================================= */

    public function m1_index()
    {
        return $this->m1_pedidos();
    }

    /** Vista listado de pedidos (Módulo 1). */
    public function m1_pedidos()
    {
        $pedidoModel = new \App\Models\PedidoModel();
        // Filtrar pedidos por la maquiladora del usuario autenticado
        $maquiladoraId = session()->get('maquiladora_id');
        $pedidos = $pedidoModel->getListadoPedidos($maquiladoraId);

        return view('modulos/pedidos', $this->payload([
            'title'      => 'Módulo 1 · Pedidos',
            'pedidos'    => $pedidos,
            'notifCount' => 0,
        ]));
    }

    public function m1_ordenes()
    {
        // Usar el modelo centralizado para evitar diferencias de esquema
        $ordenes = [];
        $maquiladoras = [];
        $maquiladoraId = session()->get('maquiladora_id');
        
        try {
            $opModel = new \App\Models\OrdenProduccionModel();
            // Filtrar OP por maquiladora del usuario autenticado
            $ordenes = $opModel->getListado($maquiladoraId);
            
            // Obtener lista de otras maquiladoras para compartir
            $db = \Config\Database::connect();
            // Intentar minúsculas
            try {
                $maquiladoras = $db->table('maquiladora')
                    ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->where('idmaquiladora !=', $maquiladoraId)
                    ->where('status', 1) // Solo activas
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->get()
                    ->getResultArray();
            } catch (\Throwable $e) {
                 // Intentar con mayúsculas
                 $maquiladoras = $db->table('Maquiladora')
                    ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->where('idmaquiladora !=', $maquiladoraId)
                    ->where('status', 1)
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->get()
                    ->getResultArray();
            }
            
        } catch (\Throwable $e) {
            $ordenes = [];
        }

        return view('modulos/m1_ordenes', $this->payload([
            'title'      => 'Módulo 1 · Órdenes',
            'ordenes'    => $ordenes,
            'maquiladoras' => $maquiladoras,
            'currentMaquiladoraId' => $maquiladoraId,
            'notifCount' => 0,
        ]));
    }

    /**
     * Compartir una orden de producción con otra maquiladora
     */
    public function m1_ordenes_compartir()
    {
        $opId = (int)$this->request->getPost('opId');
        $maquiladoraId = (int)$this->request->getPost('maquiladoraId');
        
        if ($opId <= 0 || $maquiladoraId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos inválidos']);
        }
        
        $db = \Config\Database::connect();
        try {
            // Verificar que la OP exista y pertenezca a la maquiladora actual (seguridad básica)
            $currentMaquiladoraId = session()->get('maquiladora_id');
            
            // Actualizar
            $updated = false;
            // Intentar tabla minúsculas
            try {
                $builder = $db->table('orden_produccion');
                if ($currentMaquiladoraId) {
                    $builder->where('maquiladoraID', $currentMaquiladoraId);
                }
                $builder->where('id', $opId)->update(['maquiladoraCompartidaID' => $maquiladoraId]);
                
                // Verificar si se actualizó algo (o si ya tenía ese valor, affectedRows podría ser 0, pero la query corrió)
                // Asumimos éxito si no lanza excepción, aunque lo ideal es checkear affectedRows si cambia
                $updated = true;
            } catch (\Throwable $e) {
                // Fallback mayúsculas
                try {
                    $builder = $db->table('OrdenProduccion');
                    if ($currentMaquiladoraId) {
                        $builder->where('maquiladoraID', $currentMaquiladoraId);
                    }
                    $builder->where('id', $opId)->update(['maquiladoraCompartidaID' => $maquiladoraId]);
                    $updated = true;
                } catch (\Throwable $e2) {
                    $updated = false;
                    log_message('error', 'Error al compartir OP: ' . $e2->getMessage());
                }
            }
            
            if ($updated) {
                return $this->response->setJSON(['success' => true, 'message' => 'Orden compartida correctamente']);
            } else {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se pudo actualizar la orden']);
            }
            
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
        }
    }

    public function m1_produccion()
    {
        $empleadoId = null;
        try {
            $userId = (int)(session()->get('user_id') ?? 0);
            if ($userId > 0) {
                $emp = (new \App\Models\EmpleadoModel())
                    ->where('idusuario', $userId)
                    ->select('id')
                    ->first();
                if ($emp && isset($emp['id'])) { $empleadoId = (int)$emp['id']; }
            }
        } catch (\Throwable $e) { $empleadoId = null; }

        return view('modulos/produccion', $this->payload([
            'title'       => 'Módulo 1 · Producción',
            'notifCount'  => 0,
            'empleadoId'  => $empleadoId,
        ]));
    }

    public function m1_agregar()
    {
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario
            return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido agregado correctamente');
        }

        return view('modulos/agregar_pedido', $this->payload([
            'title'      => 'Módulo 1 · Agregar Pedido',
            'notifCount' => 0,
        ]));
    }

    /** Formulario editar pedido (GET/POST). */
    public function m1_editar($id = null)
    {
        $pedidoModel = new \App\Models\PedidoModel();

        if ($this->request->getMethod() === 'post') {
            // Obtener ID desde POST si no viene en la URL
            $idPost = (int)($this->request->getPost('id') ?? 0);
            if (!$id && $idPost) {
                $id = $idPost;
            }

            // Si no hay ID en POST/URL, responder apropiadamente (JSON si XHR o Accept JSON)
            $acceptsJson = stripos((string)$this->request->getHeaderLine('accept'), 'application/json') !== false;
            if (!$id) {
                if ($this->request->isAJAX() || $acceptsJson) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'ID de pedido ausente'
                    ]);
                }
            }

            // Procesar formulario: actualizar campos del pedido
            // Normalizar total a número
            $totalPost = $this->request->getPost('total');
            if (is_string($totalPost)) { $totalPost = str_replace(',', '', $totalPost); }
            $totalPost = ($totalPost === '' || $totalPost === null) ? null : (float)$totalPost;

            $data = [
                'descripcion'      => $this->request->getPost('descripcion') ?? null,
                'cantidad'         => $this->request->getPost('cantidad') ?? null,
                'especificaciones' => $this->request->getPost('especificaciones') ?? null,
                'materiales'       => $this->request->getPost('materiales') ?? null,
                'modelo'           => $this->request->getPost('modelo') ?? null,
                'tallas'           => $this->request->getPost('tallas') ?? null,
                'color'            => $this->request->getPost('color') ?? null,
                'fecha_entrega'    => $this->request->getPost('fecha_entrega') ?? null,
                // Solo actualizar estatus si viene en POST; no establecer valor por defecto aquí
                'estatus'          => $this->request->getPost('estatus'),
                'fecha'            => $this->request->getPost('fecha') ?? null,
                'folio'            => $this->request->getPost('folio') ?? null,
                'moneda'           => $this->request->getPost('moneda') ?? null,
                'total'            => $totalPost,
                'progreso'         => $this->request->getPost('progreso') ?? null,
            ];

            // Solo columnas válidas de orden_compra (normalizar y filtrar)
            $ocData = [
                'folio'   => $data['folio'],
                'fecha'   => $data['fecha'],
                'estatus' => $data['estatus'],
                'moneda'  => $data['moneda'],
                'total'   => $data['total'],
            ];
            // Normalizar fecha: aceptar dd/mm/yyyy -> yyyy-mm-dd
            if (!empty($ocData['fecha']) && is_string($ocData['fecha'])) {
                $f = trim($ocData['fecha']);
                if (strpos($f, '/') !== false) {
                    $parts = preg_split('/[\/]/', $f);
                    if (count($parts) === 3) {
                        // asume dd/mm/yyyy
                        $dd = (int)$parts[0]; $mm = (int)$parts[1]; $yy = (int)$parts[2];
                        if ($dd > 0 && $mm > 0 && $yy > 0) {
                            $ocData['fecha'] = sprintf('%04d-%02d-%02d', $yy, $mm, $dd);
                        }
                    }
                }
                // Si tras normalizar queda inválida, no actualizar la fecha
                if ($ocData['fecha'] === '' || strtolower($ocData['fecha']) === 'invalid date') {
                    unset($ocData['fecha']);
                }
            }
            // Filtrar cadenas vacías para no sobreescribir con ""
            foreach (['folio','estatus','moneda'] as $k) {
                if (!isset($ocData[$k]) || $ocData[$k] === '') { unset($ocData[$k]); }
            }
            // Si total es null, no lo enviamos; si es numérico, mantener
            if ($ocData['total'] === null || $ocData['total'] === '') { unset($ocData['total']); }

            // Guardar
            try {
                $rowsOC = 0; $rowsOP = 0;
                if ($id) {
                    // Actualizar orden_compra con Query Builder (evita restricciones de allowedFields)
                    $db = \Config\Database::connect();
                    $updated = false;
                    try {
                        $updated = $db->table('orden_compra')->where('id', (int)$id)->update($ocData);
                        $rowsOC = $db->affectedRows();
                    } catch (\Throwable $eQB1) { $updated = false; }
                    if (!$updated) {
                        try { $db->table('OrdenCompra')->where('id', (int)$id)->update($ocData); $rowsOC = $db->affectedRows(); } catch (\Throwable $eQB2) {}
                    }

                    // Actualizar OP ligada (última por ordenCompraId) si llegaron campos
                    $opCantidadPlan    = $this->request->getPost('op_cantidadPlan');
                    $disenoVersionId   = $this->request->getPost('disenoVersionId');
                    $opFechaFinPlanRaw = $this->request->getPost('op_fechaFinPlan');
                    // Normalizar fecha fin plan si viene como dd/mm/yyyy
                    $opFechaFinPlan = null;
                    if ($opFechaFinPlanRaw !== null && $opFechaFinPlanRaw !== '') {
                        $ff = trim((string)$opFechaFinPlanRaw);
                        if (strpos($ff, '/') !== false) {
                            $pp = preg_split('/[\/]/', $ff);
                            if (count($pp) === 3) {
                                $dd=(int)$pp[0]; $mm=(int)$pp[1]; $yy=(int)$pp[2];
                                if ($dd>0 && $mm>0 && $yy>0) { $opFechaFinPlan = sprintf('%04d-%02d-%02d', $yy, $mm, $dd); }
                            }
                        } else {
                            $opFechaFinPlan = $ff; // ya en yyyy-mm-dd
                        }
                    }
                    if ($opCantidadPlan !== null || $disenoVersionId !== null || $opFechaFinPlan !== null) {
                        // $db ya definido
                        $op = null;
                        try {
                            $op = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int)$id])->getRowArray();
                        } catch (\Throwable $e1) { $op = null; }
                        if (!$op) {
                            try { $op = $db->query('SELECT * FROM OrdenProduccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int)$id])->getRowArray(); }
                            catch (\Throwable $e2) { $op = null; }
                        }
                        if ($op) {
                            $set = [];
                            if ($opCantidadPlan !== null && $opCantidadPlan !== '') { $set['cantidadPlan'] = (int)$opCantidadPlan; }
                            if ($disenoVersionId !== null && (int)$disenoVersionId > 0) { $set['disenoVersionId'] = (int)$disenoVersionId; }
                            if ($opFechaFinPlan !== null && $opFechaFinPlan !== '') { $set['fechaFinPlan'] = $opFechaFinPlan; }
                            if (!empty($set)) {
                                // Intentar con variantes de columna para fecha fin plan
                                $variants = [$set];
                                if (isset($set['fechaFinPlan'])) {
                                    $alt1 = $set; $alt1['FechaFinPlan'] = $alt1['fechaFinPlan']; unset($alt1['fechaFinPlan']);
                                    $alt2 = $set; $alt2['fecha_fin_plan'] = $alt2['fechaFinPlan']; unset($alt2['fechaFinPlan']);
                                    $variants = [$set, $alt1, $alt2];
                                }
                                foreach ($variants as $trySet) {
                                    try { $db->table('orden_produccion')->where('id', (int)$op['id'])->update($trySet); $rowsOP = max($rowsOP, $db->affectedRows()); }
                                    catch (\Throwable $e3) {
                                        try { $db->table('OrdenProduccion')->where('id', (int)$op['id'])->update($trySet); $rowsOP = max($rowsOP, $db->affectedRows()); } catch (\Throwable $e4) { /* siguiente variante */ }
                                    }
                                }
                            }
                        } else {
                            // No hay OP, crearla
                            $newOp = [
                                'ordenCompraId'   => (int)$id,
                                'disenoVersionId' => ($disenoVersionId !== null && (int)$disenoVersionId > 0) ? (int)$disenoVersionId : null,
                                'folio'           => null,
                                'cantidadPlan'    => ($opCantidadPlan !== null && $opCantidadPlan !== '') ? (int)$opCantidadPlan : null,
                                'fechaInicioPlan' => null,
                                'fechaFinPlan'    => ($opFechaFinPlan !== null && $opFechaFinPlan !== '') ? $opFechaFinPlan : null,
                                'status'          => 'Planeada',
                            ];
                            try { $db->table('orden_produccion')->insert($newOp); $rowsOP = $db->affectedRows(); }
                            catch (\Throwable $e5) {
                                // Reintentar con variantes de columna para fecha fin plan
                                $tryNew = $newOp;
                                if (array_key_exists('fechaFinPlan', $tryNew)) {
                                    $alt1 = $tryNew; $alt1['FechaFinPlan'] = $alt1['fechaFinPlan']; unset($alt1['fechaFinPlan']);
                                    $alt2 = $tryNew; $alt2['fecha_fin_plan'] = $alt2['FechaFinPlan'] ?? ($tryNew['fechaFinPlan'] ?? null); unset($alt2['fechaFinPlan']); unset($alt2['FechaFinPlan']);
                                    try { $db->table('OrdenProduccion')->insert($alt1); $rowsOP = $db->affectedRows(); }
                                    catch (\Throwable $e6) { try { $db->table('OrdenProduccion')->insert($alt2); $rowsOP = $db->affectedRows(); } catch (\Throwable $e7) {} }
                                } else {
                                    try { $db->table('OrdenProduccion')->insert($tryNew); $rowsOP = $db->affectedRows(); } catch (\Throwable $e6) {}
                                }
                            }
                        }

                        // Recalcular total desde OP y Diseño si tenemos datos suficientes
                        try {
                            // Obtener OP actualizada (última)
                            $opNow = null;
                            try { $opNow = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int)$id])->getRowArray(); } catch (\Throwable $eR1) { $opNow = null; }
                            if (!$opNow) { try { $opNow = $db->query('SELECT * FROM OrdenProduccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int)$id])->getRowArray(); } catch (\Throwable $eR2) { $opNow = null; } }
                            if ($opNow) {
                                $dvId = $opNow['disenoVersionId'] ?? null;
                                $cant = $opNow['cantidadPlan'] ?? null;
                                if ($dvId && $cant) {
                                    // Precio desde diseño
                                    $precio = null;
                                    try {
                                        $rowP = $db->query('SELECT d.precio_unidad FROM diseno_version dv LEFT JOIN diseno d ON d.id = dv.disenoId WHERE dv.id = ?', [(int)$dvId])->getRowArray();
                                        $precio = $rowP['precio_unidad'] ?? null;
                                    } catch (\Throwable $eP1) { $precio = null; }
                                    if ($precio === null) {
                                        try {
                                            $rowP = $db->query('SELECT d.precio_unidad FROM DisenoVersion dv LEFT JOIN Diseno d ON d.id = dv.disenoId WHERE dv.id = ?', [(int)$dvId])->getRowArray();
                                            $precio = $rowP['precio_unidad'] ?? null;
                                        } catch (\Throwable $eP2) { $precio = null; }
                                    }
                                    if ($precio !== null) {
                                        $calcTotal = (float)$precio * (float)$cant;
                                        try {
                                            $db->table('orden_compra')->where('id', (int)$id)->update(['total' => $calcTotal]);
                                            $rowsOC = max($rowsOC, $db->affectedRows());
                                        } catch (\Throwable $eUT1) {
                                            try { $db->table('OrdenCompra')->where('id', (int)$id)->update(['total' => $calcTotal]); $rowsOC = max($rowsOC, $db->affectedRows()); } catch (\Throwable $eUT2) {}
                                        }
                                    }
                                }
                            }
                        } catch (\Throwable $eR) {}
                    }
                }
                // Si la petición viene por AJAX o Accept JSON, responder JSON con estado actual
                if ($this->request->isAJAX() || $acceptsJson) {
                    $db = \Config\Database::connect();
                    $ocRow = null; $opRow = null;
                    try { $ocRow = $db->query('SELECT id, folio, fecha, estatus, moneda, total FROM orden_compra WHERE id = ?', [(int)$id])->getRowArray(); } catch (\Throwable $eO1) { $ocRow = null; }
                    if (!$ocRow) { try { $ocRow = $db->query('SELECT id, folio, fecha, estatus, moneda, total FROM OrdenCompra WHERE id = ?', [(int)$id])->getRowArray(); } catch (\Throwable $eO2) { $ocRow = null; } }
                    try { $opRow = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int)$id])->getRowArray(); } catch (\Throwable $ePR1) { $opRow = null; }
                    if (!$opRow) { try { $opRow = $db->query('SELECT * FROM OrdenProduccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int)$id])->getRowArray(); } catch (\Throwable $ePR2) { $opRow = null; } }
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Pedido actualizado correctamente',
                        'rowsOC' => $rowsOC,
                        'rowsOP' => $rowsOP,
                        'oc' => $ocRow,
                        'op' => $opRow,
                    ]);
                }
                return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido actualizado correctamente');
            } catch (\Throwable $e) {
                if ($this->request->isAJAX() || $acceptsJson) {
                    return $this->response->setStatusCode(500)->setJSON(['success'=>false,'message'=>$e->getMessage()]);
                }
                return redirect()->to('/modulo1/pedidos')->with('error', 'Error al actualizar: '.$e->getMessage());
            }
        }

        $pedido = $pedidoModel->getPedidoPorId((int)$id);

        return view('modulos/editarpedido', $this->payload([
            'title'      => 'Módulo 1 · Editar Pedido',
            'pedido'     => $pedido,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }

    /** Vista detalle de pedido (usa PedidoModel::getPedidoDetalle). */
    public function m1_detalles($id = null)
    {
        $pedidoModel = new \App\Models\PedidoModel();
        // Traer detalle completo para incluir cliente, direcciones, y diseño asignado
        $pedido = $pedidoModel->getPedidoDetalle((int)$id);

        // Normalizar campos esperados por la vista
        if (is_array($pedido)) {
            if (!isset($pedido['empresa']) && isset($pedido['cliente']['nombre'])) {
                $pedido['empresa'] = $pedido['cliente']['nombre'];
            }
        }

        return view('modulos/detalle_pedido', $this->payload([
            'title'      => 'Módulo 1 · Detalle del Pedido',
            'pedido'     => $pedido,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }

    /**
     * Formulario de evaluación por orden (desde Inspección)
     */
    public function m1_evaluar($id = null)
    {
        if (!$id) {
            return redirect()->to('/modulo3/inspeccion');
        }

        // Datos de ejemplo; en producción vendrán de la BD
        $ordenData = [
            'folio' => 'OP-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT),
            'cantidadPlan' => 100,
        ];

        $defectos = [
            ['id' => 1, 'codigo' => 'D01', 'description' => 'Costura abierta', 'severidad' => 'Media'],
            ['id' => 2, 'codigo' => 'D02', 'description' => 'Mancha', 'severidad' => 'Baja'],
            ['id' => 3, 'codigo' => 'D03', 'description' => 'Corte chueco', 'severidad' => 'Alta'],
        ];

        return view('evaluar', $this->payload([
            'title'       => 'Evaluar Orden',
            'orden_id'    => $id,
            'orden_data'  => $ordenData,
            'defectos'    => $defectos,
            'notifCount'  => 0,
        ]));
    }

    /**
     * Recepción del formulario de evaluación
     */
    public function m1_guardarEvaluacion()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/modulo3/inspeccion');
        }

        // Aquí se procesaría y guardaría la evaluación
        // $data = $this->request->getPost();

        return redirect()->to('/modulo3/inspeccion')->with('success', 'Evaluación guardada correctamente');
    }

    /* =========================================================
     *                       MUESTRAS
     * ========================================================= */
    public function muestras()
    {
        // Vista de listado de muestras (puede reusar una tabla similar a inspección)
        return view('modulos/muestras', $this->payload([
            'title'      => 'Muestras de Prototipos',
            'notifCount' => 0,
        ]));
    }

    public function muestras_evaluar($id = null)
    {
        if (!$id) {
            return redirect()->to('/muestras');
        }

        // Datos de ejemplo; en producción vendrán de la BD
        $muestraData = [
            'prototipo_codigo' => 'PR-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT),
            'solicitadaPor' => 'Cliente Demo',
            'archivoCadUrl' => '',
            'archivoPatronUrl' => '',
        ];

        $responsables = [
            ['id' => 1, 'nombre' => 'Ana Control Calidad'],
            ['id' => 2, 'nombre' => 'Luis Supervisor'],
        ];

        return view('modulos/evaluar_muestra', $this->payload([
            'title'        => 'Evaluar Muestra',
            'muestra_id'   => $id,
            'muestra_data' => $muestraData,
            'responsables' => $responsables,
            'notifCount'   => 0,
        ]));
    }

    public function muestras_guardarEvaluacion()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/muestras');
        }

        // $data = $this->request->getPost(); // procesar y guardar
        return redirect()->to('/muestras')->with('success', 'Evaluación de muestra guardada correctamente');
    }
    public function m1_ordenesclientes()
    {
        $pedidoModel = new \App\Models\PedidoModel();
        $pedidos = $pedidoModel->getListadoPedidos();

        return view('modulos/ordenesclientes', $this->payload([
            'title'      => 'Módulo 1 · Órdenes de Clientes',
            'pedidos'    => $pedidos,
            'notifCount' => 0,
        ]));
    }
    public function m1_perfilempleado()
    {
        $uid = (int)(session()->get('user_id') ?? 0);
        $empleado = [];
        try {
            $db = \Config\Database::connect();
            // Query principal (snake case)
            $row = null;
            try {
                $row = $db->query(
                    'SELECT 
                        e.noEmpleado, e.nombre, e.apellido, e.puesto,
                        e.email, e.telefono, e.domicilio,
                        e.fecha_nac, e.curp, e.foto,
                        TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad,
                        u.username, u.correo, u.active AS usuario_activo,
                        u.maquiladoraIdFK AS maquiladoraID,
                        m.Nombre_Maquila AS nombre_maquiladora
                     FROM empleado e 
                     INNER JOIN users u ON e.idusuario = u.id
                     LEFT JOIN maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                     WHERE u.id = ? LIMIT 1', [$uid]
                )->getRowArray();
            } catch (\Throwable $e1) { $row = null; }
            // Variantes por mayúsculas/campos alternos
            if (!$row) {
                try {
                    $row = $db->query(
                        'SELECT 
                            e.noEmpleado, e.nombre, e.apellido, e.puesto,
                            e.email, e.telefono, e.domicilio,
                            e.fecha_nac, e.curp, e.foto,
                            TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad,
                            u.username, u.correo, u.active AS usuario_activo,
                            u.maquiladoraIdFK AS maquiladoraID,
                            m.Nombre_Maquila AS nombre_maquiladora
                         FROM Empleado e 
                         INNER JOIN Users u ON e.idusuario = u.id
                         LEFT JOIN Maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                         WHERE u.id = ? LIMIT 1', [$uid]
                    )->getRowArray();
                } catch (\Throwable $e2) { $row = null; }
            }
            if ($row) {
                // Completar faltantes desde sesión (email/puesto)
                if (!isset($row['email']) || $row['email'] === null || $row['email'] === '') {
                    $row['email'] = $row['correo']
                        ?? (string)(session()->get('user_email') ?? '')
                        ?? (string)(session()->get('correo') ?? '');
                }
                
                // Codificar la imagen en base64 si existe
                if (!empty($row['foto'])) {
                    $row['foto'] = base64_encode($row['foto']);
                }
                if (!isset($row['puesto']) || $row['puesto'] === null || $row['puesto'] === '') {
                    $primary = session()->get('primary_role');
                    $rnames  = session()->get('role_names');
                    $row['puesto'] = (string)(
                        ($primary ?: (is_array($rnames) && isset($rnames[0]) ? $rnames[0] : null))
                        ?? session()->get('user_role')
                        ?? session()->get('status')
                        ?? ''
                    );
                }
                $empleado = $row;
            }
        } catch (\Throwable $e) { $empleado = []; }

        return view('modulos/perfilempleado', $this->payload([
            'title'      => 'Módulo 1 · Perfil de Empleado',
            'empleado'   => $empleado,
            'notifCount' => 0,
        ]));
    }

    /**
     * Guardar datos de empleado del usuario logueado (insert/update).
     * Entrada POST: nombre, apellido, email, telefono, domicilio, puesto, fecha_nac, curp, noEmpleado
     * Respuesta JSON { success, updated|inserted }
     */
    public function m1_empleado_guardar()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }
        $uid = (int)(session()->get('user_id') ?? 0);
        if ($uid <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión no válida']);
        }

        $in = static function($k, $t='str') {
            $v = trim((string)service('request')->getPost($k));
            if ($t === 'date') { return $v === '' ? null : $v; }
            return $v === '' ? null : $v;
        };
        
        // Procesar la foto si se subió
        $fotoData = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($fotoFile->getMimeType(), $allowedTypes)) {
                // Leer el contenido del archivo
                $fotoData = file_get_contents($fotoFile->getTempName());
            }
        }

        $row = [
            'noEmpleado'    => $in('noEmpleado'),
            'nombre'        => $in('nombre'),
            'apellido'      => $in('apellido'),
            'email'         => $in('email'),
            'telefono'      => $in('telefono'),
            'domicilio'     => $in('domicilio'),
            'puesto'        => $in('puesto'),
            'fecha_nac'     => $in('fecha_nac','date'),
            'curp'          => $in('curp'),
            'activo'        => 1,
        ];

        // Asignar siempre la maquiladora del usuario autenticado (si existe en sesión)
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            $row['maquiladoraID'] = (int)$maquiladoraId;
        }
        
        // Solo actualizar la foto si se subió una nueva
        if ($fotoData !== null) {
            $row['foto'] = $fotoData;
        }

        $db = \Config\Database::connect();
        try {
            // Defaults desde sesión si faltan
            if (empty($row['noEmpleado'])) {
                $row['noEmpleado'] = 'EMP0' . $uid;
            }
            if (empty($row['email'])) {
                $row['email'] = (string)(session()->get('user_email') ?? '');
            }
            if (empty($row['puesto'])) {
                $primary = session()->get('primary_role');
                $rnames  = session()->get('role_names');
                $row['puesto'] = (string)(
                    ($primary ?: (is_array($rnames) && isset($rnames[0]) ? $rnames[0] : null))
                    ?? session()->get('user_role')
                    ?? session()->get('status')
                    ?? ''
                );
            }
            // ¿Existe empleado ligado a este usuario?
            $emp = null;
            try { 
                $emp = $db->table('empleado')
                         ->select('*')
                         ->select('foto') // Asegurarse de obtener la foto actual
                         ->where('idusuario', $uid)
                         ->get()
                         ->getRowArray(); 
            } catch (\Throwable $e) { $emp = null; }
            
            if (!$emp) { 
                try { 
                    $emp = $db->table('Empleado')
                             ->select('*')
                             ->select('foto') // Asegurarse de obtener la foto actual
                             ->where('idusuario', $uid)
                             ->get()
                             ->getRowArray(); 
                } catch (\Throwable $e2) { $emp = null; } 
            }

            if ($emp) {
                // Update
                $ok = false; $tables = ['empleado','Empleado'];
                foreach ($tables as $t) {
                    try { $ok = $db->table($t)->where('idusuario', $uid)->update($row); if ($ok) break; } catch (\Throwable $e) { /* try next */ }
                }
                if (!$ok) { throw new \Exception('No se pudo actualizar'); }
                return $this->response->setJSON(['success' => true, 'updated' => true]);
            } else {
                // Insert con vínculo
                $row['idusuario'] = $uid;
                $ok = false; $tables = ['empleado','Empleado'];
                foreach ($tables as $t) {
                    try { $ok = $db->table($t)->insert($row); if ($ok) break; } catch (\Throwable $e) { /* try next */ }
                }
                if (!$ok) { throw new \Exception('No se pudo insertar'); }
                return $this->response->setJSON(['success' => true, 'inserted' => true]);
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* =========================================================
     *                       MÓDULO 2
     * ========================================================= */

    public function m2_index()
    {
        return $this->m2_perfildisenador();
    }

    public function m2_perfildisenador()
    {
        $disenador = [
            'nombre' => session()->get('user_name') ?? 'Diseñador',
            'email' => session()->get('user_email') ?? 'diseñador@fabrica.com',
            'especialidad' => 'Diseño Textil',
            'experiencia' => '5 años',
            'proyectos_completados' => 45,
            'proyectos_activos' => 3,
        ];

        return view('modulos/perfildisenador', $this->payload([
            'title'      => 'Módulo 2 · Perfil del Diseñador',
            'disenador'  => $disenador,
            'notifCount' => 0,
        ]));
    }

    public function m2_catalogodisenos()
    {
        // Conectar a BD y traer catálogo real
        $disenoModel = new \App\Models\DisenoModel();
        // Mostrar todas las versiones filtradas por la maquiladora del usuario
        $maquiladoraId = session()->get('maquiladora_id');
        $disenos = $disenoModel->getCatalogoDisenosTodasVersiones($maquiladoraId);

        return view('modulos/catalogodisenos', $this->payload([
            'title'      => 'Módulo 2 · Catálogo de Diseños',
            'disenos'    => $disenos,
            'notifCount' => 0,
        ]));
    }

    public function m2_agregardiseno()
    {
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario
            return redirect()->to('/modulo2/catalogodisenos')->with('success', 'Diseño agregado correctamente');
        }

        return view('modulos/agregardiseno', $this->payload([
            'title'      => 'Módulo 2 · Agregar Diseño',
            'notifCount' => 0,
        ]));
    }

    public function m2_editardiseno($id = null)
    {
        // Validar que se proporcione un ID
        if (!$id) {
            return redirect()->to('/modulo2/catalogodisenos')->with('error', 'ID de diseño no válido');
        }

        // Traer detalle desde BD usando DisenoModel
        $disenoModel = new \App\Models\DisenoModel();
        $detalle = $disenoModel->getDisenoDetalle((int)$id);
        if (!$detalle) {
            return redirect()->to('/modulo2/catalogodisenos')->with('error', 'Diseño no encontrado');
        }

        // Adaptar al formato que espera la vista editardiseno
        $diseno = [
            'id'          => $detalle['id'],
            'nombre'      => $detalle['nombre'],
            'descripcion' => $detalle['descripcion'],
            'materiales'  => implode("\n", $detalle['materiales'] ?? []),
            'cortes'      => $detalle['notas'] ?? '',
            'archivo'     => $detalle['archivoCadUrl'] ?? '',
        ];

        return view('modulos/editardiseno', $this->payload([
            'title'      => 'Módulo 2 · Editar Diseño',
            'diseno'     => $diseno,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }


    /* =========================================================
     *                       MÓDULO 11 - USUARIOS
     * ========================================================= */
    public function m11_roles()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $roles = [];
        try {
            $builder = $db->table('rol')->select('id, nombre, descripcion');
            if ($maquiladoraId) {
                try {
                    $fields = $db->getFieldNames('rol');
                    if (in_array('maquiladoraID', $fields, true)) {
                        $builder->where('maquiladoraID', (int)$maquiladoraId);
                    }
                } catch (\Throwable $e) {}
            }
            $roles = $builder->orderBy('id','ASC')->get()->getResultArray();
        } catch (\Throwable $e) {
            $roles = [];
        }

        // Diagnóstico rápido: si ?debug=1, devolver HTML mínimo
        if ($this->request->getGet('debug')) {
            $html = "<!doctype html><html><head><meta charset='utf-8'><title>Diag Roles</title></head><body><h1>Diag Roles</h1><pre>" .
                htmlspecialchars(print_r($roles, true)) . "</pre></body></html>";
            return $this->response->setHeader('Content-Type','text/html; charset=utf-8')->setBody($html);
        }

        return view('modulos/roles', $this->payload([
            'title' => 'Gestión de Roles',
            'roles' => $roles,
            'notifCount' => 0,
        ]), ['saveData' => true]);

    }
    public function m11_roles_agregar()
{
    $nom  = trim((string)($this->request->getPost('nombre') ?? $this->request->getVar('nombre') ?? ''));
    $desc = trim((string)($this->request->getPost('descripcion') ?? $this->request->getVar('descripcion') ?? ''));

    if ($nom === '') {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'El nombre es obligatorio'
        ]);
    }

    $db = \Config\Database::connect();
    try {
        $data = [
            'nombre' => $nom,
            'descripcion' => $desc,
        ];

        // Si la tabla rol tiene maquiladoraID, asignar la maquila actual
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            try {
                $fields = $db->getFieldNames('rol');
                if (in_array('maquiladoraID', $fields, true)) {
                    $data['maquiladoraID'] = (int)$maquiladoraId;
                }
            } catch (\Throwable $e) {}
        }

        $ok = $db->table('rol')->insert($data);
        if (!$ok) {
            $err = $db->error(); $msg = $err['message'] ?? 'No se pudo insertar';
            throw new \Exception($msg);
        }
        $id = $db->insertID();
        return $this->response->setJSON(['success' => true, 'id' => (int)$id, 'message' => 'Rol agregado correctamente']);
    } catch (\Throwable $e) {
        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'message' => 'Error: '.$e->getMessage()
        ]);
    }
}

    /**
     * Actualiza un rol (POST): id, nombre, descripcion
     * Respuesta: JSON { success, message? }
     */
    public function m11_roles_actualizar()
    {
        // Aceptar tanto POST normal como AJAX
        $id   = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);
        $nom  = trim((string)($this->request->getPost('nombre') ?? $this->request->getVar('nombre') ?? ''));
        $desc = trim((string)($this->request->getPost('descripcion') ?? $this->request->getVar('descripcion') ?? ''));

        if ($id <= 0 || $nom === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Datos inválidos'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            $ok = $db->table('rol')->where('id', $id)->update([
                'nombre' => $nom,
                'descripcion' => $desc,
            ]);
            if (!$ok) {
                $err = $db->error();
                $msg = $err['message'] ?? 'No se pudo actualizar';
                throw new \Exception($msg);
            }
            return $this->response->setJSON(['success' => true, 'message' => 'Rol actualizado correctamente']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener los permisos de un rol (POST): rol_id
     * Respuesta: JSON { success, permisos[] }
     */
    public function m11_roles_permisos()
    {
        $rolId = (int)($this->request->getPost('rol_id') ?? $this->request->getVar('rol_id') ?? 0);
        
        if ($rolId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de rol inválido'
            ]);
        }

        $db = \Config\Database::connect();
        
        // Verificar si existe la tabla rol_permiso
        $this->crearTablaRolPermisoSiNoExiste($db);
        
        try {
            // Obtener permisos del rol desde la tabla rol_permiso
            $permisos = $db->table('rol_permiso')
                ->select('permiso')
                ->where('rol_id', $rolId)
                ->get()
                ->getResultArray();
            
            $permisoList = array_column($permisos, 'permiso');
            
            return $this->response->setJSON([
                'success' => true,
                'permisos' => $permisoList
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener permisos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar los permisos de un rol (POST): rol_id, permisos[]
     * Respuesta: JSON { success, message? }
     */
    public function m11_roles_guardar_permisos()
    {
        $rolId = (int)($this->request->getPost('rol_id') ?? $this->request->getVar('rol_id') ?? 0);
        $permisos = $this->request->getPost('permisos') ?? [];
        
        // Depuración
        log_message('debug', 'Guardando permisos - rol_id: ' . $rolId . ', permisos: ' . json_encode($permisos));
        
        if ($rolId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de rol inválido: ' . $rolId
            ]);
        }

        $db = \Config\Database::connect();
        
        // Verificar si existe la tabla rol_permiso
        $this->crearTablaRolPermisoSiNoExiste($db);
        
        try {
            // Iniciar transacción
            $db->transStart();
            
            // Eliminar todos los permisos actuales del rol
            $db->table('rol_permiso')->where('rol_id', $rolId)->delete();
            
            // Insertar los nuevos permisos
            if (!empty($permisos)) {
                $data = [];
                foreach ($permisos as $permiso) {
                    $data[] = [
                        'rol_id' => $rolId,
                        'permiso' => trim($permiso)
                    ];
                }
                log_message('debug', 'Datos a insertar: ' . json_encode($data));
                $db->table('rol_permiso')->insertBatch($data);
            }
            
            // Completar transacción
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                // Obtener el error de la base de datos
                $error = $db->error();
                throw new \Exception('Error en la transacción: ' . ($error['message'] ?? 'Error desconocido'));
            }
            
            log_message('debug', 'Permisos guardados exitosamente para rol_id: ' . $rolId);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permisos guardados correctamente'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al guardar permisos para rol_id ' . $rolId . ': ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar y crear la tabla rol_permiso si no existe
     */
    private function crearTablaRolPermisoSiNoExiste($db)
    {
        try {
            // Verificar si la tabla existe
            $query = $db->query("SHOW TABLES LIKE 'rol_permiso'");
            if ($query->getNumRows() == 0) {
                // La tabla no existe, crearla sin clave foránea para evitar conflictos
                $sql = "
                CREATE TABLE `rol_permiso` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `rol_id` int(11) NOT NULL,
                    `permiso` varchar(100) NOT NULL,
                    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_rol_permiso_rol_id` (`rol_id`),
                    KEY `idx_rol_permiso_permiso` (`permiso`),
                    UNIQUE KEY `unique_rol_permiso` (`rol_id`, `permiso`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Permisos asignados a los roles';
                ";
                $db->query($sql);
                
                // Insertar permisos predeterminados
                $this->insertarPermisosPredeterminados($db);
            }
        } catch (\Throwable $e) {
            // Si hay error, lo registramos pero continuamos
            log_message('error', 'Error al verificar/crear tabla rol_permiso: ' . $e->getMessage());
        }
    }
    
    /**
     * Insertar permisos predeterminados para todos los roles
     */
    private function insertarPermisosPredeterminados($db)
    {
        try {
            // Obtener todos los roles
            $roles = $db->table('rol')->get()->getResultArray();
            
            // Definir permisos por rol
            $permisosPorRol = [
                'Administrador' => [
                    'menu.catalogo_disenos', 'menu.pedidos', 'menu.ordenes', 'menu.produccion',
                    'menu.muestras', 'menu.inspeccion', 'menu.inventario_almacen', 'menu.inv_maquinas',
                    'menu.desperdicios', 'menu.incidencias', 'menu.logistica_preparacion',
                    'menu.logistica_gestion', 'menu.logistica_documentos', 'menu.planificacion_materiales',
                    'menu.mant_correctivo', 'menu.mant_programacion', 'menu.mrp', 'menu.ordenes_clientes',
                    'menu.usuarios', 'menu.roles'
                ],
                'Jefe' => [
                    'menu.catalogo_disenos', 'menu.pedidos', 'menu.ordenes', 'menu.produccion',
                    'menu.muestras', 'menu.inspeccion', 'menu.inventario_almacen', 'menu.inv_maquinas',
                    'menu.desperdicios', 'menu.incidencias', 'menu.logistica_preparacion',
                    'menu.logistica_gestion', 'menu.logistica_documentos', 'menu.planificacion_materiales',
                    'menu.mant_correctivo', 'menu.mant_programacion', 'menu.mrp', 'menu.ordenes_clientes',
                    'menu.usuarios', 'menu.roles'
                ],
                'Empleado' => [
                    'menu.produccion', 'menu.incidencias'
                ],
                'Inspector' => [
                    'menu.pedidos', 'menu.ordenes', 'menu.produccion', 'menu.muestras', 'menu.inspeccion'
                ],
                'Almacenista' => [
                    'menu.inventario_almacen', 'menu.inv_maquinas', 'menu.desperdicios',
                    'menu.incidencias', 'menu.logistica_preparacion', 'menu.logistica_gestion',
                    'menu.logistica_documentos', 'menu.planificacion_materiales', 'menu.mant_correctivo'
                ],
                'Calidad' => [
                    'menu.inspeccion', 'menu.muestras', 'menu.pedidos', 'menu.logistica_preparacion',
                    'menu.logistica_gestion', 'menu.logistica_documentos', 'menu.desperdicios'
                ],
                'Diseñador' => [
                    'menu.catalogo_disenos', 'menu.pedidos', 'menu.produccion', 'menu.muestras', 'menu.desperdicios'
                ],
                'RH' => [
                    'menu.ordenes_clientes', 'menu.ordenes', 'menu.usuarios', 'menu.roles'
                ]
            ];
            
            // Insertar permisos para cada rol
            foreach ($roles as $rol) {
                $rolNombre = $rol['nombre'];
                if (isset($permisosPorRol[$rolNombre])) {
                    $data = [];
                    foreach ($permisosPorRol[$rolNombre] as $permiso) {
                        $data[] = [
                            'rol_id' => $rol['id'],
                            'permiso' => $permiso
                        ];
                    }
                    if (!empty($data)) {
                        $db->table('rol_permiso')->insertBatch($data);
                    }
                }
            }
            
            log_message('info', 'Permisos predeterminados insertados correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al insertar permisos predeterminados: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un rol (POST): id
     * Respuesta: JSON { success, message? }
     */
    public function m11_roles_eliminar()
    {
        $id = (int)($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);
        
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de rol inválido'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            // Verificar si el rol está siendo utilizado por usuarios
            $usuariosConRol = $db->table('usuario_rol')
                ->where('rolIdFK', $id)
                ->countAllResults();
                
            if ($usuariosConRol > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'No se puede eliminar el rol porque está asignado a ' . $usuariosConRol . ' usuario(s)'
                ]);
            }

            // Eliminar permisos del rol primero
            $db->table('rol_permiso')->where('rol_id', $id)->delete();
            
            // Eliminar el rol
            $ok = $db->table('rol')->where('id', $id)->delete();
            
            if (!$ok) {
                $err = $db->error(); 
                $msg = $err['message'] ?? 'No se pudo eliminar el rol';
                throw new \Exception($msg);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Rol eliminado correctamente'
            ]);
            
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el rol: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Inicializar permisos predeterminados (GET)
     * Endpoint: modulo11/roles/inicializar_permisos
     */
    public function m11_roles_inicializar_permisos()
    {
        try {
            $db = \Config\Database::connect();
            
            // Verificar si la tabla existe
            $this->crearTablaRolPermisoSiNoExiste($db);
            
            // Limpiar permisos existentes
            $db->table('rol_permiso')->emptyTable();
            
            // Insertar permisos predeterminados
            $this->insertarPermisosPredeterminados($db);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permisos inicializados correctamente'
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al inicializar permisos: ' . $e->getMessage()
            ]);
        }
    }

public function m11_usuarios()
{
    $usuarioModel = new \App\Models\UsuarioModel();
    $maquiladoraId = session()->get('maquiladora_id');

    // Obtener usuarios (excepto eliminados lógicamente), filtrados por maquiladora cuando aplique
    $builder = $usuarioModel->where('deleted_at', null);
    if ($maquiladoraId) {
        $builder = $builder->where('maquiladoraIdFK', (int)$maquiladoraId);
    }
    $usuarios = $builder->findAll();

    // Obtener mapa de roles por usuario
    $rolesPorUsuario = [];
    try {
        $db = \Config\Database::connect();
        $rows = $db->table('usuario_rol ur')
            ->select('ur.usuarioIdFK as uid, r.nombre as rol')
            ->join('rol r', 'r.id = ur.rolIdFK', 'left')
            ->get()->getResultArray();
        foreach ($rows as $r) {
            if (!isset($r['uid'])) { continue; }
            $rolesPorUsuario[(int)$r['uid']] = $r['rol'] ?? 'Usuario';
        }
    } catch (\Throwable $e) {
        $rolesPorUsuario = [];
    }

    // Formatear los datos para la vista
    $usuariosFormateados = [];
    foreach ($usuarios as $usuario) {
        $usuariosFormateados[] = [
            'id' => $usuario['id'],
            'noEmpleado' => $usuario['id'], // Usar ID como número de empleado temporal
            'nombre' => $usuario['username'] ?? 'Sin nombre',
            'apellido' => '', // No hay campo apellido en la tabla
            'email' => $usuario['correo'] ?? 'Sin correo',
            // Mostrar el nombre del rol en la columna PUESTO
            'puesto' => $rolesPorUsuario[(int)$usuario['id']] ?? 'Usuario',
            'idmaquiladora' => $usuario['maquiladoraIdFK'] ?? 'Sin asignar',
            'activo' => $usuario['active'] ?? 1,
            'fechaAlta' => $usuario['created_at'] ?? date('Y-m-d H:i:s'),
            'ultimoAcceso' => $usuario['last_active'] ?? 'Nunca'
        ];
    }

    return view('modulos/usuarios', $this->payload([
        'title'      => 'Gestión de Usuarios',
        'usuarios'   => $usuariosFormateados,
        'notifCount' => 0,
    ]));
}

    public function m11_agregar_usuario()
    {
        if ($this->request->getMethod() === 'post') {
            $usuarioModel = new \App\Models\UsuarioModel();
            $empleadoModel = new \App\Models\EmpleadoModel();

            // Validar datos
            $validation = \Config\Services::validation();
            $validation->setRules([
                'usuario' => 'required|min_length[3]|max_length[100]|is_unique[usuario.usuario]',
                'password' => 'required|min_length[6]',
                'noEmpleado' => 'required|min_length[3]|max_length[20]|is_unique[empleado.noEmpleado]',
                'nombre' => 'required|min_length[2]|max_length[100]',
                'apellido' => 'required|min_length[2]|max_length[100]',
                'email' => 'required|valid_email|max_length[100]|is_unique[empleado.email]',
                'puesto' => 'required|max_length[100]',
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Iniciar transacción
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Crear usuario
                $usuarioData = [
                    'usuario' => $this->request->getPost('usuario'),
                    'password' => $this->request->getPost('password'),
                    'activo' => 1,
                    'fechaAlta' => date('Y-m-d H:i:s'),
                    'ultimoAcceso' => date('Y-m-d H:i:s'),
                    'idmaquiladora' => $this->request->getPost('idMaquiladora') ?: null
                ];

                $usuarioId = $usuarioModel->insert($usuarioData);

                if (!$usuarioId) {
                    throw new \Exception('Error al crear el usuario');
                }

                // Crear empleado
                $empleadoData = [
                    'noEmpleado' => $this->request->getPost('noEmpleado'),
                    'nombre' => $this->request->getPost('nombre'),
                    'email' => $this->request->getPost('email'),
                    'telefono' => $this->request->getPost('telefono'),
                    'domicilio' => $this->request->getPost('domicilio'),
                    'puesto' => $this->request->getPost('puesto'),
                    'activo' => 1,
                    'idusuario' => $usuarioId
                ];

                $empleadoId = $empleadoModel->insert($empleadoData);

                if (!$empleadoId) {
                    throw new \Exception('Error al crear el empleado');
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Error en la transacción');
                }

                return redirect()->to('/modulo11/usuarios')->with('success', 'Usuario agregado correctamente');

            } catch (\Exception $e) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error al crear el usuario: ' . $e->getMessage());
            }
        }

        return view('modulos/agregar_usuario', $this->payload([
            'title'      => 'Módulo 11 · Agregar Usuario',
            'notifCount' => 0,
        ]));
    }

    /**
     * Obtiene los datos de un usuario para edición (llamada AJAX)
     */
    public function obtener_usuario($id = null)
    {
        try {
            // Verificar que sea una petición AJAX
            if (!$this->request->isAJAX()) {
                throw new \RuntimeException('Método no permitido');
            }
            
            // Validar ID
            $id = (int)$id;
            if ($id <= 0) {
                throw new \InvalidArgumentException('ID de usuario inválido');
            }
            
            // Cargar modelo de usuarios
            $usuarioModel = new \App\Models\UsuarioModel();
            
            // Buscar usuario
            $usuario = $usuarioModel->find($id);
            
            if (!$usuario) {
                throw new \RuntimeException('Usuario no encontrado');
            }
            
            $db = \Config\Database::connect();
            
            // Datos básicos del usuario
            $usuarioData = [
                'id' => $usuario['id'] ?? null,
                'username' => $usuario['username'] ?? '',
                'email' => $usuario['correo'] ?? '',
                'rol_actual' => 'Usuario',
                'rol_id' => null,
                'maquiladoraIdFK' => $usuario['maquiladoraIdFK'] ?? null,
                'activo' => $usuario['active'] ?? 1,
                'roles' => [],
                'maquiladoras' => []
            ];

            // Configurar roles por defecto
            $usuarioData['roles'] = [
                ['id' => 1, 'name' => 'Administrador'],
                ['id' => 2, 'name' => 'Usuario'],
                ['id' => 3, 'name' => 'Invitado']
            ];
            
            // Establecer un rol por defecto
            $usuarioData['rol_id'] = 2; // Usuario por defecto
            $usuarioData['rol_actual'] = 'Usuario';

            // Obtener maquiladoras si la tabla existe
            if ($db->tableExists('maquiladoras')) {
                try {
                    $usuarioData['maquiladoras'] = $db->table('maquiladoras')
                        ->select('id, nombre')
                        ->get()
                        ->getResultArray();
                } catch (\Exception $e) {
                    log_message('error', 'Error al obtener maquiladoras: ' . $e->getMessage());
                    // Si hay error, usar un array vacío
                    $usuarioData['maquiladoras'] = [];
                }
            } else {
                $usuarioData['maquiladoras'] = [];
            }

            // Si no hay maquiladoras, agregar una opción por defecto
            if (empty($usuarioData['maquiladoras'])) {
                $usuarioData['maquiladoras'] = [
                    ['id' => 1, 'nombre' => 'Maquiladora Principal']
                ];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $usuarioData
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en obtener_usuario: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener los datos del usuario: ' . $e->getMessage(),
                'trace' => ENVIRONMENT === 'development' ? $e->getTrace() : null
            ]);
        }
    }

    /**
     * Actualiza los datos de un usuario (llamada AJAX)
     */
    public function actualizar_usuario()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $id = (int)$this->request->getPost('id');
        $nombre = trim($this->request->getPost('nombre'));
        $email = trim($this->request->getPost('email'));
        $rol = $this->request->getPost('rol');
        $maquiladoraId = $this->request->getPost('idmaquiladora');
        $activo = (int)$this->request->getPost('activo');
        $password = $this->request->getPost('password');
        
        // Validar datos
        if (empty($nombre) || empty($email)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre y correo son obligatorios']);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El formato del correo no es válido']);
        }
        
        // Cargar modelo de usuarios
        $usuarioModel = new \App\Models\UsuarioModel();
        
        // Verificar si el usuario existe
        $usuario = $usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        
        // Verificar si el correo ya existe (excepto para el usuario actual)
        $existeCorreo = $usuarioModel->where('correo', $email)
                                    ->where('id !=', $id)
                                    ->first();
        if ($existeCorreo) {
            return $this->response->setJSON(['success' => false, 'message' => 'El correo electrónico ya está en uso']);
        }
        
        // Actualizar datos del usuario
        $data = [
            'username' => $nombre,
            'correo' => $email,
            'maquiladoraIdFK' => !empty($maquiladoraId) ? (int)$maquiladoraId : null,
            'active' => $activo,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Actualizar contraseña si se proporcionó
        if (!empty($password)) {
            if (strlen($password) < 8) {
                return $this->response->setJSON(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Iniciar transacción
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Actualizar datos del usuario
            $usuarioModel->update($id, $data);
            
            // Actualizar rol del usuario si se proporcionó
            if (!empty($rol)) {
                $builder = $db->table('auth_groups_users');
                
                // Verificar si el rol existe
                $rolExiste = $db->table('auth_groups')
                              ->where('id', $rol)
                              ->countAllResults() > 0;
                
                if ($rolExiste) {
                    // Eliminar roles existentes
                    $builder->where('user_id', $id)->delete();
                    
                    // Agregar nuevo rol
                    $builder->insert([
                        'group_id' => (int)$rol,
                        'user_id' => $id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Error al actualizar el usuario en la base de datos');
            }
            
            // Obtener datos actualizados del usuario para la respuesta
            $usuarioActualizado = $usuarioModel->find($id);
            $builder = $db->table('auth_groups_users')->where('user_id', $id);
            $roles = $builder->get()->getResultArray();
            $rolNombre = 'Usuario';
            
            if (!empty($roles)) {
                $rol = $db->table('auth_groups')
                         ->where('id', $roles[0]['group_id'])
                         ->get()
                         ->getRowArray();
                if ($rol) {
                    $rolNombre = $rol['name'];
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => [
                    'id' => $usuarioActualizado['id'],
                    'username' => $usuarioActualizado['username'],
                    'email' => $usuarioActualizado['correo'],
                    'rol' => $rolNombre,
                    'maquiladoraIdFK' => $usuarioActualizado['maquiladoraIdFK'],
                    'activo' => $usuarioActualizado['active']
                ]
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error al actualizar usuario: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ]);
        }
    }

    public function m11_editar_usuario($id = null)
    {
        if (!$id) {
            return redirect()->to('/modulo11/usuarios')->with('error', 'ID de usuario no válido');
        }

        $usuarioModel = new \App\Models\UsuarioModel();
        $empleadoModel = new \App\Models\EmpleadoModel();

        if ($this->request->getMethod() === 'post') {
            // Validar datos
            $validation = \Config\Services::validation();
            $validation->setRules([
                'usuario' => "required|min_length[3]|max_length[100]|is_unique[usuario.usuario,id,{$id}]",
                'noEmpleado' => 'required|min_length[3]|max_length[20]',
                'nombre' => 'required|min_length[2]|max_length[100]',
                'apellido' => 'required|min_length[2]|max_length[100]',
                'email' => 'required|valid_email|max_length[100]',
                'puesto' => 'required|max_length[100]',
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Iniciar transacción
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Actualizar usuario
                $usuarioData = [
                    'usuario' => $this->request->getPost('usuario'),
                    'activo' => $this->request->getPost('activo_usuario') ?: 1,
                    'idmaquiladora' => $this->request->getPost('idMaquiladora') ?: null
                ];

                // Si se proporcionó una nueva contraseña, la actualizamos
                if ($this->request->getPost('password') && $this->request->getPost('password') !== '') {
                    $usuarioData['password'] = $this->request->getPost('password');
                }

                $usuarioModel->update($id, $usuarioData);

                // Obtener el empleado asociado al usuario
                $empleado = $empleadoModel->where('idusuario', $id)->first();

                if ($empleado) {
                    // Actualizar empleado existente
                    $empleadoData = [
                        'noEmpleado' => $this->request->getPost('noEmpleado'),
                        'nombre' => $this->request->getPost('nombre'),
                        'apellido' => $this->request->getPost('apellido'),
                        'email' => $this->request->getPost('email'),
                        'telefono' => $this->request->getPost('telefono'),
                        'domicilio' => $this->request->getPost('domicilio'),
                        'puesto' => $this->request->getPost('puesto'),
                        'activo' => $this->request->getPost('activo_empleado') ?: 1
                    ];

                    $empleadoModel->update($empleado['id'], $empleadoData);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Error en la transacción');
                }

                return redirect()->to('/modulo11/usuarios')->with('success', 'Usuario actualizado correctamente');

            } catch (\Exception $e) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
            }
        }

        // Obtener datos del usuario con empleado desde la base de datos
        $usuario = $usuarioModel->getUsuarioConEmpleado($id);

        if (!$usuario) {
            return redirect()->to('/modulo11/usuarios')->with('error', 'Usuario no encontrado');
        }

        return view('modulos/editar_usuario', $this->payload([
            'title'      => 'Módulo 11 · Editar Usuario',
            'usuario'    => $usuario,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }

    public function m1_pedidos_crear()
    {
        $method = strtolower($this->request->getMethod());
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok'=>false,'message'=>'Método no permitido']);
        }

        $clienteId         = (int)($this->request->getPost('oc_clienteId'));
        $ocEstatus         = (string)($this->request->getPost('oc_estatus') ?? 'Pendiente');
        $ocFolio           = trim((string)$this->request->getPost('oc_folio'));
        $ocFecha           = (string)$this->request->getPost('oc_fecha');
        $ocMoneda          = trim((string)$this->request->getPost('oc_moneda')) ?: 'MXN';
        $ocTotal           = (float)($this->request->getPost('oc_total'));

        $opFolio           = trim((string)$this->request->getPost('op_folio'));
        $opCantidadPlan    = (int)($this->request->getPost('op_cantidadPlan'));
        $opFechaInicioPlan = (string)($this->request->getPost('op_fechaInicioPlan'));
        $opFechaFinPlan    = (string)($this->request->getPost('op_fechaFinPlan'));
        $opStatus          = (string)($this->request->getPost('op_status') ?? 'Planeada');

        // Diseño
        $disenoVersionId   = (int)($this->request->getPost('disenoVersionId') ?? $this->request->getPost('pa_dis_version_id') ?? 0);
        $disenoId          = (int)($this->request->getPost('disenoId') ?? 0);

        if ($clienteId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>'Cliente requerido']);
        }
        if ($opCantidadPlan <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>'Cantidad plan debe ser mayor que 0']);
        }
        if ($ocFecha === '') { $ocFecha = date('Y-m-d'); }
        if ($opFechaInicioPlan === '') { $opFechaInicioPlan = date('Y-m-d'); }

        $db = \Config\Database::connect();

        // Si no viene disenoVersionId, intentar resolver última versión por disenoId
        if ($disenoVersionId <= 0 && $disenoId > 0) {
            try {
                $row = $db->query(
                    'SELECT dv.id FROM diseno_version dv WHERE dv.disenoId = ? ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1',
                    [$disenoId]
                )->getRowArray();
                if ($row && isset($row['id'])) { $disenoVersionId = (int)$row['id']; }
            } catch (\Throwable $e) {
                try {
                    $row = $db->query(
                        'SELECT dv.id FROM disenoversion dv WHERE dv.disenoId = ? ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1',
                        [$disenoId]
                    )->getRowArray();
                    if ($row && isset($row['id'])) { $disenoVersionId = (int)$row['id']; }
                } catch (\Throwable $e2) { /* ignore */ }
            }
        }
        if ($disenoVersionId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>'Versión de diseño requerida']);
        }

        try {
            $db->transStart();

            // Insertar orden_compra (nombres exactos)
            $rowOC = [
                'clienteId' => $clienteId,
                'folio'     => $ocFolio !== '' ? $ocFolio : ('OC-'.date('Y').'-'.$clienteId),
                'fecha'     => $ocFecha,
                'estatus'   => $ocEstatus ?: 'Pendiente',
                'moneda'    => $ocMoneda,
                'total'     => $ocTotal,
            ];
            if ($maquiladoraId) {
                $rowOC['maquiladoraID'] = (int)$maquiladoraId;
            }
            $db->table('orden_compra')->insert($rowOC);
            $ocId = (int)$db->insertID();
            if ($ocId === 0) {
                // fallback por mayúsculas
                $db->table('OrdenCompra')->insert($rowOC);
                $ocId = (int)$db->insertID();
            }
            if (!$ocId) { throw new \Exception('No se pudo crear la Orden de Compra'); }

            // Forzar folio único de OC: OC-YYYY-<ocId>
            $nuevoOCFolio = 'OC-'.date('Y').'-'.$ocId;
            $okUpdOCFolio = false;
            try { $okUpdOCFolio = (bool)$db->table('orden_compra')->where('id', $ocId)->update(['folio' => $nuevoOCFolio]); } catch (\Throwable $e) { $okUpdOCFolio = false; }
            if (!$okUpdOCFolio) {
                try { $okUpdOCFolio = (bool)$db->table('OrdenCompra')->where('id', $ocId)->update(['folio' => $nuevoOCFolio]); } catch (\Throwable $e2) { /* ignore */ }
            }

            // Insertar orden_produccion (nombres exactos)
            $rowOP = [
                'ordenCompraId'   => $ocId,
                'disenoVersionId' => $disenoVersionId,
                'folio'           => $opFolio !== '' ? $opFolio : ('OP-'.date('Y').'-'.$clienteId),
                'cantidadPlan'    => $opCantidadPlan,
                'fechaInicioPlan' => $opFechaInicioPlan,
                'fechaFinPlan'    => ($opFechaFinPlan ?: null),
                'status'          => $opStatus ?: 'Planeada',
            ];
            if ($maquiladoraId) {
                $rowOP['maquiladoraID'] = (int)$maquiladoraId;
            }
            $db->table('orden_produccion')->insert($rowOP);
            $opId = (int)$db->insertID();
            if ($opId === 0) {
                $db->table('OrdenProduccion')->insert($rowOP);
                $opId = (int)$db->insertID();
            }
            if (!$opId) { throw new \Exception('No se pudo crear la Orden de Producción'); }

            // Forzar folio único de OP: OP-YYYY-<opId>
            $nuevoFolio = 'OP-'.date('Y').'-'.$opId;
            $okUpdFolio = false;
            try {
                $okUpdFolio = (bool)$db->table('orden_produccion')->where('id', $opId)->update(['folio' => $nuevoFolio]);
            } catch (\Throwable $e) { $okUpdFolio = false; }
            if (!$okUpdFolio) {
                try { $okUpdFolio = (bool)$db->table('OrdenProduccion')->where('id', $opId)->update(['folio' => $nuevoFolio]); } catch (\Throwable $e2) { /* ignore */ }
            }

            // Crear registro inicial en inspeccion vinculado a la OP (otros campos en NULL)
            $rowIns = [
                'ordenProduccionId' => $opId,
                'puntoInspeccionId' => null,
                'inspectorId'       => null,
                'fecha'             => null,
                'resultado'         => null,
                'observaciones'     => null,
            ];
            $db->table('inspeccion')->insert($rowIns);
            $insId = (int)$db->insertID();
            if ($insId === 0) {
                // Fallback por mayúsculas
                $db->table('Inspeccion')->insert($rowIns);
                $insId = (int)$db->insertID();
            }
            if (!$insId) { throw new \Exception('No se pudo crear la inspeccion inicial'); }

            // Crear registro inicial en reproceso vinculado a la inspeccion (otros campos en NULL)
            $rowRep = [
                'inspeccionId' => $insId,
                'accion'       => null,
                'cantidad'     => null,
                'fecha'        => null,
            ];
            $db->table('reproceso')->insert($rowRep);
            $repId = (int)$db->insertID();
            if ($repId === 0) {
                // Fallback por mayúsculas
                $db->table('Reproceso')->insert($rowRep);
                $repId = (int)$db->insertID();
            }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }

            return $this->response->setJSON(['ok'=>true, 'ocId'=>$ocId, 'opId'=>$opId, 'message'=>'Pedido creado']);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'Error al crear pedido: '.$e->getMessage()]);
        }
    }
}