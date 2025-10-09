<?php namespace App\Models;

use CodeIgniter\Model;

class ListaMaterialesModel extends Model
{
    protected $table         = 'lista_materiales';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['disenoVersionId','articuloId','cantidadPorUnidad','mermaPct'];
}
