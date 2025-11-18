<?php
// app/Models/MttoModel.php
namespace App\Models;

use CodeIgniter\Model;

class MttoModel extends Model
{
    protected $table         = 'mtto';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    // ⬅️ Añadimos: programacion_id, fecha_programada, observaciones, maquiladoraID
    protected $allowedFields = [
        'maquinaId', 'responsableId', 'tipo', 'estatus',
        'descripcion', 'fechaApertura', 'fechaCierre',
        'programacion_id', 'fecha_programada', 'observaciones',
        'maquiladoraID'
    ];

    /**
     * Listado con suma de horas registradas en mtto_detectado.
     * Incluye datos de máquina y fecha_programada para agenda/calendario.
     */
    public function getListado(?int $maquiladoraId = null): array
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
                'm.fecha_programada AS Programada',
                'm.programacion_id AS ProgramacionId',
                'COALESCE(SUM(d.tiempoHoras), 0) AS Horas',
                // Bandera de vencido (opcional, útil para pintar en UI)
                "(CASE WHEN m.estatus = 'pendiente' AND m.fecha_programada IS NOT NULL AND m.fecha_programada < CURDATE() THEN 1 ELSE 0 END) AS Vencido"
            ])
            ->join('maquina mx', 'mx.id = m.maquinaId', 'left')
            ->join('mtto_detectado d', 'd.otMttoId = m.id', 'left')
            ->groupBy([
                'm.id','m.fechaApertura','mx.codigo','m.maquinaId','m.responsableId',
                'm.tipo','m.estatus','m.descripcion','m.fechaCierre',
                'm.fecha_programada','m.programacion_id'
            ])
            ->orderBy('m.fechaApertura', 'DESC');

        if ($maquiladoraId !== null) {
            // Filtrar por maquiladoraID si existe la columna en mtto o en maquina
            $builder->groupStart()
                ->where('m.maquiladoraID', (int)$maquiladoraId)
                ->orWhere('mx.maquiladoraID', (int)$maquiladoraId)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Listado simple sin agregados, incluyendo la fecha programada.
     */
    public function getListadoSimple(?int $maquiladoraId = null): array
    {
        $builder = $this->db->table('mtto m')
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
                'm.fecha_programada AS Programada',
                'm.programacion_id AS ProgramacionId'
            ])
            ->join('maquina mx', 'mx.id = m.maquinaId', 'left')
            ->orderBy('m.fechaApertura', 'DESC');

        if ($maquiladoraId !== null) {
            $builder->groupStart()
                ->where('m.maquiladoraID', (int)$maquiladoraId)
                ->orWhere('mx.maquiladoraID', (int)$maquiladoraId)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Inserta detalle/parte-trabajo (evita registros vacíos).
     */
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
            'otMttoId'        => $mttoId,
            'accion'          => $accion ?: null,
            'repuestosUsados' => $repuestos ?: null,
            'tiempoHoras'     => $horas ?: 0,
        ]);
    }

    /* ===========================================================
     * Helpers OPCIONALES (útiles para calendario y alertas)
     * Si ya usas el query directo en el Controller, puedes omitirlos.
     * ===========================================================
     */

    /**
     * Eventos para FullCalendar (rango de fechas).
     * Devuelve arreglo listo para el widget: id, title, start, color.
     */
    public function getEventosCalendario(string $start, string $end, ?int $maquiladoraId = null): array
    {
        $builder = $this->db->table('mtto m')
            ->select('m.id, m.fecha_programada, m.estatus, mx.codigo AS maquinaCodigo, m.maquiladoraID, mx.maquiladoraID AS maquinaMaq')
            ->join('maquina mx', 'mx.id = m.maquinaId', 'left')
            ->where('m.fecha_programada >=', $start)
            ->where('m.fecha_programada <=', $end);

        if ($maquiladoraId !== null) {
            $builder->groupStart()
                ->where('m.maquiladoraID', (int)$maquiladoraId)
                ->orWhere('mx.maquiladoraID', (int)$maquiladoraId)
                ->groupEnd();
        }

        $rows = $builder->get()->getResultArray();

        return array_map(function($r){
            $color = ($r['estatus']==='hecho')
                ? '#198754'
                : (($r['estatus']==='vencido') ? '#dc3545' : '#ffc107');
            return [
                'id'    => (int)$r['id'],
                'title' => 'Rev. '.($r['maquinaCodigo'] ?? 'Máquina').' ('.$r['estatus'].')',
                'start' => $r['fecha_programada'],
                'color' => $color
            ];
        }, $rows);
    }

    /**
     * Pendientes próximos: devuelve OT programadas entre hoy y hoy+N días.
     */
    public function getPendientesProximos(int $dias = 7): array
    {
        return $this->where('estatus', 'pendiente')
            ->where('fecha_programada IS NOT NULL', null, false)
            ->where('fecha_programada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL '.$dias.' DAY)', null, false)
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();
    }

    /**
     * Marca una OT como completa.
     */
    public function marcarHecho(int $id): bool
    {
        return $this->update($id, [
            'estatus'     => 'hecho',
            'fechaCierre' => date('Y-m-d')
        ]);
    }

    /**
     * Reprograma una OT (y marca estatus reprogramado).
     */
    public function reprogramarFecha(int $id, string $nuevaFecha): bool
    {
        return $this->update($id, [
            'estatus'          => 'reprogramado',
            'fecha_programada' => $nuevaFecha
        ]);
    }
}
