<?php
namespace App\Models;

use CodeIgniter\Model;

class MttoProgramacionModel extends Model
{
    protected $table         = 'mtto_programacion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true; // requiere created_at / updated_at

    protected $allowedFields = [
        'maquina_id',
        'responsable_id',
        'titulo',
        'descripcion',
        'prioridad',
        'fecha_inicio',
        'fecha_fin',
        'frecuencia',
        'estado',
        'color',
    ];

    /**
     * Regresa todos los eventos cuyo rango de fechas intersecta
     * con [start, end] (formato 'Y-m-d H:i:s').
     */
    public function eventsBetween(string $start, string $end): array
    {
        return $this->where('fecha_inicio <=', $end)
            ->where('fecha_fin >=',   $start)
            ->findAll();
    }
}
