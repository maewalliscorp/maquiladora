<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdenModel extends Model
{
    // 🔹 Nombre real de la tabla en tu BD
    protected $table         = 'orden_compra';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';

    // Campos que se pueden actualizar por update()/save()
    protected $allowedFields = [
        'clienteId',
        'folio',
        'fecha',
        'estatus',
        'moneda',
        'total',
        'maquiladoraID',
        'op',
        'cajas',
        'peso',
        'embarqueId', // asegúrate de tener esta columna en orden_compra
    ];

    /**
     * Órdenes pendientes de embarque para la vista de "Preparación de envíos".
     *  - No canceladas
     *  - No asignadas a ningún embarque (embarqueId NULL o 0)
     *  - Opcionalmente filtradas por maquiladora
     */
    public function getParaPacking(?int $maquiladoraId = null): array
    {
        $db = \Config\Database::connect();

        // Detectar columnas opcionales
        try {
            $fieldsOc = $db->getFieldNames($this->table);
        } catch (\Throwable $e) {
            $fieldsOc = [];
        }

        $hasOp        = in_array('op',            $fieldsOc, true);
        $hasCajas     = in_array('cajas',         $fieldsOc, true);
        $hasPeso      = in_array('peso',          $fieldsOc, true);
        $hasMaqColumn = in_array('maquiladoraID', $fieldsOc, true);
        $hasEmbCol    = in_array('embarqueId',    $fieldsOc, true);

        // SELECT dinámico
        $select  = 'oc.id,
                    oc.folio AS pedido,
                    oc.fecha,
                    oc.total,
                    oc.clienteId,
                    c.nombre AS clienteNombre';
        $select .= $hasOp    ? ', oc.op'    : ', NULL AS op';
        $select .= $hasCajas ? ', oc.cajas' : ', NULL AS cajas';
        $select .= $hasPeso  ? ', oc.peso'  : ', NULL AS peso';

        $builder = $db->table($this->table . ' oc')
            ->select($select)
            ->join('cliente c', 'c.id = oc.clienteId', 'left')
            ->where('oc.estatus <>', 'cancelada');

        // Si existe columna embarqueId, usamos eso para filtrar pendientes
        if ($hasEmbCol) {
            $builder->groupStart()
                ->where('oc.embarqueId IS NULL', null, false)
                ->orWhere('oc.embarqueId', 0)
                ->groupEnd();
        }

        // Filtro por maquiladora
        if ($maquiladoraId !== null && $hasMaqColumn) {
            $builder->where('oc.maquiladoraID', (int)$maquiladoraId);
        }

        return $builder
            ->orderBy('oc.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Detalle de una orden (para los modales Ver/Editar).
     */
    public function getDetalle(int $id): ?array
    {
        $db = \Config\Database::connect();

        $row = $db->table($this->table . ' oc')
            ->select('oc.*, c.nombre AS cliente')
            ->join('cliente c', 'c.id = oc.clienteId', 'left')
            ->where('oc.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
