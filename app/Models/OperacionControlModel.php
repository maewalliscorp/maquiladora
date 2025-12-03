<?php

namespace App\Models;

use CodeIgniter\Model;

class OperacionControlModel extends Model
{
    protected $table = 'operaciones_control';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'controlBultoId',
        'nombre_operacion',
        'piezas_requeridas',
        'piezas_completadas',
        'porcentaje_completado',
        'es_componente',
        'orden'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener operaciones con progreso de un control
     */
    public function getOperacionesConProgreso($controlId)
    {
        return $this->where('controlBultoId', $controlId)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    /**
     * Actualizar progreso de una operación
     * Recalcula el porcentaje basado en piezas completadas vs requeridas
     */
    public function actualizarProgreso($operacionId)
    {
        $operacion = $this->find($operacionId);

        if (!$operacion) {
            return false;
        }

        $piezasRequeridas = $operacion['piezas_requeridas'];
        $piezasCompletadas = $operacion['piezas_completadas'];

        // Calcular porcentaje
        $porcentaje = $piezasRequeridas > 0
            ? round(($piezasCompletadas / $piezasRequeridas) * 100, 2)
            : 0;

        // No permitir más del 100%
        $porcentaje = min($porcentaje, 100);

        // Actualizar
        $this->update($operacionId, [
            'porcentaje_completado' => $porcentaje
        ]);

        // Actualizar estado del control
        $controlBultosModel = new \App\Models\ControlBultosModel();
        $controlBultosModel->actualizarEstado($operacion['controlBultoId']);

        return $porcentaje;
    }

    /**
     * Incrementar piezas completadas
     */
    public function incrementarPiezas($operacionId, $cantidad)
    {
        $operacion = $this->find($operacionId);

        if (!$operacion) {
            return false;
        }

        $nuevasCantidad = $operacion['piezas_completadas'] + $cantidad;

        // No permitir exceder las piezas requeridas
        $nuevasCantidad = min($nuevasCantidad, $operacion['piezas_requeridas']);

        $this->update($operacionId, [
            'piezas_completadas' => $nuevasCantidad
        ]);

        // Recalcular progreso
        return $this->actualizarProgreso($operacionId);
    }

    /**
     * Verificar si todos los componentes están completados
     */
    public function getComponentesCompletados($controlId)
    {
        $componentes = $this->where('controlBultoId', $controlId)
            ->where('es_componente', 1)
            ->findAll();

        $completados = 0;
        $total = count($componentes);

        foreach ($componentes as $comp) {
            if ($comp['porcentaje_completado'] >= 100) {
                $completados++;
            }
        }

        return [
            'total' => $total,
            'completados' => $completados,
            'todos_completos' => $total > 0 && $completados === $total,
            'porcentaje' => $total > 0 ? round(($completados / $total) * 100, 2) : 0
        ];
    }

    /**
     * Obtener estadísticas de progreso
     */
    public function getEstadisticas($controlId)
    {
        $operaciones = $this->where('controlBultoId', $controlId)->findAll();

        $stats = [
            'total_operaciones' => count($operaciones),
            'operaciones_completas' => 0,
            'operaciones_en_proceso' => 0,
            'operaciones_pendientes' => 0,
            'progreso_promedio' => 0,
        ];

        $sumaPorcentajes = 0;

        foreach ($operaciones as $op) {
            $sumaPorcentajes += $op['porcentaje_completado'];

            if ($op['porcentaje_completado'] >= 100) {
                $stats['operaciones_completas']++;
            } elseif ($op['porcentaje_completado'] > 0) {
                $stats['operaciones_en_proceso']++;
            } else {
                $stats['operaciones_pendientes']++;
            }
        }

        if (count($operaciones) > 0) {
            $stats['progreso_promedio'] = round($sumaPorcentajes / count($operaciones), 2);
        }

        return $stats;
    }
}
