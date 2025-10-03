<?php
namespace App\Models;

use CodeIgniter\Model;

class MaquinaModel extends Model
{
    protected $table            = 'maquina';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    // Ajusta a tus columnas existentes en BD
    protected $allowedFields    = [
        'codigo', 'modelo', 'fabricante', 'serie', 'fechaCompra', 'activa', 'ubicacion'
    ];

    // Añade el texto de estado a partir de 'activa'
    public function withEstado(array $rows): array
    {
        return array_map(function ($r) {
            $r['estado_txt'] = (int)($r['activa'] ?? 1) === 1 ? 'Operativa' : 'En reparación';
            return $r;
        }, $rows);
    }
}
