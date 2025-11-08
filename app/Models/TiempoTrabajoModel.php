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

        $inicio = trim($registro['inicio'] ?? '');
        if (empty($inicio)) {
            log_message('error', "Registro de tiempo_trabajo {$id} no tiene fecha de inicio válida");
            return false;
        }

        $fin = date('Y-m-d H:i:s');
        
        // Validar que la fecha de inicio sea válida
        $inicioTimestamp = strtotime($inicio);
        $finTimestamp = strtotime($fin);
        
        if ($inicioTimestamp === false || $finTimestamp === false) {
            log_message('error', "Error al convertir fechas. Inicio: {$inicio}, Fin: {$fin}");
            return false;
        }

        $diferenciaSegundos = $finTimestamp - $inicioTimestamp;
        if ($diferenciaSegundos < 0) {
            log_message('warning', "La fecha de fin es anterior a la de inicio para registro {$id}");
            return false;
        }

        $horas = $diferenciaSegundos / 3600; // Convertir segundos a horas

        // Calcular horas redondeadas
        $horasCalculadas = round($horas, 2);
        
        // Validar que los valores sean correctos antes de actualizar
        if (empty($fin) || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fin)) {
            log_message('error', "Fecha de fin inválida para registro {$id}: '{$fin}'");
            return false;
        }
        
        if (!is_numeric($horasCalculadas) || $horasCalculadas < 0) {
            log_message('error', "Horas calculadas inválidas para registro {$id}: '{$horasCalculadas}'");
            return false;
        }

        // Usar actualización SQL directa con prepared statements
        // Esto evita problemas con campos DATETIME que puedan tener valores vacíos
        try {
            $db = \Config\Database::connect();
            
            // Deshabilitar el modo estricto temporalmente si está causando problemas
            // Pero primero intentar con la actualización normal
            
            // Usar SQL directo con prepared statements para máximo control
            $sql = "UPDATE tiempo_trabajo SET fin = ?, horas = ? WHERE id = ?";
            
            // Preparar los valores asegurándonos de que sean del tipo correcto
            $params = [
                $fin,                    // DATETIME - ya validado
                (float)$horasCalculadas, // DECIMAL/FLOAT
                (int)$id                 // INT
            ];
            
            log_message('debug', "Intentando actualizar tiempo_trabajo {$id} con fin='{$fin}', horas={$horasCalculadas}");
            
            $result = $db->query($sql, $params);
            
            // Verificar errores de la base de datos
            $error = $db->error();
            if (!empty($error) && !empty($error['code'])) {
                $errorMsg = $error['message'] ?? 'Error desconocido';
                $errorCode = $error['code'] ?? 0;
                log_message('error', "Error SQL al actualizar registro {$id}. Código: {$errorCode}, Mensaje: {$errorMsg}");
                log_message('error', "Valores intentados - fin: '{$fin}', horas: '{$horasCalculadas}', id: {$id}");
                return false;
            }
            
            // Verificar que se haya actualizado al menos una fila
            $affectedRows = $db->affectedRows();
            if ($affectedRows === 0) {
                log_message('warning', "No se actualizó ninguna fila para el registro {$id}. Puede que el registro no exista.");
                return false;
            }
            
            log_message('debug', "Tiempo de trabajo {$id} finalizado correctamente. Fin: {$fin}, Horas: {$horasCalculadas}, Filas afectadas: {$affectedRows}");
            return true;
        } catch (\Exception $e) {
            log_message('error', "Excepción al finalizar tiempo de trabajo {$id}: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            return false;
        } catch (\Throwable $e) {
            log_message('error', "Error fatal al finalizar tiempo de trabajo {$id}: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            return false;
        }
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
     * Verificar si ya existe un tiempo de trabajo finalizado para un empleado y orden de producción
     * @param int $empleadoId
     * @param int $ordenProduccionId
     * @return bool
     */
    public function tieneFinalizado(int $empleadoId, int $ordenProduccionId): bool
    {
        if ($empleadoId <= 0 || $ordenProduccionId <= 0) {
            return false;
        }

        try {
            // Obtener la conexión de base de datos
            $db = \Config\Database::connect();
            
            // Usar consulta SQL directa para verificar si hay un registro finalizado
            // Solo verificar si fin IS NOT NULL (evita comparar con cadena vacía que causa error DATETIME)
            $sql = "SELECT id FROM tiempo_trabajo 
                    WHERE empleadoId = ? AND ordenProduccionId = ? AND fin IS NOT NULL
                    LIMIT 1";
            $finalizado = $db->query($sql, [$empleadoId, $ordenProduccionId])->getRowArray();

            return !empty($finalizado);
        } catch (\Throwable $e) {
            // Si hay un error, retornar false para no bloquear el proceso
            log_message('error', 'Error en tieneFinalizado: ' . $e->getMessage());
            return false;
        }
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

        // Forzar una nueva conexión para evitar caché
        $this->db->reconnect();

        // Obtener todos los empleados asignados a esta OP con el puesto especificado
        $sql = "SELECT DISTINCT at.empleadoId, e.puesto, e.nombre, e.apellido
                FROM asignacion_tarea at
                INNER JOIN empleado e ON e.id = at.empleadoId
                WHERE at.ordenProduccionId = ? AND LOWER(TRIM(e.puesto)) = LOWER(TRIM(?))";
        
        $empleadosAsignados = $this->db->query($sql, [$ordenProduccionId, $puestoNormalizado])->getResultArray();
        
        log_message('debug', "todosHanFinalizado - OP: {$ordenProduccionId}, Puesto: '{$puestoNormalizado}', Empleados encontrados: " . count($empleadosAsignados));
        
        if (empty($empleadosAsignados)) {
            // Si no hay empleados asignados de ese tipo, considerar que todos han finalizado
            log_message('debug', "No hay empleados asignados con puesto '{$puestoNormalizado}' para OP {$ordenProduccionId}, retornando true");
            return true;
        }

        $empleadoIds = array_map(function($row) {
            return (int)$row['empleadoId'];
        }, $empleadosAsignados);

        // Verificar si todos tienen al menos un tiempo_trabajo finalizado para esta OP
        // y que no tengan ningún tiempo_trabajo activo (sin finalizar)
        foreach ($empleadoIds as $empId) {
            // Verificar si tiene algún registro activo (sin finalizar)
            // Solo verificar si fin IS NULL, no comparar con cadena vacía (causa error DATETIME)
            $sqlActivo = "SELECT id FROM tiempo_trabajo 
                         WHERE empleadoId = ? AND ordenProduccionId = ? AND fin IS NULL
                         LIMIT 1";
            $activo = $this->db->query($sqlActivo, [$empId, $ordenProduccionId])->getRowArray();
            if (!empty($activo)) {
                // Hay al menos un registro activo, no todos han finalizado
                log_message('debug', "Empleado {$empId} tiene registro activo (sin finalizar) para OP {$ordenProduccionId}");
                return false;
            }

            // Verificar si tiene al menos un registro finalizado
            // Solo verificar si fin IS NOT NULL (evita comparar con cadena vacía)
            $sqlFinalizado = "SELECT id, fin FROM tiempo_trabajo 
                             WHERE empleadoId = ? AND ordenProduccionId = ? AND fin IS NOT NULL
                             LIMIT 1";
            $finalizado = $this->db->query($sqlFinalizado, [$empId, $ordenProduccionId])->getRowArray();
            
            if (empty($finalizado)) {
                // Este empleado no tiene ningún registro finalizado, no todos han finalizado
                log_message('debug', "Empleado {$empId} NO tiene registro finalizado para OP {$ordenProduccionId}");
                return false;
            } else {
                log_message('debug', "Empleado {$empId} tiene registro finalizado para OP {$ordenProduccionId} (fin: " . ($finalizado['fin'] ?? 'N/A') . ")");
            }
        }

        // Todos tienen al menos un registro finalizado y ninguno tiene registros activos
        log_message('debug', "Todos los empleados con puesto '{$puestoNormalizado}' han finalizado para OP {$ordenProduccionId}");
        return true;
    }
}

