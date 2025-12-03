<?php

namespace App\Models;

use CodeIgniter\Model;

class CorteTallaModel extends Model
{
    protected $table = 'cortes_tallas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'corte_detalle_id',
        'talla',
        'cantidad'
    ];

    public function getTallasPorDetalle($detalleId)
    {
        return $this->where('corte_detalle_id', $detalleId)->findAll();
    }
}
