<?php

namespace App\Controllers;

use App\Models\PedidosClientesModel;

class PedidosClientesController extends BaseController
{
    public function __construct()
    {
        $this->pedidosModel = new PedidosClientesModel();
    }

    /**
     * Vista principal de pedidos de clientes
     */
    public function index()
    {
        // Verificar permisos
        if (!can('menu.pedidos_clientes')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        // Obtener el ID del usuario actual
        $userId = session()->get('user_id');
        
        // Obtener las órdenes de producción del cliente actual
        $ordenes = $this->pedidosModel->getOrdenesPorCliente($userId);

        return view('modulos/pedidos_clientes', [
            'title' => 'Pedidos de Clientes',
            'ordenes' => $ordenes,
            'notifCount' => 0
        ]);
    }

    /**
     * Obtener detalles de una orden de producción (JSON)
     */
    public function getOrdenDetalles($id = null)
    {
        if (!can('menu.pedidos_clientes')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }

        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        $userId = session()->get('user_id');
        $orden = $this->pedidosModel->getOrdenPorCliente($id, $userId);

        if (!$orden) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Orden no encontrada']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $orden
        ]);
    }

    /**
     * Descargar PDF de una orden de producción
     */
    public function descargarPDF($id = null)
    {
        if (!can('menu.pedidos_clientes')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return redirect()->to('/modulo1/pedidos_clientes')->with('error', 'ID inválido');
        }

        $userId = session()->get('user_id');
        $orden = $this->pedidosModel->getOrdenPorCliente($id, $userId);

        if (!$orden) {
            return redirect()->to('/modulo1/pedidos_clientes')->with('error', 'Orden no encontrada');
        }

        try {
            // Generar PDF usando la misma lógica que en pedidos
            $pdfGenerator = new \App\Libraries\PdfGenerator();
            $pdfPath = $pdfGenerator->generateOrdenPdf($orden);

            if ($pdfPath && file_exists(WRITEPATH . $pdfPath)) {
                return $this->response->download(WRITEPATH . $pdfPath, null)
                    ->setFileName('Orden_' . ($orden['folio'] ?: $id) . '.pdf');
            } else {
                return redirect()->to('/modulo1/pedidos_clientes')->with('error', 'PDF no disponible');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error al descargar PDF: ' . $e->getMessage());
            return redirect()->to('/modulo1/pedidos_clientes')->with('error', 'Error al generar PDF');
        }
    }
}
