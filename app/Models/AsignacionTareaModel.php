<?php namespace App\Models;

use CodeIgniter\Model;

class AsignacionTareaModel extends Model
{
    protected $table      = 'asignacion_tarea';
    protected $primaryKey = 'id';
    protected $allowedFields = ['empleadoId','ordenProduccionId','rutaOperacionId','asignadoDesde','asignadoHasta'];

    public function listarPorOP(int $opId): array
    {
        if ($opId <= 0) return [];
        $sql = "SELECT at.id,
                       at.empleadoId,
                       at.ordenProduccionId,
                       at.rutaOperacionId,
                       at.asignadoDesde,
                       at.asignadoHasta,
                       e.noEmpleado,
                       e.nombre,
                       e.apellido,
                       e.puesto
                FROM asignacion_tarea at
                LEFT JOIN empleado e ON e.id = at.empleadoId
                WHERE at.ordenProduccionId = ?
                ORDER BY at.asignadoDesde IS NULL, at.asignadoDesde ASC, at.id DESC";
        return $this->db->query($sql, [$opId])->getResultArray();
    }

    public function listarPorEmpleado(int $empleadoId): array
    {
        if ($empleadoId <= 0) return [];
        // Forzar nueva conexión para evitar caché
        $db = \Config\Database::connect();
        $sql = "SELECT at.id,
                       at.ordenProduccionId AS opId,
                       at.rutaOperacionId,
                       at.asignadoDesde,
                       at.asignadoHasta,
                       op.folio,
                       op.status
                FROM asignacion_tarea at
                JOIN orden_produccion op ON op.id = at.ordenProduccionId
                WHERE at.empleadoId = ?
                ORDER BY at.asignadoDesde IS NULL, at.asignadoDesde ASC, at.id DESC";
        // Usar query directo para evitar caché del modelo
        return $db->query($sql, [$empleadoId])->getResultArray();
    }

    public function agregar(int $opId, int $empleadoId, ?string $desde = null, ?string $hasta = null, ?int $rutaOperacionId = null): bool
    {
        if ($opId <= 0 || $empleadoId <= 0) return false;
        // Evitar duplicado mismo empleado en misma OP
        if ($this->existeAsignacion($opId, $empleadoId)) return false;
        $data = [
            'ordenProduccionId' => $opId,
            'empleadoId'        => $empleadoId,
            'asignadoDesde'     => $desde,
            'asignadoHasta'     => $hasta,
            'rutaOperacionId'   => $rutaOperacionId,
        ];
        return (bool)$this->insert($data);
    }

    public function eliminar(int $asignacionId): bool
    {
        if ($asignacionId <= 0) return false;
        return (bool)$this->delete($asignacionId);
    }

    public function existeAsignacion(int $opId, int $empleadoId): bool
    {
        if ($opId <= 0 || $empleadoId <= 0) return false;
        $row = $this->db->query(
            'SELECT id FROM asignacion_tarea WHERE ordenProduccionId = ? AND empleadoId = ? LIMIT 1',
            [$opId, $empleadoId]
        )->getRowArray();
        return !empty($row);
    }
}
