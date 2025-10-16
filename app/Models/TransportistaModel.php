<?php
namespace App\Models;

use CodeIgniter\Model;

class TransportistaModel extends Model
{
    protected $table         = 'transportista';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    public    $tableName     = 'transportista';
    protected $allowedFields = ['nombre','rfc','contacto','telefono'];
    public $useTimestamps    = false;
}
