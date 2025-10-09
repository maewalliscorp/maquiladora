<?php namespace App\Models;

use CodeIgniter\Model;

class ProveedorModel extends Model
{
    protected $table         = 'proveedor';
    protected $primaryKey    = 'id_proveedor'; // ojo: tu PK se llama así
    protected $returnType    = 'array';
    protected $allowedFields = ['codigo','nombre','rfc','email','telefono','direccion'];
}
