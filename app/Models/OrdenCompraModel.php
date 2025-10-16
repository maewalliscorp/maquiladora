<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdenCompraModel extends Model
{
    protected $table         = 'orden_compra';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['clienteId','folio','fecha','estatus','moneda','total'];

    /**
     * Órdenes "pendientes de embarque": no asociadas a ningún embarque_item.
     * Ajusta el criterio de estatus si lo manejas distinto (ej. 'aprobada', 'cerrada', etc.)
     */
    public function listarPendientes()
    {
        return $this->select("
                    oc.id,
                    oc.folio           AS pedido,
                    oc.fecha,
                    oc.total,
                    oc.clienteId,
                    c.nombre           AS clienteNombre
                ")
            ->from('orden_compra AS oc')
            ->join('cliente AS c', 'c.id = oc.clienteId', 'left')
            ->where('oc.estatus <>', 'cancelada')
            // Excluir las que ya estén en algún embarque_item
            ->where("NOT EXISTS (
                    SELECT 1 FROM embarque_item ei WHERE ei.ordenCompraId = oc.id
                )", null, false)
            ->orderBy('oc.fecha','DESC')
            ->findAll();
    }
}
