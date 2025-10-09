<?php namespace App\Models;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $table         = 'stock';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['articuloId','ubicacionId','loteId','cantidad'];
}
