<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmbarqueModel;
use App\Models\EmbarqueItemModel;
use App\Models\OrdenCompraModel;
use App\Models\ClienteModel;

class LogisticaController extends BaseController
{
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

    /* ====== Botones Ver / Editar ====== */

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

        // Incluimos posibles campos de la tabla si existen (op, cajas, peso)
        return $this->response->setJSON([
            'id'        => $row['id'],
            'folio'     => $row['folio']     ?? null,
            'fecha'     => $row['fecha']     ?? null,
            'estatus'   => $row['estatus']   ?? null,
            'moneda'    => $row['moneda']    ?? null,
            'total'     => $row['total']     ?? null,
            'clienteId' => $row['clienteId'] ?? null,
            'cliente'   => $cli['nombre']    ?? null,

            // opcionales: solo llegarán si existen en tu tabla
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

        // Recogemos todos los campos que queremos permitir
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
        ], fn($v) => $v !== null && $v !== '');

        if (empty($data)) {
            return redirect()->back()->with('error', 'Sin cambios para guardar');
        }

        // Filtramos a solo columnas existentes en la tabla para evitar errores
        $db    = \Config\Database::connect();
        $table = property_exists($mOc, 'table') && !empty($mOc->table) ? $mOc->table : 'orden_compra';
        try {
            $fields = array_flip($db->getFieldNames($table)); // ['id'=>0, 'folio'=>1, ...]
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

    public function gestion()    { return view('modulos/logistica_gestion'); }
    public function documentos() { return view('modulos/logistica_documentos'); }
}
