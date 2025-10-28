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

    public function getMuestrasConPrototipo()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
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
            ORDER BY m.id
        ");

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

    public function getMuestrasConDecision()
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
            WHERE a.decision IS NOT NULL
        ";

        $query = $db->query($sql);
        return $query->getResultArray();
    }
}
