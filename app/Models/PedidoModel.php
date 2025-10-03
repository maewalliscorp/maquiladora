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
        $sql = "SELECT oc.id,
                       oc.clienteId,
                       c.nombre   AS empresa,
                       c.contacto AS contacto,
                       c.telefono AS telefono,
                       c.email    AS email,
                       c.rfc      AS rfc,
                       c.direccion AS direccion,
                       oc.folio,
                       oc.fecha,
                       oc.estatus,
                       oc.moneda,
                       oc.total,
                       d.nombre   AS disenoNombre,
                       d.descripcion AS disenoDescripcion,
                       dv.version AS disenoVersion,
                       dv.notas   AS disenoNotas,
                       dv.archivoCadUrl,
                       dv.archivoPatronUrl,
                       GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '\n') AS materiales
                FROM orden_compra oc
                LEFT JOIN cliente c ON c.id = oc.clienteId
                LEFT JOIN orden_produccion op ON op.ordenCompraId = oc.id
                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                LEFT JOIN diseno d ON d.id = dv.disenoId
                LEFT JOIN lista_materiales lm ON lm.disenoVersionId = dv.id
                WHERE oc.id = ?
                GROUP BY oc.id, oc.clienteId, c.nombre, c.contacto, c.telefono, c.email, c.rfc, c.direccion,
                         oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                         d.nombre, d.descripcion, dv.version, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl";
        try {
            return $db->query($sql, [$id])->getRowArray() ?: null;
        } catch (\Throwable $e) {
            $sql2 = "SELECT oc.id, oc.clienteId,
                             c.nombre AS empresa,
                             NULL AS contacto, NULL AS telefono, NULL AS email, NULL AS rfc, NULL AS direccion,
                             oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                             d.nombre AS disenoNombre,
                             d.descripcion AS disenoDescripcion,
                             dv.version AS disenoVersion,
                             dv.notas   AS disenoNotas,
                             dv.archivoCadUrl,
                             dv.archivoPatronUrl,
                             GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '\n') AS materiales
                      FROM OrdenCompra oc
                      LEFT JOIN Cliente c ON c.id = oc.clienteId
                      LEFT JOIN OrdenProduccion op ON op.ordenCompraId = oc.id
                      LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                      LEFT JOIN Diseno d ON d.id = dv.disenoId
                      LEFT JOIN ListaMateriales lm ON lm.disenoVersionId = dv.id
                      WHERE oc.id = ?
                      GROUP BY oc.id, oc.clienteId, c.nombre,
                               oc.folio, oc.fecha, oc.estatus, oc.moneda, oc.total,
                               d.nombre, d.descripcion, dv.version, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl";
            try {
                return $db->query($sql2, [$id])->getRowArray() ?: null;
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }
}
