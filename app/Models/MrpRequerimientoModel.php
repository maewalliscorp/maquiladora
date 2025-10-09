<?php
namespace App\Models;

use CodeIgniter\Model;

class MrpRequerimientoModel extends Model
{
    protected $table            = 'mrp_requerimiento';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['mat','u','necesidad','stock','comprar'];

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
