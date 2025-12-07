<?php

namespace App\Controllers;

use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;

class NotificacionesController extends BaseController
{
    protected $notificationModel;
    protected $userNotificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificacionModel();
        $this->userNotificationModel = new UsuarioNotificacionModel();
    }

    /**
     * Get current user and maquiladora from session
     */
    protected function getCurrentUserData(): array
    {
        $session = session();
        return [
            'userId' => $session->get('userId') ?? $session->get('user_id') ?? 1,
            'maquiladoraId' => $session->get('maquiladoraID') ?? $session->get('maquiladora_id') ?? 1
        ];
    }

    /**
     * API endpoint para obtener notificaciones
     */
    public function apiIndex()
    {
        $userData = $this->getCurrentUserData();
        $limit = $this->request->getVar('limit') ?? 10;

        $notifications = $this->notificationModel->getWithReadStatus(
            $userData['maquiladoraId'],
            $userData['userId'],
            (int) $limit
        );

        // Add time ago for each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * API endpoint para contar notificaciones no leídas
     */
    public function apiUnreadCount()
    {
        $userData = $this->getCurrentUserData();

        $count = $this->notificationModel->getUnreadCount(
            $userData['maquiladoraId'],
            $userData['userId']
        );

        return $this->response->setJSON([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * API endpoint para marcar notificación como leída
     */
    public function apiMarkAsRead($id = null)
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'ID de notificación requerido'
            ]);
        }

        $userData = $this->getCurrentUserData();

        $success = $this->userNotificationModel->markAsRead(
            (int) $id,
            $userData['userId'],
            $userData['maquiladoraId']
        );

        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error al marcar notificación'
            ]);
        }
    }

    /**
     * API endpoint para marcar todas como leídas
     */
    public function apiMarkAllAsRead()
    {
        $userData = $this->getCurrentUserData();

        $success = $this->userNotificationModel->markAllAsRead(
            $userData['userId'],
            $userData['maquiladoraId']
        );

        if ($success) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Todas las notificaciones marcadas como leídas'
            ]);
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error al marcar notificaciones'
            ]);
        }
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
            return date('d/m/Y', $timestamp);
        }
    }

    /**
     * Vista principal de notificaciones (compatibilidad con sistema existente)
     */
    public function index()
    {
        $userData = $this->getCurrentUserData();
        
        $notifications = $this->notificationModel->getWithReadStatus(
            $userData['maquiladoraId'],
            $userData['userId'],
            50
        );

        // Add time ago for each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }

        $data = [
            'notifications' => $notifications,
            'unread_count' => $this->notificationModel->getUnreadCount(
                $userData['maquiladoraId'],
                $userData['userId']
            )
        ];

        return view('notificaciones/index', $data);
    }
}
