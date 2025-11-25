<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueAduanaModel extends Model
{
    protected $table      = 'embarque_aduana';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'maquiladoraID',
        'embarqueId',
        'aduana',
        'numeroPedimento',
        'fraccionArancelaria',
        'observaciones',
        'usuarioId',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
