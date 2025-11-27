<?php
namespace App\Models;

use CodeIgniter\Model;

class ReprocesoModel extends Model
{
    protected $table = 'reproceso';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = ['inspeccionId', 'accion', 'cantidad', 'fecha'];

    protected $validationRules = [];
    protected $skipValidation = true;
}
