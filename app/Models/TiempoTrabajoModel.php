<?php

namespace App\Models;

use CodeIgniter\Model;

class TiempoTrabajoModel extends Model
{
    protected $table            = 'tiempo_trabajo';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'empleadoId',
        'ordenProduccionId',
        'rutaOperacionId',
        'inicio',
        'fin',
        'horas',
        'tipo',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    /**
     * Iniciar tiempo de trabajo
     * @param int $empleadoId
     * @param int $ordenProduccionId
     * @return int|false ID del registro insertado o false si falla
     */
    public function iniciar(int $empleadoId, int $ordenProduccionId)
    {
        if ($empleadoId <= 0 || $ordenProduccionId <= 0) {
            return false;
        }

        $data = [
            'empleadoId'        => $empleadoId,
            'ordenProduccionId' => $ordenProduccionId,
            'rutaOperacionId'   => null,
            'inicio'            => date('Y-m-d H:i:s'),
            'fin'               => null,
            'horas'             => null,
            'tipo'              => null,
        ];

        if ($this->insert($data)) {
            return (int)$this->getInsertID();
        }

        return false;
    }

    /**
     * Finalizar tiempo de trabajo
     * @param int $id ID del registro de tiempo_trabajo
     * @return bool
     */
    public function finalizar(int $id)
    {
        if ($id <= 0) {
            return false;
        }

        // Obtener el registro para calcular las horas
        $registro = $this->find($id);
        if (!$registro || !isset($registro['inicio'])) {
            return false;
        }

        $fin = date('Y-m-d H:i:s');
        $inicio = $registro['inicio'];

        // Calcular horas entre inicio y fin
        $inicioTimestamp = strtotime($inicio);
        $finTimestamp = strtotime($fin);
        
        if ($inicioTimestamp === false || $finTimestamp === false) {
            return false;
        }

        $diferenciaSegundos = $finTimestamp - $inicioTimestamp;
        $horas = $diferenciaSegundos / 3600; // Convertir segundos a horas

        $data = [
            'fin'   => $fin,
            'horas' => round($horas, 2), // Redondear a 2 decimales
        ];

        return $this->update($id, $data);
    }

    /**
     * Obtener registro activo (sin finalizar) para un empleado y orden de producción
     * @param int $empleadoId
     * @param int $ordenProduccionId
     * @return array|null
     */
    public function obtenerActivo(int $empleadoId, int $ordenProduccionId)
    {
        if ($empleadoId <= 0 || $ordenProduccionId <= 0) {
            return null;
        }

        return $this->where('empleadoId', $empleadoId)
            ->where('ordenProduccionId', $ordenProduccionId)
            ->where('fin', null)
            ->first();
    }

    /**
     * Verificar si todos los empleados de un tipo específico (Corte o Empleado) han finalizado su tiempo de trabajo
     * @param int $ordenProduccionId
     * @param string $puesto 'Corte' o 'Empleado'
     * @return bool true si todos han finalizado, false si hay alguno sin finalizar
     */
    public function todosHanFinalizado(int $ordenProduccionId, string $puesto): bool
    {
        if ($ordenProduccionId <= 0 || empty($puesto)) {
            return false;
        }

        // Normalizar el puesto para la comparación
        $puestoNormalizado = trim($puesto);

        // Obtener todos los empleados asignados a esta OP con el puesto especificado
        $sql = "SELECT DISTINCT at.empleadoId, e.puesto, e.nombre, e.apellido
                FROM asignacion_tarea at
                INNER JOIN empleado e ON e.id = at.empleadoId
                WHERE at.ordenProduccionId = ? AND LOWER(TRIM(e.puesto)) = LOWER(TRIM(?))";
        
        $empleadosAsignados = $this->db->query($sql, [$ordenProduccionId, $puestoNormalizado])->getResultArray();
        
        if (empty($empleadosAsignados)) {
            // Si no hay empleados asignados de ese tipo, considerar que todos han finalizado
            return true;
        }

        $empleadoIds = array_map(function($row) {
            return (int)$row['empleadoId'];
        }, $empleadosAsignados);

        // Verificar si todos tienen al menos un tiempo_trabajo finalizado para esta OP
        // y que no tengan ningún tiempo_trabajo activo (sin finalizar)
        foreach ($empleadoIds as $empId) {
            // Verificar si tiene algún registro activo (sin finalizar)
            $sqlActivo = "SELECT id FROM tiempo_trabajo 
                         WHERE empleadoId = ? AND ordenProduccionId = ? AND (fin IS NULL OR fin = '')
                         LIMIT 1";
            $activo = $this->db->query($sqlActivo, [$empId, $ordenProduccionId])->getRowArray();
            if (!empty($activo)) {
                // Hay al menos un registro activo, no todos han finalizado
                return false;
            }

            // Verificar si tiene al menos un registro finalizado
            // Usar una consulta más explícita
            $sqlFinalizado = "SELECT id, fin FROM tiempo_trabajo 
                             WHERE empleadoId = ? AND ordenProduccionId = ? AND fin IS NOT NULL AND fin != ''
                             LIMIT 1";
            $finalizado = $this->db->query($sqlFinalizado, [$empId, $ordenProduccionId])->getRowArray();
            
            if (empty($finalizado)) {
                // Este empleado no tiene ningún registro finalizado, no todos han finalizado
                return false;
            }
        }

        // Todos tienen al menos un registro finalizado y ninguno tiene registros activos
        return true;
    }
}

