<?php namespace App\Models;

use CodeIgniter\Model;

class OrdenProduccionModel extends Model
{
    protected $table      = 'orden_produccion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getListado()
    {
        $rows = $this->db->table($this->table . ' op')
            ->select("
                op.id               AS opId,
                op.folio            AS op,
                op.fechaInicioPlan  AS ini,
                op.fechaFinPlan     AS fin,
                op.status           AS estatus
            ")
            ->orderBy('op.fechaInicioPlan', 'DESC')
            ->get()->getResultArray();

        foreach ($rows as &$r) {
            $r['cliente']     = $r['cliente']     ?? 'N/D';
            $r['responsable'] = $r['responsable'] ?? 'N/D';
        }
        return $rows;
    }

    /**
     * (Opcional) Para MRP: devuelve OP activas con campos mínimos
     * No interfiere con getListado().
     */
    public function getActivasParaMrp(array $estados = ['planeada','liberada','en_proceso']): array
    {
        // si tus estados tienen mayúsculas/acentos, ajusta el array tal cual están en BD
        return $this->select('id, disenoVersionId, cantidadPlan, fechaInicioPlan, fechaFinPlan, status')
            ->whereIn('status', $estados)
            ->orderBy('fechaInicioPlan', 'ASC')
            ->findAll();
    }
}
