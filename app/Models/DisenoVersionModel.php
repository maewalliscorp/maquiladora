<?php namespace App\Models;

use CodeIgniter\Model;

class DisenoVersionModel extends Model
{
    protected $table         = 'diseno_version';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['disenoId','version','fecha','notas','foto','patron','aprobado'];
}
