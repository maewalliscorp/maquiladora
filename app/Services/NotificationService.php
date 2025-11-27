<?php

namespace App\Services;

use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;

class NotificationService
{
    protected $notificationModel;
    protected $userNotificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificacionModel();
        $this->userNotificationModel = new UsuarioNotificacionModel();
    }

    /**
     * Create a notification
     * 
     * @param int $maquiladoraId
     * @param string $titulo
     * @param string $mensaje
     * @param string|null $sub
     * @param string $nivel (info, success, warning, danger)
     * @param string|null $color
     * @return int|false Notification ID or false on failure
     */
    protected function createNotification(
        int $maquiladoraId,
        string $titulo,
        string $mensaje,
        ?string $sub = null,
        string $nivel = 'info',
        ?string $color = null
    ) {
        $data = [
            'maquiladoraID' => $maquiladoraId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'sub' => $sub,
            'nivel' => $nivel,
            'color' => $color ?? $this->getColorForLevel($nivel)
        ];

        return $this->notificationModel->insert($data);
    }

    /**
     * Get color based on level
     */
    protected function getColorForLevel(string $nivel): string
    {
        return match ($nivel) {
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'info' => '#17a2b8',
            default => '#6c757d'
        };
    }

    /**
     * Create stock alert notification
     */
    public function createStockAlert(int $maquiladoraId, string $material, float $stock, float $reorderPoint): int|false
    {
        $percentage = ($stock / $reorderPoint) * 100;

        if ($stock <= 0) {
            return $this->createNotification(
                $maquiladoraId,
                'Stock Agotado',
                "El material '{$material}' está agotado",
                'Requiere atención inmediata',
                'danger'
            );
        } elseif ($percentage < 20) {
            return $this->createNotification(
                $maquiladoraId,
                'Stock Bajo',
                "El material '{$material}' tiene stock bajo ({$stock} unidades)",
                'Considere realizar un pedido',
                'warning'
            );
        }

        return false;
    }

    /**
     * Create incident notification
     */
    public function createIncidentNotification(int $maquiladoraId, string $type, int $cantidad): int|false
    {
        $typeText = $type === 'desecho' ? 'Desecho' : 'Reproceso';

        return $this->createNotification(
            $maquiladoraId,
            "Nuevo {$typeText} Registrado",
            "Se ha registrado un nuevo {$typeText} de {$cantidad} unidades",
            'Revisar en módulo de Calidad',
            'warning'
        );
    }

    /**
     * Create client notification
     */
    public function createClientNotification(int $maquiladoraId, string $clientName): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Cliente',
            "Se ha agregado el cliente '{$clientName}'",
            null,
            'success'
        );
    }

    /**
     * Create sample/design notification
     */
    public function createSampleNotification(int $maquiladoraId, string $sampleName, ?string $status = null): int|false
    {
        if ($status) {
            return $this->createNotification(
                $maquiladoraId,
                'Cambio de Estado en Muestra',
                "La muestra '{$sampleName}' cambió a: {$status}",
                null,
                'info'
            );
        } else {
            return $this->createNotification(
                $maquiladoraId,
                'Nueva Muestra',
                "Se ha creado la muestra '{$sampleName}'",
                null,
                'success'
            );
        }
    }

    /**
     * Create work order notification
     */
    public function createOrderNotification(int $maquiladoraId, string $orderNumber, string $type = 'new'): int|false
    {
        if ($type === 'new') {
            return $this->createNotification(
                $maquiladoraId,
                'Nueva Orden de Trabajo',
                "Se ha creado la orden #{$orderNumber}",
                null,
                'info'
            );
        } else {
            return $this->createNotification(
                $maquiladoraId,
                'Cambio en Orden',
                "La orden #{$orderNumber} ha cambiado de estado",
                null,
                'info'
            );
        }
    }

    /**
     * Create MRP notification
     */
    public function createMRPNotification(int $maquiladoraId, string $material, float $cantidad): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Materiales Necesarios',
            "Se requieren {$cantidad} unidades de '{$material}'",
            'Revisar en módulo MRP',
            'warning'
        );
    }

    /**
     * Create OC generated notification
     */
    public function createOCNotification(int $maquiladoraId, int $ocId, string $material): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Orden de Compra Generada',
            "Se generó la OC #{$ocId} para '{$material}'",
            'Ver detalles en MRP',
            'success'
        );
    }
}
