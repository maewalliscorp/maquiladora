<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    // Tabla real según tu BD
    protected $table            = 'orden_compra';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    /**
     * Devuelve el listado de pedidos con datos de la empresa (cliente).
     * Columnas: id, empresa, folio, fecha, estatus, moneda, total
     */
    public function getListadoPedidos(): array
    {
        $db = $this->db;
        $sql = "SELECT oc.id,
                       c.nombre AS empresa,
                       oc.folio,
                       oc.fecha,
                       oc.estatus,
                       oc.moneda,
                       oc.total,
                       NULL as documento_url
                FROM orden_compra oc
                LEFT JOIN cliente c ON c.id = oc.clienteId
                ORDER BY oc.fecha DESC, oc.id DESC";
        try {
            return $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            // Variantes posibles por mayúsculas/minúsculas
            $sql2 = "SELECT oc.id, c.nombre AS empresa, oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total
                     FROM OrdenCompra oc
                     LEFT JOIN Cliente c ON c.id = oc.clienteId
                     ORDER BY oc.fecha DESC, oc.id DESC";
            try {
                return $db->query($sql2)->getResultArray();
            } catch (\Throwable $e2) {
                return [];
            }
        }
    }

    /**
     * Trae un pedido por ID con datos de cliente
     */
    public function getPedidoPorId(int $id): ?array
    {
        $db = $this->db;
        // 1) Traer base mínima de orden_compra (sin joins), para asegurar que algo se muestre
        $base = null;
        try {
            $base = $db->query(
                "SELECT id, clienteId, folio, fecha, estatus, moneda, total
                 FROM orden_compra WHERE id = ?",
                [$id]
            )->getRowArray();
        } catch (\Throwable $e) {
            try {
                $base = $db->query(
                    "SELECT id, clienteId, folio, fecha, estatus, moneda, total
                     FROM OrdenCompra WHERE id = ?",
                    [$id]
                )->getRowArray();
            } catch (\Throwable $e2) {
                $base = null;
            }
        }

        if (!$base) {
            return null;
        }

        // 2) Intentar enriquecer de forma ligera: nombre de cliente (empresa)
        try {
            if (!empty($base['clienteId'])) {
                foreach (['cliente','Cliente'] as $t) {
                    try {
                        $cli = $db->query('SELECT nombre FROM ' . $t . ' WHERE id = ?', [$base['clienteId']])->getRowArray();
                        if ($cli && isset($cli['nombre'])) {
                            $base['empresa'] = $cli['nombre'];
                            break;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        } catch (\Throwable $e) {}

        // 3) Adjuntar datos de la última orden de producción ligada (si existe)
        try {
            if (!empty($base['id'])) {
                // Variante 1: tablas en minúscula
                try {
                    $op = $db->query(
                        "SELECT op.* FROM orden_produccion op
                         INNER JOIN (
                           SELECT MAX(id) AS id, ordenCompraId
                           FROM orden_produccion
                           GROUP BY ordenCompraId
                         ) t ON t.id = op.id
                         WHERE op.ordenCompraId = ?
                         LIMIT 1",
                        [$base['id']]
                    )->getRowArray();
                    if ($op) {
                        $base['op_id'] = $op['id'] ?? null;
                        $base['op_folio'] = $op['folio'] ?? null;
                        $base['op_disenoVersionId'] = $op['disenoVersionId'] ?? null;
                        $base['op_cantidadPlan'] = $op['cantidadPlan'] ?? null;
                        $base['op_fechaInicioPlan'] = $op['fechaInicioPlan'] ?? null;
                        $base['op_fechaFinPlan'] = $op['fechaFinPlan'] ?? null;
                        $base['op_status'] = $op['status'] ?? null;
                    }
                } catch (\Throwable $e) {
                    // Variante 2: tablas con mayúsculas
                    try {
                        $op = $db->query(
                            "SELECT op.* FROM OrdenProduccion op
                             INNER JOIN (
                               SELECT MAX(id) AS id, ordenCompraId
                               FROM OrdenProduccion
                               GROUP BY ordenCompraId
                             ) t ON t.id = op.id
                             WHERE op.ordenCompraId = ?
                             LIMIT 1",
                            [$base['id']]
                        )->getRowArray();
                        if ($op) {
                            $base['op_id'] = $op['id'] ?? null;
                            $base['op_folio'] = $op['folio'] ?? null;
                            $base['op_disenoVersionId'] = $op['disenoVersionId'] ?? null;
                            $base['op_cantidadPlan'] = $op['cantidadPlan'] ?? null;
                            $base['op_fechaInicioPlan'] = $op['fechaInicioPlan'] ?? null;
                            $base['op_fechaFinPlan'] = $op['fechaFinPlan'] ?? null;
                            $base['op_status'] = $op['status'] ?? null;
                        }
                    } catch (\Throwable $e2) {}
                }
            }
        } catch (\Throwable $e) {}

        // 4) Consulta robusta y simple (join solo con cliente.nombre)
        try {
            $row = $db->query(
                "SELECT oc.id, oc.clienteId, oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                        c.nombre AS empresa
                 FROM orden_compra oc
                 LEFT JOIN cliente c ON c.id = oc.clienteId
                 WHERE oc.id = ?",
                [$id]
            )->getRowArray();
            if ($row) {
                // Si ya trajimos op_*, preservarlos
                return array_merge($row, array_intersect_key($base, array_flip([
                    'op_id','op_folio','op_disenoVersionId','op_cantidadPlan','op_fechaInicioPlan','op_fechaFinPlan','op_status'
                ])));
            }
        } catch (\Throwable $e) {
            try {
                $row2 = $db->query(
                    "SELECT oc.id, oc.clienteId, oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                            c.nombre AS empresa
                     FROM OrdenCompra oc
                     LEFT JOIN Cliente c ON c.id = oc.clienteId
                     WHERE oc.id = ?",
                    [$id]
                )->getRowArray();
                if ($row2) {
                    return array_merge($row2, array_intersect_key($base, array_flip([
                        'op_id','op_folio','op_disenoVersionId','op_cantidadPlan','op_fechaInicioPlan','op_fechaFinPlan','op_status'
                    ])));
                }
            } catch (\Throwable $e2) {}
        }

        return $base;
    }
}
