<?php

namespace App\Models;

use CodeIgniter\Database\Query;

class PedidosClientesModel extends \CodeIgniter\Model
{
    protected $table = 'orden_produccion';
    protected $primaryKey = 'id';
    protected $allowedFields = [];
    protected $returnType = 'array';

    /**
     * Obtener órdenes de producción del cliente actual
     */
    public function getOrdenesPorCliente($userId)
    {
        $db = \Config\Database::connect();
        
        try {
            // Primero obtenemos el idcliente directamente del usuario
            $user = $db->table('users')
                ->select('idcliente')
                ->where('id', $userId)
                ->get()
                ->getRowArray();

            if (!$user || !$user['idcliente']) {
                return [];
            }

            $clienteId = $user['idcliente'];

            // Obtenemos las órdenes de producción directamente usando idcliente
            $ordenesProduccion = $db->table('orden_produccion op')
                ->select('
                    op.id,
                    op.folio,
                    op.cantidadPlan,
                    op.fechaInicioPlan,
                    op.fechaFinPlan,
                    op.status,
                    op.ordenCompraId,
                    op.idcliente,
                    oc.folio as folio_oc,
                    oc.fecha as fecha_oc,
                    oc.total,
                    c.nombre as cliente_nombre,
                    d.nombre as disenio_nombre,
                    d.codigo as disenio_codigo,
                    dv.version as disenio_version
                ')
                ->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left')
                ->join('cliente c', 'c.id = op.idcliente', 'left')
                ->join('diseno_version dv', 'dv.id = op.disenoVersionId', 'left')
                ->join('diseno d', 'd.id = dv.disenoId', 'left')
                ->where('op.idcliente', $clienteId)
                ->orderBy('op.fechaInicioPlan', 'DESC')
                ->get()
                ->getResultArray();

            // Calcular porcentaje de avance para cada orden
            foreach ($ordenesProduccion as &$orden) {
                $orden['porcentaje_avance'] = $this->calcularPorcentajeAvance($orden['id']);
                
                // Formatear fechas
                if ($orden['fechaInicioPlan']) {
                    $orden['fechaInicioPlan'] = date('Y-m-d', strtotime($orden['fechaInicioPlan']));
                }
                if ($orden['fechaFinPlan']) {
                    $orden['fechaFinPlan'] = date('Y-m-d', strtotime($orden['fechaFinPlan']));
                }
                if ($orden['fecha_oc']) {
                    $orden['fecha_oc'] = date('Y-m-d', strtotime($orden['fecha_oc']));
                }
            }

            return $ordenesProduccion;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getOrdenesPorCliente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una orden de producción específica del cliente
     */
    public function getOrdenPorCliente($ordenId, $userId)
    {
        $db = \Config\Database::connect();
        
        try {
            // Primero obtenemos el idcliente directamente del usuario
            $user = $db->table('users')
                ->select('idcliente')
                ->where('id', $userId)
                ->get()
                ->getRowArray();

            if (!$user || !$user['idcliente']) {
                return null;
            }

            $clienteId = $user['idcliente'];

            // Obtenemos la orden de producción verificando que pertenezca al cliente
            $orden = $db->table('orden_produccion op')
                ->select('
                    op.id,
                    op.folio,
                    op.cantidadPlan,
                    op.fechaInicioPlan,
                    op.fechaFinPlan,
                    op.status,
                    op.ordenCompraId,
                    op.idcliente,
                    oc.folio as folio_oc,
                    oc.fecha as fecha_oc,
                    oc.total,
                    oc.moneda,
                    c.nombre as cliente_nombre,
                    c.email as cliente_email,
                    c.telefono as cliente_telefono,
                    d.nombre as disenio_nombre,
                    d.codigo as disenio_codigo,
                    d.descripcion as disenio_descripcion,
                    dv.version as disenio_version,
                    dv.fecha as disenio_fecha_version
                ')
                ->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left')
                ->join('cliente c', 'c.id = op.idcliente', 'left')
                ->join('diseno_version dv', 'dv.id = op.disenoVersionId', 'left')
                ->join('diseno d', 'd.id = dv.disenoId', 'left')
                ->where('op.id', $ordenId)
                ->where('op.idcliente', $clienteId)
                ->get()
                ->getRowArray();

            if (!$orden) {
                return null;
            }

            // Calcular porcentaje de avance
            $orden['porcentaje_avance'] = $this->calcularPorcentajeAvance($orden['id']);

            // Obtener detalles por tallas
            $orden['tallas_detalle'] = $this->getTallasDetalle($orden['id']);

            // Obtener asignaciones de tareas
            $orden['asignaciones'] = $this->getAsignaciones($orden['id']);

            return $orden;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getOrdenPorCliente: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcular porcentaje de avance de una orden
     */
    private function calcularPorcentajeAvance($ordenId)
    {
        $db = \Config\Database::connect();
        
        try {
            // Contar tareas asignadas
            $totalTareas = $db->table('asignacion_tarea')
                ->where('ordenProduccionId', $ordenId)
                ->countAllResults();

            if ($totalTareas == 0) {
                return 0;
            }

            // Contar tareas completadas
            $tareasCompletadas = $db->table('asignacion_tarea')
                ->where('ordenProduccionId', $ordenId)
                ->where('estado', 'Completada')
                ->countAllResults();

            return round(($tareasCompletadas / $totalTareas) * 100, 2);

        } catch (\Throwable $e) {
            log_message('error', 'Error en calcularPorcentajeAvance: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener detalles por tallas de una orden
     */
    private function getTallasDetalle($ordenId)
    {
        $db = \Config\Database::connect();
        
        try {
            return $db->table('pedido_tallas_detalle ptd')
                ->select('
                    ptd.cantidad,
                    s.nombre as sexo_nombre,
                    t.nombre as talla_nombre
                ')
                ->join('sexo s', 's.id = ptd.id_sexo', 'left')
                ->join('tallas t', 't.id = ptd.id_talla', 'left')
                ->where('ptd.ordenProduccionId', $ordenId)
                ->get()
                ->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'Error en getTallasDetalle: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener asignaciones de tareas de una orden
     */
    private function getAsignaciones($ordenId)
    {
        $db = \Config\Database::connect();
        
        try {
            return $db->table('asignacion_tarea at')
                ->select('
                    at.tarea,
                    at.estado,
                    at.fecha_asignacion,
                    at.fecha_limite,
                    at.fecha_completado,
                    e.nombre as empleado_nombre,
                    e.noEmpleado as empleado_no
                ')
                ->join('empleado e', 'e.id = at.empleadoId', 'left')
                ->where('at.ordenProduccionId', $ordenId)
                ->orderBy('at.fecha_asignacion', 'ASC')
                ->get()
                ->getResultArray();

        } catch (\Throwable $e) {
            log_message('error', 'Error en getAsignaciones: ' . $e->getMessage());
            return [];
        }
    }
}
