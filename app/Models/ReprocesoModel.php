<?php
namespace App\Models;

use CodeIgniter\Model;

class ReprocesoModel extends Model
{
    protected $table         = 'reproceso';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = ['inspeccionId','accion','cantidad','fecha'];

    protected $validationRules = [
        'inspeccionId' => 'required|integer',
        'accion'       => 'required|string|min_length[3]',
        'cantidad'     => 'required|numeric',
        'fecha'        => 'required|valid_date[Y-m-d]',
    ];
}
