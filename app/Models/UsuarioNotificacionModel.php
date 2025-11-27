<?php
namespace App\Models;

use CodeIgniter\Model;

class UsuarioNotificacionModel extends Model
{
    // OJO: tu tabla es camelCase
    protected $table = 'usuarioNotificacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = ['maquiladoraID', 'idUserFK', 'idNotificacionFK', 'is_leida'];

    public function contarNoLeidas(int $userId): int
    {
        return (int) $this->where(['idUserFK' => $userId, 'is_leida' => 0])->countAllResults();
    }

    /**
     * Mark notification as read for a user
     */
    public function markAsRead(int $notificationId, int $userId, int $maquiladoraId): bool
    {
        $existing = $this->where([
            'idNotificacionFK' => $notificationId,
            'idUserFK' => $userId,
            'maquiladoraID' => $maquiladoraId
        ])->first();

        if ($existing) {
            return $this->update($existing['id'], ['is_leida' => 1]);
        } else {
            return $this->insert([
                'maquiladoraID' => $maquiladoraId,
                'idUserFK' => $userId,
                'idNotificacionFK' => $notificationId,
                'is_leida' => 1
            ]) !== false;
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId, int $maquiladoraId): bool
    {
        $db = $this->db;

        $notifications = $db->table('notificaciones')
            ->select('id')
            ->where('maquiladoraID', $maquiladoraId)
            ->get()
            ->getResultArray();

        foreach ($notifications as $notification) {
            $this->markAsRead($notification['id'], $userId, $maquiladoraId);
        }

        return true;
    }
}
