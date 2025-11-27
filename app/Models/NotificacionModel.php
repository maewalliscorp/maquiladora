<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificacionModel extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'maquiladoraID',
        'mensaje',
        'titulo',
        'sub',
        'nivel',
        'color'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get recent notifications for a maquiladora
     * 
     * @param int $maquiladoraId
     * @param int $limit
     * @return array
     */
    public function getRecent(int $maquiladoraId, int $limit = 10): array
    {
        return $this->where('maquiladoraID', $maquiladoraId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * Get notifications with user read status
     * 
     * @param int $maquiladoraId
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getWithReadStatus(int $maquiladoraId, int $userId, int $limit = 10): array
    {
        $db = $this->db;

        return $db->table('notificaciones n')
            ->select('n.*, un.is_leida, un.id as user_notification_id')
            ->join('usuarioNotificacion un', 'un.idNotificacionFK = n.id AND un.idUserFK = ' . $userId, 'left')
            ->where('n.maquiladoraID', $maquiladoraId)
            ->orderBy('n.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get unread count for a user
     * 
     * @param int $maquiladoraId
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $maquiladoraId, int $userId): int
    {
        $db = $this->db;

        // Count notifications that don't have a read record for this user
        $result = $db->query("
            SELECT COUNT(*) as count
            FROM notificaciones n
            LEFT JOIN usuarioNotificacion un ON un.idNotificacionFK = n.id AND un.idUserFK = ?
            WHERE n.maquiladoraID = ? 
            AND (un.is_leida IS NULL OR un.is_leida = 0)
        ", [$userId, $maquiladoraId])->getRowArray();

        return (int) ($result['count'] ?? 0);
    }
}
