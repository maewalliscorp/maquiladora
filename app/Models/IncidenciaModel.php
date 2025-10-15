<?php
namespace App\Models;

use CodeIgniter\Model;

class IncidenciaModel extends Model
{
    protected $table         = 'incidencia';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    // Campos reales de la tabla (según tu diagrama)
    protected $allowedFields = [
        'fecha', 'tipo', 'prioridad', 'descripcion', 'accion',
        'empleadoFK', 'ordenProduccionFK'
    ];
}
