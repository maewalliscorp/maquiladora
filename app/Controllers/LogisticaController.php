<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmbarqueModel;
use App\Models\EmbarqueItemModel;
use App\Models\OrdenCompraModel;
use App\Models\ClienteModel;
// Gestión (tracking)
use App\Models\GuiaEnvioModel;
use App\Models\TransportistaModel;

class LogisticaController extends BaseController
{
    /* =========================================================
     *  PACKING · PREPARACIÓN
     * =======================================================*/
    public function preparacion()
    {
        $mEmbarque = new EmbarqueModel();
        $mOrden    = new OrdenCompraModel();
        $mCliente  = new ClienteModel();

        $data = [
            'embarque' => $mEmbarque->getAbiertoActual() ?? [],
            'clientes' => $mCliente->listado() ?? [],
            'ordenes'  => $mOrden->listarPendientes() ?? [],
        ];

        return view('modulos/logistica_preparacion', $data);
    }

    public function crearEmbarque()
    {
        $folio     = trim((string) $this->request->getPost('folio'));
        $clienteId = (int) $this->request->getPost('clienteId');

        if ($folio === '' || $clienteId <= 0) {
            return redirect()->back()->with('error', 'Folio y Cliente son obligatorios');
        }

        $m = new EmbarqueModel();

        $exist = $m->where('folio', $folio)->where('estatus', 'abierto')->first();
        if ($exist) {
            return redirect()->back()->with('ok', 'Usando embarque abierto #' . $exist['id']);
        }

        $id = $m->insert([
            'folio'     => $folio,
            'clienteId' => $clienteId,
            'fecha'     => date('Y-m-d'),
            'estatus'   => 'abierto',
        ], true);

        return redirect()->back()->with('ok', 'Embarque creado #' . $id);
    }

    public function agregarOrden($embarqueId)
    {
        $embarqueId = (int) $embarqueId;
        $ordenId    = (int) $this->request->getPost('ordenId');

        if ($embarqueId <= 0 || $ordenId <= 0) {
            return redirect()->back()->with('error', 'Faltan datos para agregar al envío');
        }

        $mEmb  = new EmbarqueModel();
        $mItem = new EmbarqueItemModel();

        $emb = $mEmb->find($embarqueId);
        if (!$emb || ($emb['estatus'] ?? '') !== 'abierto') {
            return redirect()->back()->with('error', 'El embarque no existe o no está abierto');
        }

        if ($mItem->where('embarqueId', $embarqueId)->where('ordenCompraId', $ordenId)->first()) {
            return redirect()->back()->with('ok', 'El pedido ya estaba agregado a este envío');
        }

        $mItem->insert([
            'embarqueId'    => $embarqueId,
            'ordenCompraId' => $ordenId,
            'cantidad'      => 1,
            'unidadMedida'  => 'CJ',
        ]);

        return redirect()->back()->with('ok', 'Pedido agregado al envío');
    }

    /* ====== Botones Ver / Editar de pedidos ====== */

    public function ordenJson($id)
    {
        $id   = (int) $id;
        $mOc  = new OrdenCompraModel();
        $mCli = new ClienteModel();

        $row = $mOc->find($id);
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado']);
        }

        $cli = $row['clienteId'] ? $mCli->find((int) $row['clienteId']) : null;

        return $this->response->setJSON([
            'id'        => $row['id'],
            'folio'     => $row['folio']     ?? null,
            'fecha'     => $row['fecha']     ?? null,
            'estatus'   => $row['estatus']   ?? null,
            'moneda'    => $row['moneda']    ?? null,
            'total'     => $row['total']     ?? null,
            'clienteId' => $row['clienteId'] ?? null,
            'cliente'   => $cli['nombre']    ?? null,
            // opcionales si existen en la tabla
            'op'        => $row['op']        ?? null,
            'cajas'     => $row['cajas']     ?? null,
            'peso'      => $row['peso']      ?? null,
        ]);
    }

    public function ordenEditar($id)
    {
        $id  = (int) $id;
        $mOc = new OrdenCompraModel();

        if (!$mOc->find($id)) {
            return redirect()->back()->with('error', 'Orden no encontrada');
        }

        // Campos permitidos (se filtran contra columnas reales)
        $data = array_filter([
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

        if (empty($data)) {
            return redirect()->back()->with('error', 'Sin cambios para guardar');
        }

        $db    = \Config\Database::connect();
        $table = property_exists($mOc, 'table') && !empty($mOc->table) ? $mOc->table : 'orden_compra';
        try {
            $fields = array_flip($db->getFieldNames($table));
        } catch (\Throwable $e) {
            $fields = array_flip(['folio','fecha','estatus','moneda','total','clienteId','op','cajas','peso']);
        }

        $allowed = array_intersect_key($data, $fields);
        $ignored = array_diff(array_keys($data), array_keys($allowed));

        if (empty($allowed)) {
            return redirect()->back()->with('error', 'Ningún campo editable coincide con la tabla');
        }

        $mOc->update($id, $allowed);
        $msg = 'Pedido actualizado';
        if (!empty($ignored)) {
            $msg .= ' (se ignoraron: ' . implode(', ', $ignored) . ')';
        }
        return redirect()->back()->with('ok', $msg);
    }

    /* ====== Placeholders documentos ====== */
    public function packingList($id) { return redirect()->back()->with('ok', 'Packing List generado (demo)'); }
    public function etiquetas($id)   { return redirect()->back()->with('ok', 'Etiquetas generadas (demo)'); }

    /* =========================================================
     *  TRACKING · GESTIÓN DE ENVÍOS (tolerante a columnas faltantes)
     * =======================================================*/

    // Vista + datos (lista de envíos, transportistas, embarques)
    public function gestion()
    {
        $db = \Config\Database::connect();

        // Verifica columnas existentes en guia_envio
        try {
            $geFields = array_flip($db->getFieldNames('guia_envio'));
        } catch (\Throwable $e) {
            $geFields = [];
        }
        $hasFecha  = isset($geFields['fechaSalida']);
        $hasEstado = isset($geFields['estado']);

        // SELECT robusto (si no existen, manda NULL AS ...)
        $select  = 'g.id, g.numeroGuia, g.urlSeguimiento, g.embarqueId, ';
        $select .= ($hasFecha  ? 'g.fechaSalida' : 'NULL') . ' AS fechaSalida, ';
        $select .= ($hasEstado ? 'g.estado'      : 'NULL') . ' AS estado, ';
        $select .= 't.nombre AS transportista, ';
        $select .= 'e.folio AS embarque, e.fecha AS fechaEmbarque, e.estatus AS estatusEmbarque';

        $envios = $db->table('guia_envio g')
            ->select($select)
            ->join('transportista t', 't.id = g.transportistaId', 'left')
            ->join('embarque e',      'e.id = g.embarqueId',      'left')
            ->orderBy('g.id', 'DESC')
            ->get()->getResultArray();

        // Combos
        $transportistas = (new TransportistaModel())
            ->orderBy('nombre','ASC')->findAll();

        $embarques = $db->table('embarque')
            ->select('id, folio')
            ->orderBy('id','DESC')
            ->get()->getResultArray();

        return view('modulos/logistica_gestion', [
            'envios'         => $envios,
            'transportistas' => $transportistas,
            'embarques'      => $embarques,
        ]);
    }

    // Crear envío (registro en guia_envio) — filtra por columnas reales
    public function crearEnvio()
    {
        $m = new GuiaEnvioModel();

        $input = [
            'embarqueId'      => (int) $this->request->getPost('embarqueId'),
            'transportistaId' => (int) $this->request->getPost('transportistaId'),
            'numeroGuia'      => trim((string)$this->request->getPost('numeroGuia')),
            'urlSeguimiento'  => trim((string)$this->request->getPost('urlSeguimiento')),
            'fechaSalida'     => $this->request->getPost('fechaSalida'),
            'estado'          => $this->request->getPost('estado'),
        ];

        // Filtra solo columnas existentes
        $db     = \Config\Database::connect();
        $fields = array_flip($db->getFieldNames($m->table));
        $data   = array_intersect_key($input, $fields);

        if (empty($data['numeroGuia']) || empty($data['transportistaId'])) {
            return redirect()->back()->with('error','Transportista y número de guía son obligatorios.');
        }

        $m->insert($data);
        return redirect()->back()->with('ok','Envío registrado.');
    }

    // JSON de un envío (para modal Ver/Editar) — robusto a columnas faltantes
    public function envioJson($id)
    {
        $id = (int) $id;
        $db = \Config\Database::connect();

        // Checa columnas existentes
        try {
            $geFields = array_flip($db->getFieldNames('guia_envio'));
        } catch (\Throwable $e) {
            $geFields = [];
        }
        $hasFecha  = isset($geFields['fechaSalida']);
        $hasEstado = isset($geFields['estado']);

        $select  = 'g.id, g.embarqueId, g.transportistaId, g.numeroGuia, g.urlSeguimiento, ';
        $select .= ($hasFecha  ? 'g.fechaSalida' : 'NULL') . ' AS fechaSalida, ';
        $select .= ($hasEstado ? 'g.estado'      : 'NULL') . ' AS estado, ';
        $select .= 't.nombre AS transportista, e.folio AS embarqueFolio';

        $row = $db->table('guia_envio g')
            ->select($select)
            ->join('transportista t','t.id=g.transportistaId','left')
            ->join('embarque e','e.id=g.embarqueId','left')
            ->where('g.id', $id)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error'=>'No encontrado']);
        }
        return $this->response->setJSON($row);
    }

    // Editar envío — ya filtra por columnas reales
    public function editarEnvio($id)
    {
        $id = (int) $id;
        $m  = new GuiaEnvioModel();

        if (!$m->find($id)) {
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

        $db     = \Config\Database::connect();
        $fields = array_flip($db->getFieldNames($m->table));
        $data   = array_filter(array_intersect_key($input, $fields), fn($v) => $v !== null);

        $m->update($id, $data);
        return redirect()->back()->with('ok','Envío actualizado.');
    }

    // Eliminar envío
    public function eliminarEnvio($id)
    {
        $id = (int) $id;
        $m  = new GuiaEnvioModel();
        $m->delete($id);
        return redirect()->back()->with('ok','Envío eliminado.');
    }

    /* --------------------------------------------------------- */
    public function documentos()
    {
        return view('modulos/logistica_documentos');
    }
}
