<?php
namespace App\Models;

use CodeIgniter\Model;

class MrpOcModel extends Model
{
    protected $table            = 'mrp_oc';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['prov','mat','cant','u','eta'];

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
}
