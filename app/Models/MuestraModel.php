<?php

namespace App\Models;

use CodeIgniter\Model;

class MuestraModel extends Model
{
    protected $table = 'muestra';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'prototipoId',
        'solicitadaPor',
        'fechaSolicitud',
        'fechaEnvio',
        'estado',
        'observaciones'
    ];

    public function getMuestrasConPrototipo($maquiladoraId = null)
    {
        $db = \Config\Database::connect();

        $sql = "
            SELECT 
                m.id AS muestraId,
                p.codigo AS codigoPrototipo,
                m.solicitadaPor,
                m.fechaSolicitud,
                m.fechaEnvio,
                m.estado,
                m.observaciones
            FROM muestra m
            INNER JOIN prototipo p ON m.prototipoId = p.id
            LEFT JOIN diseno_version dv ON dv.id = p.disenoVersionId
            LEFT JOIN diseno d ON d.id = dv.disenoId
        ";

        $params = [];
        if ($maquiladoraId) {
            // Permitir que la maquiladora esté en diseno_version, diseno o muestra
            $sql .= "\n            WHERE (dv.maquiladoraID = ? OR d.maquiladoraID = ? OR m.maquiladoraID = ?)";
            $params[] = (int)$maquiladoraId;
            $params[] = (int)$maquiladoraId;
            $params[] = (int)$maquiladoraId;
        }

        $sql .= "
            ORDER BY m.id
        ";

        $query = $db->query($sql, $params);
        return $query->getResultArray();
    }

    public function getEvaluacionMuestra($muestraId)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
            m.id AS muestraId,
            m.estado,
            m.observaciones,
            a.id AS aprobacionId,
            c.nombre AS clienteNombre,
            a.fecha AS fechaAprobacion,
            a.decision,
            a.comentarios
        FROM muestra m
        LEFT JOIN aprobacion_muestra a ON m.id = a.muestraId
        LEFT JOIN cliente c ON a.clienteId = c.id
        WHERE m.id = ?
        ORDER BY a.fecha DESC
        LIMIT 1
    ", [$muestraId]);

        return $query->getRowArray();
    }

    public function getMuestrasConDecision($maquiladoraId = null)
    {
        $db = \Config\Database::connect();

        $sql = "
            SELECT 
                m.id,
                a.clienteId,
                c.nombre AS clienteNombre,
                m.prototipoId,
                a.fecha,
                m.solicitadaPor,
                m.fechaSolicitud,
                m.fechaEnvio,
                a.decision,
                m.estado,
                a.comentarios,
                m.observaciones
            FROM muestra m
            INNER JOIN aprobacion_muestra a ON m.id = a.muestraId
            LEFT JOIN cliente c ON a.clienteId = c.id
            LEFT JOIN prototipo p ON p.id = m.prototipoId
            LEFT JOIN diseno_version dv ON dv.id = p.disenoVersionId
            LEFT JOIN diseno d ON d.id = dv.disenoId
            WHERE a.decision IS NOT NULL
        ";

        $params = [];
        if ($maquiladoraId) {
            $sql .= "\n              AND (dv.maquiladoraID = ? OR d.maquiladoraID = ? OR m.maquiladoraID = ?)";
            $params[] = (int)$maquiladoraId;
            $params[] = (int)$maquiladoraId;
            $params[] = (int)$maquiladoraId;
        }

        $query = $db->query($sql, $params);
        return $query->getResultArray();
    }
}
