<?php
// app/Models/MttoModel.php
// app/Models/MttoModel.php
namespace App\Models;

use CodeIgniter\Model;

class MttoModel extends Model
{
    protected $table         = 'mtto';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'maquinaId', 'responsableId', 'tipo', 'estatus',
        'descripcion', 'fechaApertura', 'fechaCierre'
    ];

    public function getListado(): array
    {
        $db = $this->db;

        $builder = $db->table('mtto m')
            ->select([
                'm.id AS Folio',
                'm.fechaApertura AS Apertura',
                'COALESCE(mx.codigo, m.maquinaId) AS Maquina',
                'm.maquinaId AS MaquinaId',
                'm.responsableId AS ResponsableId',
                'm.tipo AS Tipo',
                'm.estatus AS Estatus',
                'm.descripcion AS Descripcion',
                'm.fechaCierre AS Cierre',
                'COALESCE(SUM(d.tiempoHoras), 0) AS Horas',
            ])
            ->join('maquina mx', 'mx.id = m.maquinaId', 'left')
            ->join('mtto_detectado d', 'd.otMttoId = m.id', 'left')
            ->groupBy([
                'm.id','m.fechaApertura','mx.codigo','m.maquinaId','m.responsableId',
                'm.tipo','m.estatus','m.descripcion','m.fechaCierre'
            ])
            ->orderBy('m.fechaApertura', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getListadoSimple(): array
    {
        return $this->db->table('mtto m')
            ->select([
                'm.id AS Folio',
                'm.fechaApertura AS Apertura',
                'COALESCE(mx.codigo, m.maquinaId) AS Maquina',
                'm.maquinaId AS MaquinaId',
                'm.responsableId AS ResponsableId',
                'm.tipo AS Tipo',
                'm.estatus AS Estatus',
                'm.descripcion AS Descripcion',
                'm.fechaCierre AS Cierre',
            ])
            ->join('maquina mx', 'mx.id = m.maquinaId', 'left')
            ->orderBy('m.fechaApertura', 'DESC')
            ->get()->getResultArray();
    }

    public function insertDetalle(int $mttoId, ?string $accion, ?string $repuestos, ?float $horas): bool
    {
        if (!$mttoId) return false;
        $accion    = trim((string)$accion);
        $repuestos = trim((string)$repuestos);
        $horas     = $horas !== null ? (float)$horas : null;

        if ($accion === '' && $repuestos === '' && ($horas === null || $horas <= 0)) {
            return false;
        }
        $det = new \App\Models\MttoDetectadoModel();
        return (bool) $det->insert([
            'otMttoId'       => $mttoId,
            'accion'         => $accion ?: null,
            'repuestosUsados'=> $repuestos ?: null,
            'tiempoHoras'    => $horas ?: 0,
        ]);
    }
}


