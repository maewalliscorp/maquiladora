<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class OrdenCompraModel extends Model
{
    protected $table         = 'orden_compra';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';

    // Ajusta la lista según las columnas reales de tu tabla
    protected $allowedFields = [
        'clienteId',
        'folio',
        'fecha',
        'estatus',
        'moneda',
        'total',
        'maquiladoraID',
        'maquiladoraId', // por si usas este nombre
        'op',            // si lo tienes como columna extra
        'cajas',         // idem
        'peso',          // idem
    ];

    /**
     * Devuelve las órdenes de compra para la vista de
     * "Preparación de Envíos" (packing).
     *
     * Regla:
     *  - Si NO hay embarque actual => sólo muestra órdenes
     *    que NO estén ligadas a ningún embarque_item.
     *  - Si hay embarque actual => muestra:
     *      * órdenes sin embarque_item
     *      * órdenes ligadas a ESTE embarque
     *    y oculta las que estén ligadas a OTROS embarques.
     *
     * @param int|null $maquiladoraId  Filtro por maquiladora (puede ser null)
     * @param int|null $embarqueId     Embarque "abierto" actual (puede ser null)
     * @return array<int,array<string,mixed>>
     */
    public function listarParaPacking(?int $maquiladoraId = null, ?int $embarqueId = null): array
    {
        $db = Database::connect();

        // Descubrimos columnas reales de orden_compra
        try {
            $colsOc = array_flip($db->getFieldNames('orden_compra'));
        } catch (\Throwable $e) {
            $colsOc = [];
        }

        $builder = $db->table($this->table . ' oc');

        // SELECT base
        $select = "
            oc.id,
            oc.folio                               AS pedido,
            oc.fecha,
            oc.estatus,
            oc.moneda,
            oc.total,
            oc.clienteId,
            c.nombre                               AS clienteNombre,
            op.folio                               AS op,
            COALESCE(SUM(oci.cantidad), 0)        AS cajas
        ";

        // Peso: si existe la columna la usamos, si no devolvemos 0
        if (isset($colsOc['peso'])) {
            $select .= ", COALESCE(oc.peso, 0)      AS peso";
        } else {
            $select .= ", 0                          AS peso";
        }

        // Para saber si ya tiene algún embarque, obtenemos el máximo embarqueId
        if ($db->tableExists('embarque_item')) {
            $select .= ", MAX(ei.embarqueId)        AS embarqueIdRef";
        } else {
            $select .= ", NULL                      AS embarqueIdRef";
        }

        // false => no escapa la cadena, la manda tal cual (evita problemas con funciones)
        $builder->select($select, false);

        // Cliente
        $builder->join('cliente c', 'c.id = oc.clienteId', 'left');

        // Orden de producción (si existe la tabla)
        if ($db->tableExists('orden_produccion')) {
            $builder->join('orden_produccion op', 'op.ordenCompraId = oc.id', 'left');
        }

        // Detalle de orden para sumar cajas
        if ($db->tableExists('orden_compra_item')) {
            $builder->join('orden_compra_item oci', 'oci.ordenId = oc.id', 'left');
        }

        // Relación con embarques
        if ($db->tableExists('embarque_item')) {
            $builder->join('embarque_item ei', 'ei.ordenCompraId = oc.id', 'left');
        }

        // Filtro por maquiladora
        if ($maquiladoraId !== null) {
            if (isset($colsOc['maquiladoraID'])) {
                $builder->where('oc.maquiladoraID', $maquiladoraId);
            } elseif (isset($colsOc['maquiladoraId'])) {
                $builder->where('oc.maquiladoraId', $maquiladoraId);
            }
        }

        // Reglas de embarque
        if ($embarqueId !== null && $embarqueId > 0 && $db->tableExists('embarque_item')) {
            // Mostrar:
            //  - órdenes ligadas a ESTE embarque
            //  - órdenes sin embarque_item
            $builder->groupStart()
                ->where('ei.embarqueId', $embarqueId)
                ->orWhere('ei.embarqueId IS NULL', null, false)
                ->groupEnd();
        } elseif ($db->tableExists('embarque_item')) {
            // No hay embarque actual => sólo órdenes sin embarque_item
            $builder->where('ei.embarqueId IS NULL', null, false);
        }

        // Group by de campos no agregados
        $groupBy = "
            oc.id,
            oc.folio,
            oc.fecha,
            oc.estatus,
            oc.moneda,
            oc.total,
            oc.clienteId,
            c.nombre,
            op.folio
        ";
        if (isset($colsOc['peso'])) {
            $groupBy .= ", oc.peso";
        }

        $builder->groupBy($groupBy);
        $builder->orderBy('oc.fecha', 'DESC');

        $rows = $builder->get()->getResultArray();

        // Calculamos enEmbarque (0/1) en PHP, evitando el CASE en SQL
        foreach ($rows as &$row) {
            $row['enEmbarque'] = !empty($row['embarqueIdRef']) ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /**
     * Versión "clásica": pendientes de embarque (sin usar embarqueId).
     * La dejo por si en algún otro lugar ya la estás usando.
     */
    public function listarPendientes(?int $maquiladoraId = null): array
    {
        // Solo órdenes sin embarque_item
        return $this->listarParaPacking($maquiladoraId, null);
    }
}
