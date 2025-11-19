<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueModel extends Model
{
    protected $table         = 'embarque';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'maquiladoraID',
        'clienteId',
        'folio',
        'fecha',
        'estatus',
    ];

    /**
     * Devuelve el embarque "abierto" más reciente de la maquiladora.
     * Si no se pasa maquiladora, devuelve el último con estatus 'abierto'.
     */
    public function getAbiertoActual(?int $maquiladoraId = null): ?array
    {
        $builder = $this->where('estatus', 'abierto');

        if ($maquiladoraId !== null) {
            if ($this->fieldExists('maquiladoraID')) {
                $builder->where('maquiladoraID', $maquiladoraId);
            } elseif ($this->fieldExists('maquiladoraId')) {
                $builder->where('maquiladoraId', $maquiladoraId);
            }
        }

        $row = $builder->orderBy('id', 'DESC')->first();
        return $row ?: null;
    }

    /* Helper simple para revisar columnas */
    protected function fieldExists(string $field): bool
    {
        try {
            $fields = $this->db->getFieldNames($this->table);
            return in_array($field, $fields, true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
