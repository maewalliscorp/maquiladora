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
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Insertar la notificación
            $data = [
                'maquiladoraID' => $maquiladoraId,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'sub' => $sub,
                'nivel' => $nivel,
                'color' => $color ?? $this->getColorForLevel($nivel)
            ];

            $notificationId = $this->notificationModel->insert($data);
            
            if (!$notificationId) {
                throw new \Exception('No se pudo crear la notificación');
            }

            // Obtener todos los usuarios de la maquiladora
            $usuarios = $db->table('users')
                           ->where('maquiladoraIdFK', $maquiladoraId)
                           ->get()
                           ->getResultArray();

            // Asignar la notificación a cada usuario
            foreach ($usuarios as $usuario) {
                $userNotificationData = [
                    'maquiladoraID' => $maquiladoraId,
                    'idNotificacionFK' => $notificationId,
                    'idUserFK' => $usuario['id'],
                    'is_leida' => 0
                ];
                
                $this->userNotificationModel->insert($userNotificationData);
            }

            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de notificación');
            }

            return $notificationId;
            
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al crear notificación: ' . $e->getMessage());
            return false;
        }
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

    /**
     * Notificación cuando se agrega un cliente
     */
    public function notifyClienteAgregado(int $maquiladoraId, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Cliente Agregado',
            "Se ha registrado el cliente: {$clienteNombre}",
            null,
            'success'
        );
    }

    /**
     * Notificación cuando se actualiza un cliente
     */
    public function notifyClienteActualizado(int $maquiladoraId, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Cliente Actualizado',
            "Se han actualizado los datos del cliente: {$clienteNombre}",
            null,
            'info'
        );
    }

    /**
     * Notificación cuando se elimina un cliente
     */
    public function notifyClienteEliminado(int $maquiladoraId, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Cliente Eliminado',
            "Se ha eliminado el cliente: {$clienteNombre}",
            null,
            'warning'
        );
    }

    /**
     * Notificación cuando se agrega un diseño
     */
    public function notifyDisenoAgregado(int $maquiladoraId, string $disenoNombre, string $codigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Diseño Agregado',
            "Se ha creado el diseño: {$codigo} - {$disenoNombre}",
            null,
            'success'
        );
    }

    /**
     * Notificación cuando se actualiza un diseño
     */
    public function notifyDisenoActualizado(int $maquiladoraId, string $disenoNombre, string $codigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Diseño Actualizado',
            "Se ha actualizado el diseño: {$codigo} - {$disenoNombre}",
            null,
            'info'
        );
    }

    /**
     * Notificación cuando se elimina un diseño
     */
    public function notifyDisenoEliminado(int $maquiladoraId, string $disenoNombre, string $codigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Diseño Eliminado',
            "Se ha eliminado el diseño: {$codigo} - {$disenoNombre}",
            null,
            'warning'
        );
    }

    /**
     * Notificación cuando se agrega un pedido
     */
    public function notifyPedidoAgregado(int $maquiladoraId, string $pedidoCodigo, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Pedido Creado',
            "Se ha creado el pedido {$pedidoCodigo} para el cliente {$clienteNombre}",
            null,
            'info'
        );
    }

    public function notifyOrdenEstatusActualizado(int $maquiladoraId, string $ordenFolio, string $nuevoEstatus, string $clienteNombre): int|false
    {
        // Determinar el nivel y color según el estatus
        $nivel = 'info';
        $estatusLower = strtolower($nuevoEstatus);
        
        if (strpos($estatusLower, 'completada') !== false || strpos($estatusLower, 'finalizada') !== false) {
            $nivel = 'success';
        } elseif (strpos($estatusLower, 'en proceso') !== false || strpos($estatusLower, 'corte') !== false) {
            $nivel = 'primary';
        } elseif (strpos($estatusLower, 'pausada') !== false || strpos($estatusLower, 'detenida') !== false) {
            $nivel = 'warning';
        } elseif (strpos($estatusLower, 'cancelada') !== false) {
            $nivel = 'danger';
        }

        return $this->createNotification(
            $maquiladoraId,
            'Estatus de Orden Actualizado',
            "La orden {$ordenFolio} ha cambiado a estatus: {$nuevoEstatus}",
            "Cliente: {$clienteNombre}",
            $nivel
        );
    }

    /**
     * Notificación cuando se actualiza el estado de un pedido
     */
    public function notifyPedidoEstadoActualizado(int $maquiladoraId, string $pedidoCodigo, string $estado): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Estado de Pedido Actualizado',
            "El pedido {$pedidoCodigo} ha cambiado a estado: {$estado}",
            null,
            'info'
        );
    }

    /**
     * Notificación cuando se agrega una orden de producción
     */
    public function notifyOrdenProduccionAgregada(int $maquiladoraId, string $ordenCodigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nueva Orden de Producción',
            "Se ha creado la orden de producción: {$ordenCodigo}",
            null,
            'info'
        );
    }

    /**
     * Notificación de mantenimiento programado
     */
    public function notifyMantenimientoProgramado(int $maquiladoraId, string $maquinaNombre, string $fecha): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Mantenimiento Programado',
            "Se ha programado mantenimiento para {$maquinaNombre} el {$fecha}",
            null,
            'warning'
        );
    }

    /**
     * Notificación de incidencia reportada
     */
    public function notifyIncidenciaReportada(int $maquiladoraId, string $tipoIncidencia, string $descripcion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Incidencia Reportada',
            "Se ha reportado una incidencia: {$tipoIncidencia} - {$descripcion}",
            null,
            'danger'
        );
    }
}
