<?php
namespace App\Models;

use CodeIgniter\Model;

class IncidenciaModel extends Model
{
    protected $table            = 'incidencia';   // nombre de tabla
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'op', 'tipo', 'fecha', 'descripcion'
    ];
}
