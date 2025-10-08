<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionModel extends Model
{
    protected $table         = 'inspeccion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false; // activa si tu tabla tiene created_at/updated_at
    protected $allowedFields = [
        'ordenProduccionId','puntoInspeccionId','inspectorId',
        'fecha','resultado','observaciones'
    ];

    protected $validationRules = [
        'ordenProduccionId' => 'required|integer',
        'fecha'             => 'required|valid_date',
        'resultado'         => 'required|string',
        'observaciones'     => 'permit_empty|string'
    ];
}
