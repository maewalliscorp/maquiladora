<?php

namespace App\Models;

use CodeIgniter\Model;

class CorteModel extends Model
{
    protected $table = 'cortes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'idmaquiladora',
        'numero_corte',
        'estilo',
        'prenda',
        'cliente',
        'color',
        'precio',
        'fecha_entrada',
        'fecha_embarque',
        'cortador',
        'tendedor',
        'tela',
        'largo_trazo',
        'ancho_tela',
        'total_prendas',
        'total_tela_usada',
        'consumo_promedio'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getCortesPorMaquiladora($maquiladoraId)
    {
        return $this->where('idmaquiladora', $maquiladoraId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
