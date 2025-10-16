<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueItemModel extends Model
{
    protected $table         = 'embarque_item';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['embarqueId','ordenCompraId','productoId','cantidad','unidadMedida'];

    public function existeVinculo(int $embarqueId, int $ordenId): bool
    {
        return (bool) $this->where('embarqueId', $embarqueId)
            ->where('ordenCompraId', $ordenId)
            ->first();
    }
}
