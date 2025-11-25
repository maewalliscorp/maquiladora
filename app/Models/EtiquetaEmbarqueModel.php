<?php

namespace App\Models;

use CodeIgniter\Model;

class EtiquetaEmbarqueModel extends Model
{
    protected $table          = 'etiqueta_embarque';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'maquiladoraID',
        'embarqueId',
        'codigo',
        'ship_to_nombre',
        'ship_to_direccion',
        'ship_to_ciudad',
        'ship_to_pais',
        'referencia',
        'peso_bruto',
        'peso_neto',
        'bultos',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
