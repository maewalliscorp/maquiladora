<?php
namespace App\Models;

use CodeIgniter\Model;

class PlanMantenimientoModel extends Model
{
    protected $table         = 'plan_mantenimiento';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'maquinaId', 'tipo', 'frecuencia', 'intervalo', 'inicio', 'tareas', 'activo'
    ];
}
