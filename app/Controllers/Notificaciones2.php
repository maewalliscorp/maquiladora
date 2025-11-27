<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;
use App\Services\NotificationService;

class Notificaciones2 extends BaseController
{
    protected $notificationModel;
    protected $userNotificationModel;
    protected $notificationService;

    public function __construct()
    {
        $this->notificationModel = new NotificacionModel();
        $this->userNotificationModel = new UsuarioNotificacionModel();
        $this->notificationService = new NotificationService();
    }

    /**
     * Main notifications view
     */
    public function index()
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);
        $maquiladoraId = (int) (session('maquiladoraID') ?? 1);

        // Get notifications with read status
        $notifications = $this->notificationModel->getWithReadStatus($maquiladoraId, $userId, 50);

        // Get unread count
        $unreadCount = $this->notificationModel->getUnreadCount($maquiladoraId, $userId);

        // Add time ago to each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }

        return view('modulos/notificaciones2', [
            'title' => 'Notificaciones (Sistema Nuevo)',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);
        $maquiladoraId = (int) (session('maquiladoraID') ?? 1);

        $this->userNotificationModel->markAsRead((int) $id, $userId, $maquiladoraId);

        return redirect()->back()->with('success', 'Notificación marcada como leída');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);
        $maquiladoraId = (int) (session('maquiladoraID') ?? 1);

        $this->userNotificationModel->markAllAsRead($userId, $maquiladoraId);

        return redirect()->back()->with('success', 'Todas las notificaciones marcadas como leídas');
    }

    /**
     * Delete notification (remove user link)
     */
    public function delete($id)
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);

        $this->userNotificationModel
            ->where(['idUserFK' => $userId, 'idNotificacionFK' => $id])
            ->delete();

        return redirect()->back()->with('success', 'Notificación eliminada');
    }

    /**
     * Generate test notifications (for demo purposes)
     */
    public function generateTestNotifications()
    {
        $maquiladoraId = (int) (session('maquiladoraID') ?? 1);

        // Create different types of test notifications
        $this->notificationService->createStockAlert($maquiladoraId, 'Tela Algodón 180g', 5.0, 50.0);
        $this->notificationService->createStockAlert($maquiladoraId, 'Hilo 40/2', 0.0, 25.0);
        $this->notificationService->createIncidentNotification($maquiladoraId, 'desecho', 25);
        $this->notificationService->createIncidentNotification($maquiladoraId, 'reproceso', 15);
        $this->notificationService->createClientNotification($maquiladoraId, 'Textiles Premium S.A.');
        $this->notificationService->createSampleNotification($maquiladoraId, 'Diseño Primavera 2025');
        $this->notificationService->createOrderNotification($maquiladoraId, 'OP-2025-0042', 'new');
        $this->notificationService->createMRPNotification($maquiladoraId, 'Etiquetas talla M', 500);
        $this->notificationService->createOCNotification($maquiladoraId, 123, 'Botones metálicos');

        return redirect()->to('/modulo3/notificaciones2')->with('success', '9 notificaciones de prueba generadas');
    }

    /**
     * Helper: Convert timestamp to "time ago" format
     */
    protected function timeAgo($datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Justo ahora';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "Hace {$mins} " . ($mins == 1 ? 'minuto' : 'minutos');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Hace {$hours} " . ($hours == 1 ? 'hora' : 'horas');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "Hace {$days} " . ($days == 1 ? 'día' : 'días');
        } else {
            return date('d/m/Y H:i', $timestamp);
        }
    }
}
