<?php namespace App\Models;

use CodeIgniter\Model;

class OrdenProduccionModel extends Model
{
    protected $table      = 'orden_produccion';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ordenCompraId','disenoVersionId','folio','cantidadPlan','fechaInicioPlan','fechaFinPlan','status'];

    public function getListado($maquiladoraId = null)
    {
        // SQL MySQL directo, acorde a tu esquema (snake_case)
        $sql = "SELECT
                    op.id              AS opId,
                    op.folio           AS op,
                    op.fechaInicioPlan AS ini,
                    op.fechaFinPlan    AS fin,
                    op.status          AS estatus,
                    op.maquiladoraID,
                    op.maquiladoraCompartidaID,
                    d.nombre           AS diseno,
                    c.nombre           AS cliente
                FROM orden_produccion op
                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                LEFT JOIN diseno d          ON d.id  = dv.disenoId
                LEFT JOIN orden_compra oc   ON oc.id = op.ordenCompraId
                LEFT JOIN cliente c         ON c.id  = oc.clienteId";

        $params = [];
        if ($maquiladoraId) {
            // Filtrar por maquiladora en orden_produccion (y opcionalmente en orden_compra)
            $sql .= " WHERE (op.maquiladoraID = ? OR op.maquiladoraCompartidaID = ?)";
            $params[] = (int)$maquiladoraId;
            $params[] = (int)$maquiladoraId;
        }

        $sql .= " ORDER BY op.fechaInicioPlan DESC";

        $rows = $this->db->query($sql, $params)->getResultArray();

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
        // SQL MySQL directo para detalle completo
        $sql = "SELECT
                    op.id,
                    op.ordenCompraId,
                    op.disenoVersionId,
                    op.folio,
                    op.cantidadPlan,
                    op.fechaInicioPlan,
                    op.fechaFinPlan,
                    op.status,
                    d.codigo   AS disenoCodigo,
                    d.nombre   AS disenoNombre,
                    d.descripcion AS disenoDescripcion,
                    d.precio_unidad AS disenoPrecio,
                    dv.version AS disenoVersion,
                    dv.fecha   AS disenoFecha,
                    dv.notas   AS disenoNotas,
                    dv.aprobado AS disenoAprobado,
                    dv.foto     AS disenoFoto,
                    dv.patron   AS disenoPatron,
                    c.nombre    AS clienteNombre,
                    oc.total    AS pedidoTotal
                FROM orden_produccion op
                LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                LEFT JOIN diseno d          ON d.id  = dv.disenoId
                LEFT JOIN orden_compra oc   ON oc.id = op.ordenCompraId
                LEFT JOIN cliente c         ON c.id  = oc.clienteId
                WHERE op.id = ?";
        
        $row = $this->db->query($sql, [$id])->getRowArray();

        if (!$row) return null;

        $fmt = function($v){ return $v ? date('Y-m-d H:i:s', strtotime($v)) : ''; };
        
        // Convertir BLOBs a base64
        $fotoBase64 = null;
        if (!empty($row['disenoFoto'])) {
            $fotoBase64 = 'data:image/jpeg;base64,' . base64_encode($row['disenoFoto']);
        }
        
        $patronBase64 = null;
        if (!empty($row['disenoPatron'])) {
            $patronBase64 = 'data:image/jpeg;base64,' . base64_encode($row['disenoPatron']);
        }

        return [
            'id'              => (int)$row['id'],
            'ordenCompraId'   => $row['ordenCompraId'] ?? null,
            'disenoVersionId' => $row['disenoVersionId'] ?? null,
            'folio'           => $row['folio'] ?? '',
            'cantidadPlan'    => isset($row['cantidadPlan']) ? (int)$row['cantidadPlan'] : null,
            'fechaInicioPlan' => $fmt($row['fechaInicioPlan'] ?? null),
            'fechaFinPlan'    => $fmt($row['fechaFinPlan'] ?? null),
            'status'          => $row['status'] ?? '',
            'cliente'         => $row['clienteNombre'] ?? '',
            'total'           => $row['pedidoTotal'] ?? null,
            'diseno'          => [
                'codigo'      => $row['disenoCodigo'] ?? '',
                'nombre'      => $row['disenoNombre'] ?? '',
                'descripcion' => $row['disenoDescripcion'] ?? '',
                'precio_unidad' => $row['disenoPrecio'] ?? null,
                'version'     => $row['disenoVersion'] ?? '',
                'fecha'       => $fmt($row['disenoFecha'] ?? null),
                'notas'       => $row['disenoNotas'] ?? '',
                'aprobado'    => isset($row['disenoAprobado']) ? (int)$row['disenoAprobado'] : null,
                'archivoCadUrl' => $fotoBase64, // Usamos foto como archivoCadUrl para compatibilidad con frontend
                'archivoPatronUrl' => $patronBase64,
                'archivos'    => [] // Array vacío para compatibilidad
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