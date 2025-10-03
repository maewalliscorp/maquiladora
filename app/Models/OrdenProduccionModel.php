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
            ->select("
                op.folio            AS op,
                op.fechaInicioPlan  AS ini,
                op.fechaFinPlan     AS fin,
                op.status           AS estatus
            ")
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
