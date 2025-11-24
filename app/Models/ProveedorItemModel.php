<?php namespace App\Models;

use CodeIgniter\Model;

class ProveedorItemModel extends Model
{
    protected $table         = 'proveedor_item';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'id_proveedorOC',
        'articuloId',
        'cantidad',
        'unidadMedida',
        'precioUnitario',
        'fechaEntregaPrevista',
    ];
}
