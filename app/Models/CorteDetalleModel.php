<?php

namespace App\Models;

use CodeIgniter\Model;

class CorteDetalleModel extends Model
{
    protected $table = 'cortes_detalles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'corte_id',
        'numero_rollo',
        'lote',
        'color_rollo',
        'peso_kg',
        'longitud_mts',
        'metros_usados',
        'merma_danada',
        'merma_faltante',
        'merma_desperdicio',
        'tela_sobrante',
        'diferencia',
        'cantidad_lienzos',
        'total_prendas_rollo'
    ];

    public function getDetallesPorCorte($corteId)
    {
        return $this->where('corte_id', $corteId)
            ->orderBy('numero_rollo', 'ASC')
            ->findAll();
    }
}
