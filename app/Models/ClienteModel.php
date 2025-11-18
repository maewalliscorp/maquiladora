<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table         = 'cliente';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nombre', 'email', 'telefono', 'rfc', 'tipo_persona', 'fechaRegistro']; // agrega los que uses

    public function listado(?int $maquiladoraId = null): array
    {
        if ($maquiladoraId === null) {
            return $this->select('id, nombre')->orderBy('nombre','ASC')->findAll();
        }

        $db = \Config\Database::connect();

        return $db->table('cliente c')
            ->select('c.id, c.nombre')
            ->join('Cliente_Maquiladora cm', 'cm.clienteFK = c.id', 'inner')
            ->where('cm.maquiladoraFK', (int)$maquiladoraId)
            ->orderBy('c.nombre','ASC')
            ->get()->getResultArray();
    }
}
