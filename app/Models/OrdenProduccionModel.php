<?php namespace App\Models;

use CodeIgniter\Model;

class OrdenProduccionModel extends Model
{
    protected $table      = 'orden_produccion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getListado()
    {
        // ⚠️ SOLO columnas que EXISTEN en tu tabla
        $rows = $this->db->table($this->table . ' op')
            ->select("\n                op.id               AS opId,\n                op.folio            AS op,\n                op.fechaInicioPlan  AS ini,\n                op.fechaFinPlan     AS fin,\n                op.status           AS estatus\n            ")
            ->orderBy('op.fechaInicioPlan', 'DESC')
            ->get()->getResultArray();

        // Completa columnas que la vista espera
        foreach ($rows as &$r) {
            $r['cliente']      = $r['cliente']      ?? 'N/D';
            $r['responsable']  = $r['responsable']  ?? 'N/D';
        }
        return $rows;
    }
}
