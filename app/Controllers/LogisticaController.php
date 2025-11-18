<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmbarqueModel;
use App\Models\EmbarqueItemModel;
use App\Models\OrdenCompraModel;
use App\Models\ClienteModel;
use App\Models\GuiaEnvioModel;
use App\Models\TransportistaModel;
use App\Models\DocumentoEnvioModel;

class LogisticaController extends BaseController
{
    /* =========================================================
     * Utilidades internas (detección de tablas/columnas)
     * =======================================================*/
    private function db()
    {
        return \Config\Database::connect();
    }

    private function tableExists(string $table): bool
    {
        try {
            return (bool) $this->db()->query('SHOW TABLES LIKE ' . $this->db()->escape($table))->getRowArray();
        } catch (\Throwable $e) { return false; }
    }

    /** @return array<string,bool> */
    private function hasCols(string $table, array $need): array
    {
        try { $cols = array_flip($this->db()->getFieldNames($table)); }
        catch (\Throwable $e) { $cols = []; }
        $out = [];
        foreach ($need as $c) $out[$c] = isset($cols[$c]);
        return $out;
    }

    /** Filtra $data a solo columnas existentes ($table). */
    private function filterToRealColumns(string $table, array $data): array
    {
        try { $cols = array_flip($this->db()->getFieldNames($table)); }
        catch (\Throwable $e) { $cols = []; }
        return array_intersect_key($data, $cols);
    }

    /* =========================================================
     *  PACKING · PREPARACIÓN
     * =======================================================*/
    public function preparacion()
    {
        $embarque = [];
        $clientes = [];
        $ordenes  = [];
        $maquiladoraId = session()->get('maquiladora_id');
        try { $embarque = (new EmbarqueModel())->getAbiertoActual($maquiladoraId ? (int)$maquiladoraId : null) ?? []; } catch (\Throwable $e) {}
        try { $clientes = (new ClienteModel())->listado($maquiladoraId ? (int)$maquiladoraId : null) ?? []; } catch (\Throwable $e) {}
        try { $ordenes  = (new OrdenCompraModel())->listarPendientes($maquiladoraId ? (int)$maquiladoraId : null) ?? []; } catch (\Throwable $e) {}

        return view('modulos/logistica_preparacion', compact('embarque','clientes','ordenes'));
    }

    public function crearEmbarque()
    {
        $folio     = trim((string) $this->request->getPost('folio'));
        $clienteId = (int) $this->request->getPost('clienteId');

        if (!$this->tableExists('embarque')) {
            return redirect()->back()->with('error', 'La tabla "embarque" no existe.');
        }

        $need = $this->hasCols('embarque', ['folio','clienteId']);
        if (($need['folio'] && $folio === '') || ($need['clienteId'] && $clienteId <= 0)) {
            return redirect()->back()->with('error', 'Folio y Cliente son obligatorios.');
        }

        $m = new EmbarqueModel();

        if ($need['folio'] && $this->hasCols('embarque',['estatus'])['estatus']) {
            $exist = $m->where('folio', $folio)->where('estatus', 'abierto')->first();
            if ($exist) {
                return redirect()->back()->with('ok', 'Usando embarque abierto #' . ($exist['id'] ?? ''));
            }
        }

        $payload = [
            'folio'     => $folio,
            'clienteId' => $clienteId,
            'fecha'     => date('Y-m-d'),
            'estatus'   => 'abierto',
        ];
        $data = $this->filterToRealColumns('embarque', $payload);
        if (empty($data)) {
            return redirect()->back()->with('error', 'No hay columnas válidas para guardar en "embarque".');
        }

        try { $id = $m->insert($data, true); }
        catch (\Throwable $e) { return redirect()->back()->with('error', 'No se pudo crear el embarque: '.$e->getMessage()); }

        return redirect()->back()->with('ok', 'Embarque creado #' . $id);
    }

    public function agregarOrden($embarqueId)
    {
        $embarqueId = (int) $embarqueId;
        $ordenId    = (int) $this->request->getPost('ordenId');

        if (!$this->tableExists('embarque') || !$this->tableExists('embarque_item')) {
            return redirect()->back()->with('error', 'Faltan tablas "embarque" o "embarque_item".');
        }

        if ($embarqueId <= 0 || $ordenId <= 0) {
            return redirect()->back()->with('error', 'Faltan datos para agregar al envío.');
        }

        $mEmb  = new EmbarqueModel();
        $mItem = new EmbarqueItemModel();

        $emb = $mEmb->find($embarqueId);
        $hasEstatus = $this->hasCols('embarque',['estatus'])['estatus'];
        if (!$emb || ($hasEstatus && ($emb['estatus'] ?? '') !== 'abierto')) {
            return redirect()->back()->with('error', 'El embarque no existe o no está abierto.');
        }

        $dupCheck = $this->hasCols('embarque_item',['embarqueId','ordenCompraId']);
        if ($dupCheck['embarqueId'] && $dupCheck['ordenCompraId']) {
            if ($mItem->where('embarqueId', $embarqueId)->where('ordenCompraId', $ordenId)->first()) {
                return redirect()->back()->with('ok', 'El pedido ya estaba agregado a este envío.');
            }
        }

        $payload = [
            'embarqueId'    => $embarqueId,
            'ordenCompraId' => $ordenId,
            'cantidad'      => 1,
            'unidadMedida'  => 'CJ',
        ];
        $data = $this->filterToRealColumns('embarque_item', $payload);
        if (empty($data)) {
            return redirect()->back()->with('error', 'No hay columnas válidas para guardar en "embarque_item".');
        }

        try { $mItem->insert($data); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo agregar: '.$e->getMessage()); }

        return redirect()->back()->with('ok', 'Pedido agregado al envío.');
    }

    /* ====== Botones Ver / Editar de pedidos ====== */

    public function ordenJson($id)
    {
        $id   = (int) $id;
        $row  = null; $cli = null;

        try { $row = (new OrdenCompraModel())->find($id); } catch (\Throwable $e) {}
        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado']);

        try {
            if (!empty($row['clienteId'])) {
                $cli = (new ClienteModel())->find((int)$row['clienteId']);
            }
        } catch (\Throwable $e) {}

        return $this->response->setJSON([
            'id'        => $row['id']         ?? null,
            'folio'     => $row['folio']      ?? null,
            'fecha'     => $row['fecha']      ?? null,
            'estatus'   => $row['estatus']    ?? null,
            'moneda'    => $row['moneda']     ?? null,
            'total'     => $row['total']      ?? null,
            'clienteId' => $row['clienteId']  ?? null,
            'cliente'   => $cli['nombre']     ?? null,
            'op'        => $row['op']         ?? null,
            'cajas'     => $row['cajas']      ?? null,
            'peso'      => $row['peso']       ?? null,
        ]);
    }

    public function ordenEditar($id)
    {
        $id  = (int) $id;
        $mOc = new OrdenCompraModel();

        try { $exists = (bool) $mOc->find($id); }
        catch (\Throwable $e) { $exists = false; }

        if (!$exists) return redirect()->back()->with('error', 'Orden no encontrada');

        $payload = array_filter([
            'folio'     => $this->request->getPost('folio'),
            'fecha'     => $this->request->getPost('fecha'),
            'estatus'   => $this->request->getPost('estatus'),
            'moneda'    => $this->request->getPost('moneda'),
            'total'     => $this->request->getPost('total'),
            'clienteId' => $this->request->getPost('clienteId'),
            'op'        => $this->request->getPost('op'),
            'cajas'     => $this->request->getPost('cajas'),
            'peso'      => $this->request->getPost('peso'),
        ], fn($v) => $v !== null && trim((string)$v) !== '');

        $table = property_exists($mOc, 'table') && !empty($mOc->table) ? $mOc->table : 'orden_compra';
        $data  = $this->filterToRealColumns($table, $payload);
        if (empty($data)) return redirect()->back()->with('error', 'Ningún campo editable coincide con la tabla.');

        try { $mOc->update($id, $data); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo actualizar: '.$e->getMessage()); }

        $ignored = array_diff(array_keys($payload), array_keys($data));
        $msg = 'Pedido actualizado';
        if (!empty($ignored)) $msg .= ' (se ignoraron: ' . implode(', ', $ignored) . ')';
        return redirect()->back()->with('ok', $msg);
    }

    /* ====== Placeholders documentos de packing ====== */
    public function packingList($id) { return redirect()->back()->with('ok', 'Packing List generado (demo)'); }
    public function etiquetas($id)   { return redirect()->back()->with('ok', 'Etiquetas generadas (demo)'); }

    /* =========================================================
     *  TRACKING · GESTIÓN DE ENVÍOS
     * =======================================================*/
    public function gestion()
    {
        $db = $this->db();
        $maquiladoraId = session()->get('maquiladora_id');

        if (!$this->tableExists('guia_envio')) {
            $transportistas = $this->tableExists('transportista')
                ? (new TransportistaModel())->orderBy('nombre','ASC')->findAll() : [];
            $embarques = $this->tableExists('embarque')
                ? $db->table('embarque')->select('id, folio')->orderBy('id','DESC')->get()->getResultArray() : [];

            session()->setFlashdata('warn', 'La tabla "guia_envio" no existe (vista en modo lectura vacía).');
            return view('modulos/logistica_gestion', [
                'envios'         => [],
                'transportistas' => $transportistas,
                'embarques'      => $embarques,
            ]);
        }

        $has  = $this->hasCols('guia_envio', ['fechaSalida','estado','numeroGuia','urlSeguimiento','embarqueId','transportistaId']);
        $tHas = $this->tableExists('transportista') ? $this->hasCols('transportista',['nombre']) : ['nombre'=>false];
        $eHas = $this->tableExists('embarque') ? $this->hasCols('embarque',['folio','fecha','estatus']) : ['folio'=>false,'fecha'=>false,'estatus'=>false];

        $select  = 'g.id';
        $select .= $has['numeroGuia']     ? ', g.numeroGuia'      : ', NULL AS numeroGuia';
        $select .= $has['urlSeguimiento'] ? ', g.urlSeguimiento'  : ', NULL AS urlSeguimiento';
        $select .= $has['embarqueId']     ? ', g.embarqueId'      : ', NULL AS embarqueId';
        $select .= $has['fechaSalida']    ? ', g.fechaSalida'     : ', NULL AS fechaSalida';
        $select .= $has['estado']         ? ', g.estado'          : ', NULL AS estado';
        $select .= $tHas['nombre']        ? ', t.nombre AS transportista' : ', NULL AS transportista';
        $select .= $eHas['folio']         ? ', e.folio AS embarque'       : ', NULL AS embarque';
        $select .= $eHas['fecha']         ? ', e.fecha AS fechaEmbarque'  : ', NULL AS fechaEmbarque';
        $select .= $eHas['estatus']       ? ', e.estatus AS estatusEmbarque' : ', NULL AS estatusEmbarque';

        $builder = $db->table('guia_envio g')->select($select);
        if ($this->tableExists('transportista')) $builder->join('transportista t','t.id=g.transportistaId','left');
        if ($this->tableExists('embarque'))      $builder->join('embarque e','e.id=g.embarqueId','left');

        // Filtro por maquiladora: primero por guia_envio.maquiladoraID, si no existe, por embarque.maquiladoraID
        if ($maquiladoraId) {
            try {
                $colsGuia = $this->hasCols('guia_envio', ['maquiladoraID']);
                $colsEmb  = $this->tableExists('embarque') ? $this->hasCols('embarque',['maquiladoraID']) : ['maquiladoraID'=>false];

                if ($colsGuia['maquiladoraID'] || $colsEmb['maquiladoraID']) {
                    $builder->groupStart();
                    if ($colsGuia['maquiladoraID']) {
                        $builder->where('g.maquiladoraID', (int)$maquiladoraId);
                    }
                    if ($colsEmb['maquiladoraID']) {
                        // Incluye también envíos cuyo embarque pertenezca a la maquila
                        $builder->orWhere('e.maquiladoraID', (int)$maquiladoraId);
                    }
                    $builder->groupEnd();
                }
            } catch (\Throwable $e) {
                // si algo falla, dejamos sin filtro extra
            }
        }

        $envios = $builder->orderBy('g.id','DESC')->get()->getResultArray();

        $transportistas = [];
        if ($this->tableExists('transportista')) {
            $tBuilder = (new TransportistaModel())->orderBy('nombre','ASC');
            // Si la tabla transportista tiene maquiladoraID, filtramos también
            try {
                $colsT = $this->hasCols('transportista',['maquiladoraID']);
                if ($maquiladoraId && $colsT['maquiladoraID']) {
                    $tBuilder = $tBuilder->where('maquiladoraID', (int)$maquiladoraId);
                }
            } catch (\Throwable $e) {}
            $transportistas = $tBuilder->findAll();
        }

        $embarques = [];
        if ($this->tableExists('embarque')) {
            $eBuilder = $db->table('embarque')->select('id, folio');
            try {
                $colsE = $this->hasCols('embarque',['maquiladoraID']);
                if ($maquiladoraId && $colsE['maquiladoraID']) {
                    $eBuilder->where('maquiladoraID', (int)$maquiladoraId);
                }
            } catch (\Throwable $e) {}
            $embarques = $eBuilder->orderBy('id','DESC')->get()->getResultArray();
        }

        return view('modulos/logistica_gestion', compact('envios','transportistas','embarques'));
    }

    public function crearEnvio()
    {
        if (!$this->tableExists('guia_envio')) {
            return redirect()->back()->with('error','La tabla "guia_envio" no existe.');
        }

        $m = new GuiaEnvioModel();

        $input = [
            'embarqueId'      => (int) $this->request->getPost('embarqueId'),
            'transportistaId' => (int) $this->request->getPost('transportistaId'),
            'numeroGuia'      => trim((string)$this->request->getPost('numeroGuia')),
            'urlSeguimiento'  => trim((string)$this->request->getPost('urlSeguimiento')),
            'fechaSalida'     => $this->request->getPost('fechaSalida'),
            'estado'          => $this->request->getPost('estado'),
        ];

        // Asignar maquiladora al envío si la columna existe
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            try {
                $colsG = $this->hasCols($m->table ?? 'guia_envio', ['maquiladoraID']);
                if ($colsG['maquiladoraID']) {
                    $input['maquiladoraID'] = (int)$maquiladoraId;
                }
            } catch (\Throwable $e) {}
        }

        $data = $this->filterToRealColumns($m->table ?? 'guia_envio', $input);

        $need = $this->hasCols($m->table ?? 'guia_envio', ['transportistaId','numeroGuia']);
        if (($need['transportistaId'] && empty($data['transportistaId'])) ||
            ($need['numeroGuia'] && empty($data['numeroGuia']))) {
            return redirect()->back()->with('error','Transportista y número de guía son obligatorios.');
        }

        if (empty($data)) return redirect()->back()->with('error','No hay columnas válidas para guardar.');

        try { $m->insert($data); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo guardar: '.$e->getMessage()); }

        return redirect()->back()->with('ok','Envío registrado.');
    }

    public function envioJson($id)
    {
        if (!$this->tableExists('guia_envio')) {
            return $this->response->setStatusCode(404)->setJSON(['error'=>'Tabla guia_envio no existe']);
        }

        $db = $this->db();
        $hG = $this->hasCols('guia_envio', ['embarqueId','transportistaId','numeroGuia','urlSeguimiento','fechaSalida','estado']);
        $tN = $this->tableExists('transportista') ? $this->hasCols('transportista',['nombre'])['nombre'] : false;
        $eF = $this->tableExists('embarque') ? $this->hasCols('embarque',['folio'])['folio'] : false;

        $select  = 'g.id';
        $select .= $hG['embarqueId']     ? ', g.embarqueId'     : ', NULL AS embarqueId';
        $select .= $hG['transportistaId']? ', g.transportistaId': ', NULL AS transportistaId';
        $select .= $hG['numeroGuia']     ? ', g.numeroGuia'     : ', NULL AS numeroGuia';
        $select .= $hG['urlSeguimiento'] ? ', g.urlSeguimiento' : ', NULL AS urlSeguimiento';
        $select .= $hG['fechaSalida']    ? ', g.fechaSalida'    : ', NULL AS fechaSalida';
        $select .= $hG['estado']         ? ', g.estado'         : ', NULL AS estado';
        $select .= $tN ? ', t.nombre AS transportista' : ', NULL AS transportista';
        $select .= $eF ? ', e.folio AS embarqueFolio'  : ', NULL AS embarqueFolio';

        $b = $db->table('guia_envio g')->select($select);
        if ($tN) $b->join('transportista t','t.id=g.transportistaId','left');
        if ($eF) $b->join('embarque e','e.id=g.embarqueId','left');
        $row = $b->where('g.id',(int)$id)->get()->getRowArray();

        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error'=>'No encontrado']);
        return $this->response->setJSON($row);
    }

    public function editarEnvio($id)
    {
        if (!$this->tableExists('guia_envio')) {
            return redirect()->back()->with('error','La tabla "guia_envio" no existe.');
        }

        $m  = new GuiaEnvioModel();
        if (!$m->find((int)$id)) {
            return redirect()->back()->with('error','Envío no encontrado.');
        }

        $input = [
            'embarqueId'      => $this->request->getPost('embarqueId'),
            'transportistaId' => $this->request->getPost('transportistaId'),
            'numeroGuia'      => $this->request->getPost('numeroGuia'),
            'urlSeguimiento'  => $this->request->getPost('urlSeguimiento'),
            'fechaSalida'     => $this->request->getPost('fechaSalida'),
            'estado'          => $this->request->getPost('estado'),
        ];
        $data = $this->filterToRealColumns($m->table ?? 'guia_envio', $input);
        if (empty($data)) return redirect()->back()->with('error','Nada que actualizar (columnas inexistentes).');

        try { $m->update((int)$id, $data); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo actualizar: '.$e->getMessage()); }

        return redirect()->back()->with('ok','Envío actualizado.');
    }

    public function eliminarEnvio($id)
    {
        if (!$this->tableExists('guia_envio')) {
            return redirect()->back()->with('error','La tabla "guia_envio" no existe.');
        }
        try { (new GuiaEnvioModel())->delete((int)$id); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo eliminar: '.$e->getMessage()); }
        return redirect()->back()->with('ok','Envío eliminado.');
    }

    /* =========================================================
     *  DOCUMENTOS DE EMBARQUE (usa doc_embarque)
     * =======================================================*/
    public function documentos()
    {
        $db = $this->db();
        $maquiladoraId = session()->get('maquiladora_id');

        // Combo de embarques
        $embarques = [];
        if ($this->tableExists('embarque')) {
            $eBuilder = $db->table('embarque')->select('id, folio');
            try {
                $colsE = $this->hasCols('embarque',['maquiladoraID']);
                if ($maquiladoraId && $colsE['maquiladoraID']) {
                    $eBuilder->where('maquiladoraID', (int)$maquiladoraId);
                }
            } catch (\Throwable $e) {}
            $embarques = $eBuilder->orderBy('id','DESC')->get()->getResultArray();
        }

        if (!$this->tableExists('doc_embarque')) {
            session()->setFlashdata('warn', 'La tabla "doc_embarque" no existe (vista vacía).');
            return view('modulos/logistica_documentos', [
                'docs'      => [],
                'embarques' => $embarques,
            ]);
        }

        // Descubrimos columnas reales en doc_embarque
        try { $cols = array_flip($db->getFieldNames('doc_embarque')); }
        catch (\Throwable $e) { $cols = []; }
        $has = fn($c) => isset($cols[$c]);

        // SELECT robusto
        $select  = 'd.id';
        $select .= $has('embarqueId') ? ', d.embarqueId'  : ', NULL AS embarqueId';
        $select .= $has('tipo')       ? ', d.tipo'        : ', NULL AS tipo';
        $select .= $has('numero')     ? ', d.numero'      : ', NULL AS numero';
        $select .= $has('fecha')      ? ', d.fecha'       : ', NULL AS fecha';
        $select .= $has('estado')     ? ', d.estado'      : ', NULL AS estado';
        $select .= $has('archivoRuta')? ', d.archivoRuta' : ', NULL AS archivoRuta';
        $select .= $has('urlPdf')     ? ', d.urlPdf'      : ', NULL AS urlPdf';
        $select .= $has('archivoPdf') ? ', d.archivoPdf'  : ', NULL AS archivoPdf';
        $select .= $this->tableExists('embarque') ? ', e.folio AS embarqueFolio' : ', NULL AS embarqueFolio';

        $builder = $db->table('doc_embarque d')->select($select);
        if ($this->tableExists('embarque')) $builder->join('embarque e','e.id=d.embarqueId','left');

        // Filtro por maquiladora: por doc_embarque.maquiladoraID y/o embarque.maquiladoraID
        if ($maquiladoraId) {
            try {
                $colsD = $this->hasCols('doc_embarque', ['maquiladoraID']);
                $colsE = $this->tableExists('embarque') ? $this->hasCols('embarque',['maquiladoraID']) : ['maquiladoraID'=>false];

                if ($colsD['maquiladoraID'] || $colsE['maquiladoraID']) {
                    $builder->groupStart();
                    if ($colsD['maquiladoraID']) {
                        $builder->where('d.maquiladoraID', (int)$maquiladoraId);
                    }
                    if ($colsE['maquiladoraID']) {
                        $builder->orWhere('e.maquiladoraID', (int)$maquiladoraId);
                    }
                    $builder->groupEnd();
                }
            } catch (\Throwable $e) {}
        }

        $docs = $builder->orderBy('d.id','DESC')->get()->getResultArray();

        return view('modulos/logistica_documentos', [
            'docs'      => $docs,
            'embarques' => $embarques,
        ]);
    }

    public function crearDocumento()
    {
        if (!$this->tableExists('doc_embarque')) {
            return redirect()->back()->with('error', 'La tabla "doc_embarque" no existe.');
        }

        $m  = new DocumentoEnvioModel(); // $table='doc_embarque' en el modelo
        $db = $this->db();

        try { $real = array_flip($db->getFieldNames($m->table ?? 'doc_embarque')); }
        catch (\Throwable $e) { $real = []; }

        $input = [
            'embarqueId'  => (int)$this->request->getPost('embarqueId'),
            'tipo'        => trim((string)$this->request->getPost('tipo')),
            'numero'      => trim((string)$this->request->getPost('numero')),
            'fecha'       => $this->request->getPost('fecha'),
            'estado'      => $this->request->getPost('estado'),
            'archivoRuta' => trim((string)$this->request->getPost('archivoRuta')),
            'urlPdf'      => trim((string)$this->request->getPost('urlPdf')),
            'archivoPdf'  => trim((string)$this->request->getPost('archivoPdf')),
        ];
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            try {
                $hasMaq = $this->hasCols('doc_embarque',['maquiladoraID']);
                if ($hasMaq['maquiladoraID']) {
                    $input['maquiladoraID'] = (int)$maquiladoraId;
                }
            } catch (\Throwable $e) {}
        }
        $data = array_intersect_key($input, $real);
        if (isset($data['tipo']) && $data['tipo'] === '') unset($data['tipo']);

        if (isset($real['numero']) && empty($data['numero'])) {
            $pref = isset($data['tipo']) ? strtoupper(substr($data['tipo'],0,2)) : 'DO';
            $data['numero'] = $pref.'-'.date('Y').'-'.str_pad((string)rand(1,9999),4,'0',STR_PAD_LEFT);
        }
        if (isset($real['fecha']) && empty($data['fecha'])) {
            $data['fecha'] = date('Y-m-d');
        }

        if (empty($data)) return redirect()->back()->with('error','No hay columnas válidas para guardar.');

        try { $m->insert($data); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo crear: '.$e->getMessage()); }

        return redirect()->back()->with('ok','Documento creado.');
    }

    public function docJson($id)
    {
        if (!$this->tableExists('doc_embarque')) {
            return $this->response->setStatusCode(404)->setJSON(['error'=>'Tabla doc_embarque no existe']);
        }

        $db = $this->db();

        try { $cols = array_flip($db->getFieldNames('doc_embarque')); }
        catch (\Throwable $e) { $cols = []; }
        $has = fn($c)=>isset($cols[$c]);

        $select  = 'd.id';
        $select .= $has('embarqueId')  ? ', d.embarqueId'  : ', NULL AS embarqueId';
        $select .= $has('tipo')        ? ', d.tipo'        : ', NULL AS tipo';
        $select .= $has('numero')      ? ', d.numero'      : ', NULL AS numero';
        $select .= $has('fecha')       ? ', d.fecha'       : ', NULL AS fecha';
        $select .= $has('estado')      ? ', d.estado'      : ', NULL AS estado';
        $select .= $has('archivoRuta') ? ', d.archivoRuta' : ', NULL AS archivoRuta';
        $select .= $has('urlPdf')      ? ', d.urlPdf'      : ', NULL AS urlPdf';
        $select .= $has('archivoPdf')  ? ', d.archivoPdf'  : ', NULL AS archivoPdf';
        $select .= $this->tableExists('embarque') ? ', e.folio AS embarqueFolio' : ', NULL AS embarqueFolio';

        $b = $db->table('doc_embarque d')->select($select);
        if ($this->tableExists('embarque')) $b->join('embarque e','e.id=d.embarqueId','left');
        $row = $b->where('d.id',(int)$id)->get()->getRowArray();

        if (!$row) return $this->response->setStatusCode(404)->setJSON(['error'=>'No encontrado']);
        return $this->response->setJSON($row);
    }

    public function editarDocumento($id)
    {
        if (!$this->tableExists('doc_embarque')) {
            return redirect()->back()->with('error', 'La tabla "doc_embarque" no existe.');
        }

        $m = new DocumentoEnvioModel(); // $table = 'doc_embarque'
        if (!$m->find((int)$id)) {
            return redirect()->back()->with('error','Documento no encontrado.');
        }

        $db = $this->db();
        try { $real = array_flip($db->getFieldNames($m->table ?? 'doc_embarque')); }
        catch (\Throwable $e) { $real = []; }

        $input = [
            'embarqueId'  => $this->request->getPost('embarqueId'),
            'tipo'        => $this->request->getPost('tipo'),
            'numero'      => $this->request->getPost('numero'),
            'fecha'       => $this->request->getPost('fecha'),
            'estado'      => $this->request->getPost('estado'),
            'archivoRuta' => $this->request->getPost('archivoRuta'),
            'urlPdf'      => $this->request->getPost('urlPdf'),
            'archivoPdf'  => $this->request->getPost('archivoPdf'),
        ];
        $data = array_filter(array_intersect_key($input, $real), fn($v)=>$v!==null);

        if (empty($data)) return redirect()->back()->with('error','Nada que actualizar.');

        try { $m->update((int)$id, $data); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo actualizar: '.$e->getMessage()); }

        return redirect()->back()->with('ok','Documento actualizado.');
    }

    public function eliminarDocumento($id)
    {
        if (!$this->tableExists('doc_embarque')) {
            return redirect()->back()->with('error', 'La tabla "doc_embarque" no existe.');
        }
        try { (new DocumentoEnvioModel())->delete((int)$id); }
        catch (\Throwable $e) { return redirect()->back()->with('error','No se pudo eliminar: '.$e->getMessage()); }
        return redirect()->back()->with('ok','Documento eliminado.');
    }

    public function descargarPdf($id)
    {
        $row = null;
        try { $row = (new DocumentoEnvioModel())->find((int)$id); } catch (\Throwable $e) {}
        if (!$row) return redirect()->back()->with('error','Documento no encontrado.');

        $ruta = $row['archivoRuta'] ?? null;
        $url  = $row['urlPdf']      ?? null;
        $loc  = $row['archivoPdf']  ?? null;

        if ($url) return redirect()->to($url);
        if ($ruta && preg_match('~^https?://~i', $ruta)) return redirect()->to($ruta);

        $candidatos = [];
        if ($ruta) $candidatos[] = $ruta;
        if ($loc)  $candidatos[] = $loc;

        foreach ($candidatos as $rel) {
            $paths = [
                WRITEPATH.'uploads/'.ltrim($rel,'/'),
                FCPATH.ltrim($rel,'/'),
            ];
            foreach ($paths as $p) {
                if (is_file($p)) {
                    return $this->response->download($p, null)->setFileName(basename($p));
                }
            }
        }

        return redirect()->back()->with('error','No hay PDF/archivo disponible para este documento.');
    }

    /* =========================================================
     *  DOCUMENTO MANUAL (sin BD)
     * =======================================================*/
    public function documentoManual()
    {
        // Plantilla por defecto
        $embarqueDefault = [
            'folio'                => 'EMB-2025-0012',
            'fecha'                => date('Y-m-d'),
            'origen'               => 'Planta Textiles XYZ, Blvd. Industrial 123, Puebla, PUE, MX',
            'destino'              => 'Comercializadora ABC, Av. Reforma 100, Cuauhtémoc, CDMX, MX',
            'remitente'            => 'Textiles XYZ S.A. de C.V.',
            'rfcRemitente'         => 'TXY123456789',
            'domicilioRemitente'   => 'Blvd. Industrial 123, Puebla, PUE, MX',
            'destinatario'         => 'Comercializadora ABC S.A. de C.V.',
            'rfcDestinatario'      => 'ABC987654321',
            'domicilioDestinatario'=> 'Av. Reforma 100, Cuauhtémoc, CDMX, MX',
            'tipoTransporte'       => 'Terrestre (Camión)',
            'transportista'        => 'Transportes Morales S.A. de C.V.',
            'operador'             => 'Juan Pérez',
            'placas'               => 'XYZ-123-4',
            'referencia'           => 'OC-9981 / Pedido #45021',
            'notas'                => 'Manipular con cuidado. No apilar más de 4 tarimas.',
        ];

        $itemsDefault = [
            ['sku'=>'P0001','descripcion'=>'Playera algodón (talla M) color blanco','cantidad'=>120,'um'=>'pz','peso'=>0.20,'valor'=>85.00],
            ['sku'=>'P0002','descripcion'=>'Playera algodón (talla L) color blanco','cantidad'=>80,'um'=>'pz','peso'=>0.21,'valor'=>88.50],
            ['sku'=>'P0010','descripcion'=>'Sudadera algodón (talla M) color negro','cantidad'=>60,'um'=>'pz','peso'=>0.65,'valor'=>265.00],
            ['sku'=>'A0100','descripcion'=>'Tarima estándar 1.2x1.0 m','cantidad'=>4,'um'=>'pza','peso'=>20.00,'valor'=>250.00],
        ];

        $embarque = $embarqueDefault;
        $items    = $itemsDefault;

        if ($this->request->getMethod() === 'post') {
            $get = fn($k, $def='') => trim((string)$this->request->getPost($k) ?? $def);

            $embarque = [
                'folio'                 => $get('folio', $embarqueDefault['folio']),
                'fecha'                 => $get('fecha', date('Y-m-d')),
                'origen'                => $get('origen', $embarqueDefault['origen']),
                'destino'               => $get('destino', $embarqueDefault['destino']),
                'remitente'             => $get('remitente', $embarqueDefault['remitente']),
                'rfcRemitente'          => $get('rfcRemitente', $embarqueDefault['rfcRemitente']),
                'domicilioRemitente'    => $get('domicilioRemitente', $embarqueDefault['domicilioRemitente']),
                'destinatario'          => $get('destinatario', $embarqueDefault['destinatario']),
                'rfcDestinatario'       => $get('rfcDestinatario', $embarqueDefault['rfcDestinatario']),
                'domicilioDestinatario' => $get('domicilioDestinatario', $embarqueDefault['domicilioDestinatario']),
                'tipoTransporte'        => $get('tipoTransporte', $embarqueDefault['tipoTransporte']),
                'transportista'         => $get('transportista', $embarqueDefault['transportista']),
                'operador'              => $get('operador', $embarqueDefault['operador']),
                'placas'                => $get('placas', $embarqueDefault['placas']),
                'referencia'            => $get('referencia', $embarqueDefault['referencia']),
                'notas'                 => $get('notas', $embarqueDefault['notas']),
            ];

            // Acepta ambos esquemas: items_*[] y (sku[], descripcion[], cantidad[], um[], pesoUnit[], valorUnit[])
            $sku   = $this->request->getPost('items_sku')   ?? $this->request->getPost('sku')         ?? [];
            $desc  = $this->request->getPost('items_desc')  ?? $this->request->getPost('descripcion') ?? [];
            $cant  = $this->request->getPost('items_cant')  ?? $this->request->getPost('cantidad')    ?? [];
            $um    = $this->request->getPost('items_um')    ?? $this->request->getPost('um')          ?? [];
            $peso  = $this->request->getPost('items_peso')  ?? $this->request->getPost('pesoUnit')    ?? [];
            $valor = $this->request->getPost('items_valor') ?? $this->request->getPost('valorUnit')   ?? [];

            $items = [];
            $n = max(count($sku), count($desc), count($cant), count($um), count($peso), count($valor));
            for ($i = 0; $i < $n; $i++) {
                $s = trim((string)($sku[$i]   ?? ''));
                $d = trim((string)($desc[$i]  ?? ''));
                if ($s === '' && $d === '') continue;

                $items[] = [
                    'sku'         => $s,
                    'descripcion' => $d,
                    'cantidad'    => (float)($cant[$i]  ?? 0),
                    'um'          => trim((string)($um[$i] ?? 'pz')),
                    'peso'        => (float)($peso[$i]  ?? 0),
                    'valor'       => (float)($valor[$i] ?? 0),
                ];
            }

            if (empty($items)) $items = $itemsDefault;
        }

        // Prefill rápido opcional vía ?folio=...
        $folioQS = $this->request->getGet('folio');
        if ($folioQS) $embarque['folio'] = $folioQS;

        return view('modulos/embarque_documento_manual', compact('embarque','items'));
    }

    /**
     * Vista SOLO para imprimir (GET/POST /modulo3/embarque/manual/print)
     */
    public function documentoManualPrint()
    {
        // Defaults
        $embarque = [
            'folio' => 'EMB-2025-0012',
            'fecha' => date('Y-m-d'),
        ];
        $items = [];

        $payload = $this->request->getPost('__payload');
        if ($payload) {
            try {
                $obj = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                $keys = ['folio','fecha','origen','destino','remitente','rfcRemitente','domicilioRemitente',
                    'destinatario','rfcDestinatario','domicilioDestinatario','tipoTransporte',
                    'transportista','operador','placas','referencia','notas'];
                foreach ($keys as $k) $embarque[$k] = trim((string)($obj[$k] ?? ($embarque[$k] ?? '')));

                $sku   = $obj['items_sku']   ?? $obj['sku']         ?? [];
                $desc  = $obj['items_desc']  ?? $obj['descripcion'] ?? [];
                $cant  = $obj['items_cant']  ?? $obj['cantidad']    ?? [];
                $um    = $obj['items_um']    ?? $obj['um']          ?? [];
                $peso  = $obj['items_peso']  ?? $obj['pesoUnit']    ?? [];
                $valor = $obj['items_valor'] ?? $obj['valorUnit']   ?? [];

                $n = max(count($sku), count($desc), count($cant), count($um), count($peso), count($valor));
                for ($i=0; $i<$n; $i++) {
                    $s = trim((string)($sku[$i]   ?? ''));
                    $d = trim((string)($desc[$i]  ?? ''));
                    if ($s === '' && $d === '') continue;
                    $items[] = [
                        'sku'         => $s,
                        'descripcion' => $d,
                        'cantidad'    => (float)($cant[$i]  ?? 0),
                        'um'          => trim((string)($um[$i] ?? 'pz')),
                        'peso'        => (float)($peso[$i]  ?? 0),
                        'valor'       => (float)($valor[$i] ?? 0),
                    ];
                }
            } catch (\Throwable $e) {}
        }

        if (empty($items)) {
            $items = [
                ['sku'=>'P0001','descripcion'=>'Playera algodón (talla M) color blanco','cantidad'=>120,'um'=>'pz','peso'=>0.20,'valor'=>85.00],
                ['sku'=>'P0002','descripcion'=>'Playera algodón (talla L) color blanco','cantidad'=>80,'um'=>'pz','peso'=>0.21,'valor'=>88.50],
            ];
        }

        return view('modulos/embarque_documento_print', compact('embarque','items'));
    }

    /* =========================================================
     *  FACTURACIÓN (UI + mock/real)
     * =======================================================*/

    /** Página para capturar/editar datos de facturación del embarque */
    public function facturarUI($embarqueId)
    {
        return view('modulos/facturar_envio', [
            'embarqueId'    => (int)$embarqueId,
            'embarqueFolio' => null,
        ]);
    }

    /** UUID v4 para demo */
    private function uuidv4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /** Convierte total a texto MUY simple (demo) */
    private function montoALetraMXN(float $m): string
    {
        $enteros = floor($m);
        $cent    = round(($m - $enteros) * 100);
        return number_format($enteros, 0, '.', ',') . ' PESOS ' . str_pad((string)$cent, 2, '0', STR_PAD_LEFT) . '/100 M.N.';
    }

    /** Redondeo corto */
    private function r2($n){ return round((float)$n, 2); }

    /** Endpoint que timbra: mock (gratis) o Facturapi si lo configuras */
    public function facturar($embarqueId)
    {
        try {
            $provider = strtolower((string)(getenv('facturacion.provider') ?: 'mock'));
            $data = $this->request->getJSON(true) ?? $this->request->getPost();

            $rfc   = trim($data['rfc'] ?? '');
            $name  = trim($data['nombre'] ?? '');
            if (!$rfc) {
                return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'msg'=>'RFC del receptor es requerido']);
            }

            $uso   = $data['usoCFDI']               ?? 'G03';
            $reg   = $data['regimenFiscalReceptor'] ?? '601';
            $zip   = $data['domicilioFiscalReceptor'] ?? '00000';
            $fp    = $data['formaPago']             ?? '03';
            $mp    = $data['metodoPago']            ?? 'PUE';
            $mon   = $data['moneda']                ?? 'MXN';
            $in    = $data['conceptos']             ?? [];

            // Normaliza conceptos (para el HTML factura_cfdi_demo)
            $conceptos = [];
            if ($in && is_array($in)) {
                foreach ($in as $c) {
                    $cantidad = (float)($c['cantidad'] ?? 1);
                    $vu       = (float)($c['precioUnitario'] ?? 0);
                    $importe  = $cantidad * $vu;
                    $iva      = $this->r2($importe * 0.16);
                    $conceptos[] = [
                        'prodserv'     => $c['claveProdServ'] ?? '01010101',
                        'claveUnidad'  => $c['claveUnidad']   ?? 'E48',
                        'cantidad'     => $cantidad,
                        'unidad'       => '', // opcional
                        'descripcion'  => $c['descripcion']    ?? 'Concepto',
                        'valorUnitario'=> $vu,
                        'descuento'    => 0,
                        'importe'      => $importe,
                        'iva'          => $iva,
                        'ieps'         => 0,
                    ];
                }
            } else {
                $cantidad = 1;
                $vu       = 100.00;
                $importe  = $cantidad * $vu;
                $iva      = $this->r2($importe * 0.16);
                $conceptos[] = [
                    'prodserv'     => '01010101',
                    'claveUnidad'  => 'E48',
                    'cantidad'     => $cantidad,
                    'unidad'       => '',
                    'descripcion'  => 'Servicio de envío - Embarque #'.$embarqueId,
                    'valorUnitario'=> $vu,
                    'descuento'    => 0,
                    'importe'      => $importe,
                    'iva'          => $iva,
                    'ieps'         => 0,
                ];
            }

            // Totales
            $subtotal   = $this->r2(array_reduce($conceptos, fn($a,$c)=>$a + (float)$c['importe'], 0));
            $trasladado = $this->r2(array_reduce($conceptos, fn($a,$c)=>$a + (float)$c['iva'], 0));
            $retenidos  = 0.0;
            $descuento  = 0.0;
            $total      = $this->r2($subtotal - $descuento + $trasladado - $retenidos);

            $serie = 'DEMO';
            $folio = rand(1000, 9999);
            $uuid  = $this->uuidv4();
            $fecha = date('Y-m-d H:i:s');

            /* ---------- MOCK (gratis) ---------- */
            if ($provider === 'mock') {
                // Arma payload para la vista factura_cfdi_demo
                $emisor = [
                    'logo'          => '', // si tienes logo en /public, pon la URL
                    'nombre'        => 'Textiles XYZ S.A. de C.V.',
                    'rfc'           => 'TXY123456789',
                    'regimen'       => '601',
                    'lugarExpedicion'=> $zip,
                    'noCertCSD'     => '00001000000403258748',
                ];
                $receptor = [
                    'nombre'   => $name ?: $rfc,
                    'rfc'      => $rfc,
                    'usoCfdi'  => $uso,
                    'domicilio'=> 'CP '.$zip,
                ];
                $factura = [
                    'tipo'        => 'I',
                    'serie'       => $serie,
                    'folio'       => $folio,
                    'fecha'       => $fecha,
                    'moneda'      => $mon,
                    'tipoCambio'  => '1.0000',
                    'formaPago'   => $fp,
                    'metodoPago'  => $mp,
                    'condiciones' => 'Contado',
                ];
                $totales = [
                    'subtotal'  => $subtotal,
                    'descuento' => $descuento,
                    'trasladados'=> $trasladado,
                    'retenidos' => $retenidos,
                    'total'     => $total,
                    'letra'     => $this->montoALetraMXN($total),
                ];
                $timbre = [
                    'uuid'          => $uuid,
                    'fechaTimbrado' => $fecha,
                    'noCertCSD'     => $emisor['noCertCSD'],
                    'selloCfdi'     => substr(hash('sha256', 'cfdi-'.$uuid), 0, 80).'…',
                    'selloSat'      => substr(hash('sha256', 'sat-'.$uuid), 0, 80).'…',
                    'qr'            => null,
                ];

                // Guarda en sesión (dos llaves por comodidad)
                $key1 = 'factura_demo';
                $key2 = 'factura_demo_'.$embarqueId;
                $pack = compact('emisor','receptor','factura','conceptos','totales','timbre');
                session()->set($key1, $pack);
                session()->set($key2, $pack);

                // URLs internas para preview/PDF
                $previewUrl = site_url('logistica/factura/'.$embarqueId);
                $pdfUrl     = site_url('logistica/factura/'.$embarqueId.'/pdf');

                return $this->response->setJSON([
                    'ok'          => true,
                    'uuid'        => $uuid,
                    'serie'       => $serie,
                    'folio'       => $folio,
                    'total'       => $total,
                    'fecha'       => date('c'),
                    'provider'    => 'mock',
                    'preview_url' => $previewUrl,
                    'pdf_url'     => $pdfUrl,
                    // Si quieres seguir mostrando un XML de demo, puedes poner un enlace:
                    'xml_url'     => 'https://www.w3schools.com/xml/note.xml',
                ]);
            }

            /* ---------- FACTURAPI (real) ---------- */
            $apiKey = getenv('facturacion.facturapi_key') ?: '';
            if (!$apiKey) {
                return $this->response->setStatusCode(500)
                    ->setJSON(['ok'=>false,'msg'=>'Falta facturacion.facturapi_key en .env (o usa facturacion.provider=mock)']);
            }

            $items = [];
            foreach ($conceptos as $c) {
                $items[] = [
                    'product' => [
                        'description' => $c['descripcion'],
                        'product_key' => $c['prodserv'],
                        'price'       => (float)$c['valorUnitario'],
                        'unit_key'    => $c['claveUnidad'],
                        'name'        => $c['descripcion'],
                    ],
                    'quantity' => (float)$c['cantidad'],
                ];
            }

            $payload = [
                'type'           => 'I',
                'customer'       => [
                    'legal_name' => $name ?: $rfc,
                    'tax_id'     => $rfc,
                    'tax_system' => $reg,
                    'address'    => ['zip' => $zip],
                ],
                'use'            => $uso,
                'payment_form'   => $fp,
                'payment_method' => $mp,
                'currency'       => $mon,
                'items'          => $items,
                'external_id'    => 'embarque-'.$embarqueId,
            ];

            $ch = curl_init('https://www.facturapi.io/v2/invoices');
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$apiKey,
                ],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 45,
            ]);
            $out  = curl_exec($ch);
            $err  = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                return $this->response->setStatusCode(502)->setJSON(['ok'=>false,'msg'=>$err]);
            }

            $d = json_decode($out, true) ?: [];
            if (!in_array($code, [200,201])) {
                $msg = $d['message'] ?? 'No se pudo timbrar';
                return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'msg'=>$msg,'raw'=>$d]);
            }

            return $this->response->setJSON([
                'ok'      => true,
                'uuid'    => $d['uuid'] ?? null,
                'serie'   => $d['series'] ?? ($d['series_name'] ?? null),
                'folio'   => $d['number'] ?? null,
                'total'   => $d['total'] ?? null,
                'pdf_url' => $d['files']['pdf'] ?? null,
                'xml_url' => $d['files']['xml'] ?? null,
                'fecha'   => $d['date'] ?? null,
                'provider'=> 'facturapi',
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'msg'=>$e->getMessage()]);
        }
    }
}
