<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table         = 'cliente';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nombre', 'email', 'telefono', 'rfc', 'tipo_persona', 'fechaRegistro']; // agrega los que uses

    public function listado(): array
    {
        return $this->select('id, nombre')->orderBy('nombre','ASC')->findAll();
    }
}
