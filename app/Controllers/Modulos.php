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

        // $db ya definido arriba
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

            // Alias a 'name' para coincidir con el JS de la vista
            $roles = $db->table('rol')->select('id, nombre as name')->orderBy('nombre','ASC')->get()->getResultArray();

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
        return view('modulos/reportes', $this->payload([
            'title'      => 'Reportes',
            'notifCount' => 0,
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
        $pedidos = $pedidoModel->getListadoPedidos();

        return view('modulos/pedidos', $this->payload([
            'title'      => 'Módulo 1 · Pedidos',
            'pedidos'    => $pedidos,
            'notifCount' => 0,
        ]));
    }

    public function m1_ordenes()
    {
        // Traer órdenes reales: orden_produccion -> orden_compra -> cliente
        $db = \Config\Database::connect();
        $ordenes = [];

        // Variante minúsculas
        $sql = "SELECT 
                    COALESCE(op.folio, CONCAT('OP-', LPAD(op.id, 4, '0'))) AS op,
                    c.nombre AS cliente,
                    d.nombre AS diseno,
                    op.fechaInicioPlan AS ini,
                    op.fechaFinPlan AS fin,
                    op.status AS estatus
                FROM orden_produccion op
                LEFT JOIN orden_compra oc ON oc.id = op.ordenCompraId
                LEFT JOIN cliente c ON c.id = oc.clienteId
                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                LEFT JOIN diseno d ON d.id = dv.disenoId
                WHERE op.status IS NULL OR UPPER(op.status) <> 'COMPLETADA'
                ORDER BY op.fechaInicioPlan DESC, op.id DESC";
        try {
            $ordenes = $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            // Variante con mayúsculas en nombres de tabla
            $sql2 = "SELECT 
                        COALESCE(op.folio, CONCAT('OP-', LPAD(op.id, 4, '0'))) AS op,
                        c.nombre AS cliente,
                        d.nombre AS diseno,
                        op.fechaInicioPlan AS ini,
                        op.fechaFinPlan AS fin,
                        op.status AS estatus
                    FROM OrdenProduccion op
                    LEFT JOIN OrdenCompra oc ON oc.id = op.ordenCompraId
                    LEFT JOIN Cliente c ON c.id = oc.clienteId
                    LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                    LEFT JOIN Diseno d ON d.id = dv.disenoId
                    WHERE op.status IS NULL OR UPPER(op.status) <> 'COMPLETADA'
                    ORDER BY op.fechaInicioPlan DESC, op.id DESC";
            try {
                $ordenes = $db->query($sql2)->getResultArray();
            } catch (\Throwable $e2) {
                $ordenes = [];
            }
        }

        return view('modulos/m1_ordenes', $this->payload([
            'title'      => 'Módulo 1 · Órdenes',
            'ordenes'    => $ordenes,
            'notifCount' => 0,
        ]));
    }

    public function m1_produccion()
    {
        return view('modulos/produccion', $this->payload([
            'title'      => 'Módulo 1 · Producción',
            'notifCount' => 0,
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
                'estatus'          => $this->request->getPost('estatus') ?? 'Pendiente',
                'fecha'            => $this->request->getPost('fecha') ?? null,
                'folio'            => $this->request->getPost('folio') ?? null,
                'moneda'           => $this->request->getPost('moneda') ?? null,
                'total'            => $totalPost,
                'progreso'         => $this->request->getPost('progreso') ?? null,
            ];

            // Solo columnas válidas de orden_compra
            $ocData = [
                'folio'   => $data['folio'],
                'fecha'   => $data['fecha'],
                'estatus' => $data['estatus'],
                'moneda'  => $data['moneda'],
                'total'   => $data['total'],
            ];

            // Guardar
            try {
                if ($id) {
                    // Actualizar orden_compra con Query Builder (evita restricciones de allowedFields)
                    $db = \Config\Database::connect();
                    $updated = false;
                    try {
                        $updated = $db->table('orden_compra')->where('id', (int)$id)->update($ocData);
                    } catch (\Throwable $eQB1) { $updated = false; }
                    if (!$updated) {
                        try { $db->table('OrdenCompra')->where('id', (int)$id)->update($ocData); } catch (\Throwable $eQB2) {}
                    }

                    // Actualizar OP ligada (última por ordenCompraId) si llegaron campos
                    $opCantidadPlan    = $this->request->getPost('op_cantidadPlan');
                    $disenoVersionId   = $this->request->getPost('disenoVersionId');
                    if ($opCantidadPlan !== null || $disenoVersionId !== null) {
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
                            if (!empty($set)) {
                                try { $db->table('orden_produccion')->where('id', (int)$op['id'])->update($set); }
                                catch (\Throwable $e3) {
                                    try { $db->table('OrdenProduccion')->where('id', (int)$op['id'])->update($set); } catch (\Throwable $e4) {}
                                }
                            }
                        }
                    }
                }
                // Si la petición viene por AJAX, responder JSON
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => 'Pedido actualizado correctamente']);
                }
                return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido actualizado correctamente');
            } catch (\Throwable $e) {
                if ($this->request->isAJAX()) {
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
        $empleado = [
            'nombre' => session()->get('user_name') ?? 'Admin',
            'email' => session()->get('user_email') ?? 'admin@fabrica.com',
            'rol' => session()->get('user_role') ?? 'admin',
            'departamento' => 'Administración',
            'fecha_ingreso' => '2024-01-15',
            'telefono' => '+52 555 123 4567',
        ];

        return view('modulos/perfilempleado', $this->payload([
            'title'      => 'Módulo 1 · Perfil de Empleado',
            'empleado'   => $empleado,
            'notifCount' => 0,
        ]));
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
        // Mostrar todas las versiones (quitar filtro de "versión reciente")
        $disenos = $disenoModel->getCatalogoDisenosTodasVersiones();

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
        $roles = [];
        try {
            $roles = $db->table('rol')->select('id, nombre, descripcion')->orderBy('id','ASC')->get()->getResultArray();
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
        $ok = $db->table('rol')->insert([
            'nombre' => $nom,
            'descripcion' => $desc,
        ]);
        if (!$ok) {
            $err = $db->error(); $msg = $err['message'] ?? 'No se pudo insertar';
            throw new \Exception($msg);
        }
        $id = $db->insertID();
        return $this->response->setJSON(['success' => true, 'id' => (int)$id]);
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
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
public function m11_usuarios()
{
    $usuarioModel = new \App\Models\UsuarioModel();

    // Obtener todos los usuarios (excepto eliminados lógicamente)
    $usuarios = $usuarioModel->where('deleted_at', null)->findAll();

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
            $db->table('orden_compra')->insert($rowOC);
            $ocId = (int)$db->insertID();
            if ($ocId === 0) {
                // fallback por mayúsculas
                $db->table('OrdenCompra')->insert($rowOC);
                $ocId = (int)$db->insertID();
            }
            if (!$ocId) { throw new \Exception('No se pudo crear la Orden de Compra'); }

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
            $db->table('orden_produccion')->insert($rowOP);
            $opId = (int)$db->insertID();
            if ($opId === 0) {
                $db->table('OrdenProduccion')->insert($rowOP);
                $opId = (int)$db->insertID();
            }
            if (!$opId) { throw new \Exception('No se pudo crear la Orden de Producción'); }

            $db->transComplete();
            if ($db->transStatus() === false) { throw new \Exception('Error en la transacción'); }

            return $this->response->setJSON(['ok'=>true, 'ocId'=>$ocId, 'opId'=>$opId, 'message'=>'Pedido creado']);
        } catch (\Throwable $e) {
            try { $db->transRollback(); } catch (\Throwable $e2) {}
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'Error al crear pedido: '.$e->getMessage()]);
        }
    }
}