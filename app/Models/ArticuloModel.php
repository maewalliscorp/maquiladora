<?php namespace App\Models;

use CodeIgniter\Model;

class ArticuloModel extends Model
{
    protected $table         = 'articulo';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['sku','nombre','tipo','unidadMedida','stockMin','stockMax','activo'];
}
