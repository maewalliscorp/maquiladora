<?php
namespace App\Models;

use CodeIgniter\Model;

class UsuarioNotificacionModel extends Model
{
    // OJO: tu tabla es camelCase
    protected $table         = 'usuarioNotificacion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = ['idUserFK','idNotificacionFK','is_leida'];

    public function contarNoLeidas(int $userId): int
    {
        return (int) $this->where(['idUserFK' => $userId, 'is_leida' => 0])->countAllResults();
    }
}
