<?php
namespace App\Models;

use CodeIgniter\Model;

class NotificacionModel extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false; // ya tienes created_at / updated_at pero no las llenamos aquí

    protected $allowedFields = [
        'maquiladoraID',
        'titulo',
        'sub',
        'mensaje',
        'nivel',
        'color',
        'created_at',
        'updated_at'
    ];
}
