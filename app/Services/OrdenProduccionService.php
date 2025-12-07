<?php
namespace App\Services;

use App\Models\OrdenProduccionModel;
use App\Constants\OrdenStatus;
use Config\Database;

class OrdenProduccionService
{
    protected $db;
    protected $ordenModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->ordenModel = new OrdenProduccionModel();
    }

    /**
     * Actualiza el estatus de una orden de producción y maneja efectos secundarios.
     * 
     * @param int $id ID de la Orden de Producción
     * @param string $estatus Nuevo estatus
     * @return array Resultado de la operación ['ok' => bool, 'error' => string, ...]
     */
    public function actualizarEstatus(int $id, string $estatus): array
    {
        if ($id <= 0 || empty($estatus)) {
            return ['ok' => false, 'error' => 'Parámetros inválidos'];
        }

        try {
            // Obtener información de la orden antes de actualizar para la notificación
            $ordenInfo = $this->db->table('orden_produccion op')
                                  ->select('op.folio, op.ordenCompraId, oc.folio as oc_folio, c.nombre as cliente_nombre, op.maquiladoraID')
                                  ->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left')
                                  ->join('cliente c', 'c.id = oc.clienteId', 'left')
                                  ->where('op.id', $id)
                                  ->get()
                                  ->getRowArray();

            $this->db->transStart();

            // 1. Actualizar estatus de la OP
            if (!$this->ordenModel->updateEstatus($id, $estatus)) {
                $this->db->transRollback();
                return ['ok' => false, 'error' => 'No se pudo actualizar el estatus'];
            }

            $insId = null;
            $repId = null;

            // 2. Si cambia a "En proceso", generar inspección y reproceso (si no existen)
            if (strcasecmp($estatus, OrdenStatus::EN_PROCESO) === 0) {
                $ids = $this->ensureInspeccionAndReproceso($id);
                $insId = $ids['inspeccionId'];
                $repId = $ids['reprocesoId'];
            }

            // 3. Sincronizar estatus con Orden de Compra
            $this->syncOrdenCompraStatus($id, $estatus);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return ['ok' => false, 'error' => 'Error en la transacción'];
            }

            // 4. Enviar notificación del cambio de estatus
            try {
                $notificationService = new \App\Services\NotificationService();
                
                $folioOP = $ordenInfo['folio'] ?? 'OP-' . $id;
                $clienteNombre = $ordenInfo['cliente_nombre'] ?? 'Cliente desconocido';
                $maquiladoraId = $ordenInfo['maquiladoraID'] ?? session()->get('maquiladora_id') ?? 1;
                
                $notificationService->notifyOrdenEstatusActualizado(
                    $maquiladoraId,
                    $folioOP,
                    $estatus,
                    $clienteNombre
                );
                
                log_message('debug', "Notificación enviada para cambio de estatus de OP {$folioOP} a {$estatus}");
            } catch (\Exception $e) {
                log_message('error', 'Error al enviar notificación de cambio de estatus: ' . $e->getMessage());
            }

            return [
                'ok' => true,
                'id' => $id,
                'status' => $estatus,
                'inspeccionId' => $insId,
                'reprocesoId' => $repId
            ];

        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    /**
     * Asegura que existan registros de inspección y reproceso para la OP.
     */
    private function ensureInspeccionAndReproceso(int $opId): array
    {
        $insId = null;
        $repId = null;

        // Verificar o crear Inspección
        $rowExist = $this->db->table('inspeccion')->select('id')->where('ordenProduccionId', $opId)->get()->getRowArray();

        if ($rowExist) {
            $insId = (int) $rowExist['id'];
        } else {
            $rowIns = [
                'ordenProduccionId' => $opId,
                'puntoInspeccionId' => null,
                'inspectorId' => null,
                'fecha' => null,
                'resultado' => null,
                'observaciones' => null,
            ];
            $this->db->table('inspeccion')->insert($rowIns);
            $insId = (int) $this->db->insertID();
        }

        // Verificar o crear Reproceso
        if ($insId > 0) {
            $rowRepExist = $this->db->table('reproceso')->select('id')->where('inspeccionId', $insId)->get()->getRowArray();

            if ($rowRepExist) {
                $repId = (int) $rowRepExist['id'];
            } else {
                $rowRep = [
                    'inspeccionId' => $insId,
                    'accion' => null,
                    'cantidad' => null,
                    'fecha' => null,
                ];
                $this->db->table('reproceso')->insert($rowRep);
                $repId = (int) $this->db->insertID();
            }
        }

        return ['inspeccionId' => $insId, 'reprocesoId' => $repId];
    }

    /**
     * Actualiza el estatus de la Orden de Compra asociada según el estatus de la OP.
     */
    private function syncOrdenCompraStatus(int $opId, string $estatusOp): void
    {
        // Obtener ordenCompraId
        $rowOP = $this->ordenModel->getDetalleBasico($opId);
        if (!$rowOP || empty($rowOP['ordenCompraId'])) {
            return;
        }

        $ocId = (int) $rowOP['ordenCompraId'];
        $nuevoEstatusOC = null;

        // Mapeo de estatus
        if (strcasecmp($estatusOp, OrdenStatus::EN_CORTE) === 0) {
            $nuevoEstatusOC = OrdenStatus::ACEPTADA;
        } elseif (strcasecmp($estatusOp, OrdenStatus::EN_PROCESO) === 0) {
            $nuevoEstatusOC = OrdenStatus::EN_PROCESO;
        } elseif (strcasecmp($estatusOp, OrdenStatus::PAUSADA) === 0) {
            $nuevoEstatusOC = OrdenStatus::PAUSADA;
        } elseif (strcasecmp($estatusOp, OrdenStatus::COMPLETADA) === 0) {
            $nuevoEstatusOC = OrdenStatus::FINALIZADA;
        }

        if ($nuevoEstatusOC) {
            $this->db->table('orden_compra')->where('id', $ocId)->update(['estatus' => $nuevoEstatusOC]);
        }
    }
}
