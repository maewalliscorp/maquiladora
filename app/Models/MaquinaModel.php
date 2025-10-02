<?php
namespace App\Models;

use CodeIgniter\Model;

class MaquinaModel extends Model
{
    protected $table      = 'maquina';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['codigo','modelo','fechaCompra','ubicacion','activa'];
    protected $useTimestamps = false;
}
