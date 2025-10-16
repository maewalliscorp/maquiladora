<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueModel extends Model
{
    protected $table         = 'embarque';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['clienteId', 'folio', 'fecha', 'estatus'];

    public function getAbiertoActual(): ?array
    {
        return $this->where('estatus', 'abierto')->orderBy('id', 'DESC')->first();
    }
}
