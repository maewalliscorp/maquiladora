<?php namespace App\Models;

use CodeIgniter\Model;

class OrdenProduccionModel extends Model
{
    protected $table      = 'orden_produccion';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ordenCompraId','disenoVersionId','folio','cantidadPlan','fechaInicioPlan','fechaFinPlan','status'];
    public function getListado()
    {
        // SQL MySQL directo, acorde a tu esquema (snake_case)
        $sql = "SELECT\n                    op.id              AS opId,\n                    op.folio           AS op,\n                    op.fechaInicioPlan AS ini,\n                    op.fechaFinPlan    AS fin,\n                    op.status          AS estatus,\n                    d.nombre           AS diseno,\n                    c.nombre           AS cliente\n                FROM orden_produccion op\n                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId\n                LEFT JOIN diseno d          ON d.id  = dv.disenoId\n                LEFT JOIN orden_compra oc   ON oc.id = op.ordenCompraId\n                LEFT JOIN cliente c         ON c.id  = oc.clienteId\n                ORDER BY op.fechaInicioPlan DESC";
        $rows = $this->db->query($sql)->getResultArray();

        // Completa columnas que la vista espera
        foreach ($rows as &$r) {
            $r['cliente'] = $r['cliente'] ?? 'N/D';
        }
        return $rows;
    }
    /**
     * Actualiza el estatus de una orden de producciÃ³n.
     */
    public function updateEstatus(int $id, string $estatus): bool
    {
        if ($id <= 0 || $estatus === '') return false;
        return (bool)$this->update($id, ['status' => $estatus]);
    }

    /**
     */
    public function getDetalle(int $id): ?array
    {
        if ($id <= 0) return null;
        // SQL MySQL directo para detalle
        $sql = "SELECT
                    op.id,
                    op.ordenCompraId,
                    op.disenoVersionId,
                    op.folio,
                    op.cantidadPlan,
                    op.fechaInicioPlan,
                    op.fechaFinPlan,
                    op.status,
                    d.nombre   AS disenoNombre,
                    dv.version AS disenoVersion,
                    dv.fecha   AS disenoFecha,
                    dv.notas   AS disenoNotas,
                    dv.archivoCadUrl,   
                    dv.archivoPatronUrl,
                    dv.aprobado AS disenoAprobado
                FROM orden_produccion op
                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                LEFT JOIN diseno d          ON d.id  = dv.disenoId
                WHERE op.id = ?";
        $row = $this->db->query($sql, [$id])->getRowArray();

        if (!$row) return null;

        $fmt = function($v){ return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };
        return [
            'id'              => (int)$row['id'],
            'ordenCompraId'   => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio'           => $row['folio'] ?? '',
            'cantidadPlan'    => isset($row['cantidadPlan']) ? (int)$row['cantidadPlan'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan'    => $fmt($row['fechaFinPlan'] ?? null),
            'status'          => $row['status'] ?? '',
            'diseno'          => [
                'nombre'  => $row['disenoNombre'] ?? '',
                'version' => $row['disenoVersion'] ?? '',
                'fecha'   => $fmt($row['disenoFecha'] ?? null),
                'notas'   => $row['disenoNotas'] ?? '',
                'archivoCadUrl'    => $row['disenoArchivoCadUrl'] ?? '',
                'archivoPatronUrl' => $row['disenoArchivoPatronUrl'] ?? '',
                'aprobado'         => isset($row['disenoAprobado']) ? (int)$row['disenoAprobado'] : null,
            ],
        ];
    }

    /**
     * Detalle básico solo desde orden_produccion (sin joins), para poblar el modal inicialmente.
     */
    public function getDetalleBasico(int $id): ?array
    {
        if ($id <= 0) return null;
        $sql = "SELECT id, ordenCompraId, disenoVersionId, folio, cantidadPlan, fechaInicioPlan, fechaFinPlan, status
                FROM orden_produccion WHERE id = ?";
        $row = $this->db->query($sql, [$id])->getRowArray();
        if (!$row) return null;
        $fmt = function($v){ return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };
        return [
            'id'              => (int)$row['id'],
            'ordenCompraId'   => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio'           => $row['folio'] ?? '',
            'cantidadPlan'    => isset($row['cantidadPlan']) ? (int)$row['cantidadPlan'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan'    => $fmt($row['fechaFinPlan'] ?? null),
            'status'          => $row['status'] ?? '',
        ];
    }

    /**
     * Detalle básico por folio (sin joins)
     */
    public function getDetalleBasicoPorFolio(string $folio): ?array
    {
        $folio = trim($folio);
        if ($folio === '') return null;
        $sql = "SELECT id, ordenCompraId, disenoVersionId, folio, cantidadPlan, fechaInicioPlan, fechaFinPlan, status
                FROM orden_produccion WHERE folio = ?";
        $row = $this->db->query($sql, [$folio])->getRowArray();
        if (!$row) return null;
        $fmt = function($v){ return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };
        return [
            'id'              => (int)$row['id'],
            'ordenCompraId'   => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio'           => $row['folio'] ?? '',
            'cantidadPlan'    => isset($row['cantidadPlan']) ? (int)$row['cantidadPlan'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan'    => $fmt($row['fechaFinPlan'] ?? null),
            'status'          => $row['status'] ?? '',
        ];
    }

}