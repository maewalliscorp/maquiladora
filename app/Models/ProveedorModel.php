<?php namespace App\Models;

use CodeIgniter\Model;

class ProveedorModel extends Model
{
    protected $table         = 'proveedor';
    protected $primaryKey    = 'id_proveedor';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'codigo',
        'nombre',
        'rfc',
        'email',
        'telefono',
        'direccion',
        'tipo_alerta',
    ];
}
