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
     * Actualizar un diseño y su última versión.
     * Entrada (POST): codigo?, nombre?, descripcion?, version?, fecha?, notas?, archivoCadUrl?, archivoPatronUrl?, aprobado?, materials?[]
     * - Si se incluye archivoCadFile/archivoPatronFile, se actualiza la URL correspondiente.
     * - Reemplaza por completo la lista de materiales de la última versión si viene 'materials'.
     * Respuesta: JSON { ok: bool, message }
     */
    public function m2_actualizar($id = null)
    {
        if ($this->request->getMethod() !== 'post') {
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

    /** Alias a dashboard(). */
    public function index()
    {
        return $this->dashboard();
    }

    /* =========================================================
     *                   MÓDULO 3 (Dashboard)
     * ========================================================= */

    /** Dashboard de gestión (demo). */
    public function dashboard()
    {
        $kpis = [
            ['label' => 'Órdenes Activas',     'value' => 8,  'color' => 'primary'],
            ['label' => 'WIP (%)',             'value' => 62, 'color' => 'info'],
            ['label' => 'Incidencias Hoy',     'value' => 3,  'color' => 'danger'],
            ['label' => 'Órdenes Completadas', 'value' => 21, 'color' => 'success'],
        ];


        return view('modulos/dashboard', $this->payload([
            'title' => 'Módulo 3 · Dashboard',
            'kpis'  => $kpis,
            'notifCount' => 3,
        ]));
    }

    /**
     * Crear un nuevo diseño y su primera versión.
     * Entrada (POST): codigo?, nombre (req), descripcion?, version (req), fecha?, notas?, archivoCadUrl?, archivoPatronUrl?, aprobado?
     * Respuesta: JSON { ok: bool, id, versionId, message }
     */
    public function m2_crear_diseno()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        }

        $db = \Config\Database::connect();
        $dataDiseno = [
            'codigo'      => trim((string)$this->request->getPost('codigo')) ?: null,
            'nombre'      => trim((string)$this->request->getPost('nombre')),
            'descripcion' => trim((string)$this->request->getPost('descripcion')) ?: null,
        ];
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
            $idDiseno = (int)$db->insertID();

            if (!$idDiseno) {
                // Fallback por mayúsculas
                $db->table('Diseno')->insert($dataDiseno);
                $idDiseno = (int)$db->insertID();
            }

            if (!$idDiseno) {
                throw new \Exception('No se pudo crear el diseño');
            }

            // Insertar versión
            $dataVersion['disenoId'] = $idDiseno;
            $db->table('diseno_version')->insert($dataVersion);
            $idVersion = (int)$db->insertID();
            if (!$idVersion) {
                // Fallback por mayúsculas / sin guiones
                $db->table('disenoversion')->insert($dataVersion);
                $idVersion = (int)$db->insertID();
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
        ];

        return $this->response->setJSON($out);
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
            'version' => $detalle['version'] ?? '',
            'fecha' => $detalle['fecha'] ?? '',
            'notas' => $detalle['notas'] ?? '',
            'materiales' => $detalle['materiales'] ?? [],
            'archivoCadUrl' => $detalle['archivoCadUrl'] ?? '',
            'archivoPatronUrl' => $detalle['archivoPatronUrl'] ?? '',
            'aprobado' => $detalle['aprobado'] ?? null,
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
                'total'            => $this->request->getPost('total') ?? null,
                'progreso'         => $this->request->getPost('progreso') ?? null,
            ];

            // Guardar
            if ($id) {
                $pedidoModel->where('id', (int)$id)->set($data)->update();
            }
            return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido actualizado correctamente');
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

    public function m11_usuarios()
    {
        $usuarioModel = new \App\Models\UsuarioModel();

        // Obtener usuarios con datos de empleado desde la base de datos
        $usuarios = $usuarioModel->getUsuariosConEmpleados();

        return view('modulos/usuarios', $this->payload([
            'title'      => 'Módulo 11 · Gestión de Usuarios',
            'usuarios'   => $usuarios,
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
                    'apellido' => $this->request->getPost('apellido'),
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
}