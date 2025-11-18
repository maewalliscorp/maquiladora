<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueModel extends Model
{
    protected $table         = 'embarque';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['clienteId', 'folio', 'fecha', 'estatus', 'maquiladoraID'];

    public function getAbiertoActual(?int $maquiladoraId = null): ?array
    {
        $builder = $this->where('estatus', 'abierto');

        if ($maquiladoraId !== null) {
            $db = \Config\Database::connect();
            try {
                $fields = $db->getFieldNames($this->table);
                if (in_array('maquiladoraID', $fields, true)) {
                    $builder = $builder->where('maquiladoraID', (int)$maquiladoraId);
                }
            } catch (\Throwable $e) {
                // ignore, fallback sin filtro extra
            }
        }

        return $builder->orderBy('id', 'DESC')->first();
    }
}
