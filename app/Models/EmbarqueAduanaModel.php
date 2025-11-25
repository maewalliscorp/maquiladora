<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueAduanaModel extends Model
{
    protected $table            = 'embarque_aduana';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'maquiladoraID',
        'embarqueId',
        'aduana',
        'numeroPedimento',
        'fraccionArancelaria',
        'observaciones',
        'created_at',
        'updated_at',
    ];

    // Timestamps automáticos
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // si activas soft deletes
}
