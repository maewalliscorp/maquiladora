<?php
namespace App\Models;

use CodeIgniter\Model;

class MttoDetectadoModel extends Model
{
    protected $table         = 'mtto_detectado';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'otMttoId',        // FK -> mtto.id
        'accion',
        'repuestosUsados',
        'tiempoHoras'      // DECIMAL(5,2)
    ];
}
