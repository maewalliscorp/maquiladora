<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrdenCompraModel;

class OrdenesController extends BaseController
{
    /**
     * GET /modulo3/ordenes/{id}/json
     */
    public function json($id)
    {
        $ordenModel = new OrdenCompraModel();

        if (method_exists($ordenModel, 'getDetalle')) {
            $data = $ordenModel->getDetalle((int) $id);
        } else {
            $data = $ordenModel->find((int) $id);
        }

        if (!$data) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'No encontrado']);
        }

        return $this->response->setJSON($data);
    }

    /**
     * POST /modulo3/ordenes/{id}/editar
     */
    /**
     * POST /modulo3/ordenes/crear
     */
    public function crear()
    {
        $ordenModel = new OrdenCompraModel();
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID') ?? null;

        $data = [
            'folio' => $this->request->getPost('folio'),
            'fecha' => $this->request->getPost('fecha') ?: date('Y-m-d'),
            'clienteId' => $this->request->getPost('clienteId') ?: null,
            'estatus' => $this->request->getPost('estatus') ?: 'abierto',
            'moneda' => $this->request->getPost('moneda') ?: 'MXN',
            'total' => $this->request->getPost('total') ?: 0,
            'op' => $this->request->getPost('op'),
            'cajas' => $this->request->getPost('cajas') ?: 0,
            'peso' => $this->request->getPost('peso') ?: 0,
        ];

        // Add maquiladoraId if exists
        if ($maquiladoraId) {
            $data['maquiladoraID'] = (int) $maquiladoraId;
        }

        // Validaciones básicas
        if (empty($data['folio'])) {
            return $this->response->setJSON(['ok' => false, 'message' => 'El folio es obligatorio']);
        }

        try {
            $id = $ordenModel->insert($data);
            if ($id) {
                return $this->response->setJSON(['ok' => true, 'message' => 'Orden creada correctamente', 'id' => $id]);
            } else {
                return $this->response->setJSON(['ok' => false, 'message' => 'No se pudo crear la orden']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * POST /modulo3/ordenes/{id}/editar
     */
    public function editar($id)
    {
        $ordenModel = new OrdenCompraModel();

        $data = [
            'folio' => $this->request->getPost('folio'),
            'fecha' => $this->request->getPost('fecha'),
            'clienteId' => $this->request->getPost('clienteId') ?: null,
            'estatus' => $this->request->getPost('estatus'),
            'moneda' => $this->request->getPost('moneda'),
            'total' => $this->request->getPost('total'),
            'op' => $this->request->getPost('op'),
            'cajas' => $this->request->getPost('cajas'),
            'peso' => $this->request->getPost('peso'),
        ];

        if ($ordenModel->update((int) $id, $data) === false) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'message' => 'No se pudo actualizar la orden']);
            }
            return redirect()->back()->withInput()->with('error', 'No se pudo actualizar la orden');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'message' => 'Orden actualizada correctamente']);
        }

        return redirect()
            ->to(site_url('modulo3/logistica_preparacion'))
            ->with('ok', 'Orden actualizada correctamente');
    }

    /**
     * DELETE /modulo3/ordenes/{id}/eliminar
     */
    public function eliminar($id)
    {
        $ordenModel = new OrdenCompraModel();

        try {
            if ($ordenModel->delete((int) $id)) {
                return $this->response->setJSON(['ok' => true, 'message' => 'Orden eliminada correctamente']);
            } else {
                return $this->response->setJSON(['ok' => false, 'message' => 'No se pudo eliminar la orden']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
