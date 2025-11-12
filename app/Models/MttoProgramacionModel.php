<?php
namespace App\Models;

use CodeIgniter\Model;

class MttoProgramacionModel extends Model
{
    protected $table         = 'mtto_programacion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true; // requiere columnas created_at / updated_at (nullable)
    protected $allowedFields = [
        'maquina_id','titulo','descripcion','prioridad',
        'fecha_inicio','fecha_fin','frecuencia','estado','color'
    ];

    public function eventsBetween(string $start, string $end): array
    {
        return $this->where('fecha_inicio <=', $end)
            ->where('fecha_fin >=',   $start)
            ->findAll();
    }
}
