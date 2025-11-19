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
    public function editar($id)
    {
        $ordenModel = new OrdenCompraModel();

        $data = [
            'folio'     => $this->request->getPost('folio'),
            'fecha'     => $this->request->getPost('fecha'),
            'clienteId' => $this->request->getPost('clienteId') ?: null,
            'estatus'   => $this->request->getPost('estatus'),
            'moneda'    => $this->request->getPost('moneda'),
            'total'     => $this->request->getPost('total'),
            'op'        => $this->request->getPost('op'),
            'cajas'     => $this->request->getPost('cajas'),
            'peso'      => $this->request->getPost('peso'),
        ];

        if ($ordenModel->update((int) $id, $data) === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la orden');
        }

        return redirect()
            ->to(site_url('modulo3/logistica_preparacion'))
            ->with('ok', 'Orden actualizada correctamente');
    }
}
