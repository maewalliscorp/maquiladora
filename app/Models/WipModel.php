<?php
namespace App\Models;

use CodeIgniter\Model;

class WipModel extends Model
{
    protected $returnType = 'array';
    protected $useTimestamps = false;

    /** Candidatas directas para WIP */
    private array $basesPreferidas = ['proceso_trabajo', 'orden_produccion_etapa', 'wip'];

    /** Catálogos posibles para nombre de etapa (ajusta si usas otro) */
    private array $catalogosEtapa = ['proceso', 'etapa_produccion', 'fase', 'actividad'];

    /** Tabla de empleados para responsable */
    private string $tEmpleado = 'empleado';

    /* ---------------- helpers ---------------- */

    private function showTables(): array
    {
        try {
            $rows = $this->db->query('SHOW TABLES')->getResultArray();
            // flatea a lista de strings:
            $out = [];
            foreach ($rows as $r) { $out[] = array_values($r)[0]; }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function tableExists(string $t): bool { return $this->db->tableExists($t); }

    private function getFields(string $t): array
    {
        try { return $this->db->getFieldNames($t); }
        catch (\Throwable $e) { return []; }
    }

    private function countRows(string $t): ?int
    {
        try { return (int)$this->db->table($t)->countAllResults(); }
        catch (\Throwable $e) { return null; }
    }

    private function pick(string $t, array $candidates): ?string
    {
        $f = $this->getFields($t);
        foreach ($candidates as $x) if (in_array($x, $f, true)) return $x;
        return null;
    }

    private function clamp(float|int $v): int { return (int)max(0, min(100, round((float)$v))); }

    private function pctFromDates(?string $ini, ?string $fin): int
    {
        if (!$ini || !$fin) return 0;
        $ti = strtotime($ini); $tf = strtotime($fin); $tn = time();
        if (!$ti || !$tf || $tf <= $ti) return 0;
        return $this->clamp((($tn - $ti) / ($tf - $ti)) * 100);
    }

    /** Encuentra la mejor tabla base disponible para WIP (por nombre y/o por filas) */
    private function pickBaseTable(): ?string
    {
        $all = $this->showTables();
        if (!$all) return null;

        // 1) Si existe alguna de las preferidas, úsala (prioridad por filas > 0)
        $candidatas = array_values(array_intersect($this->basesPreferidas, $all));
        if ($candidatas) {
            // ordena: con filas primero
            usort($candidatas, fn($a,$b) => ($this->countRows($b) ?? -1) <=> ($this->countRows($a) ?? -1));
            return $candidatas[0];
        }

        // 2) Busca por patrones del nombre
        $ordenadas = [];
        foreach ($all as $t) {
            $low = strtolower($t);
            if (str_contains($low, 'proceso') && (str_contains($low, 'trabajo') || str_contains($low,'etapa')))
                $ordenadas[] = $t;
            elseif (str_contains($low, 'orden') && str_contains($low, 'produccion') && (str_contains($low,'etapa') || str_contains($low,'fase')))
                $ordenadas[] = $t;
        }
        if ($ordenadas) {
            usort($ordenadas, fn($a,$b) => ($this->countRows($b) ?? -1) <=> ($this->countRows($a) ?? -1));
            return $ordenadas[0];
        }

        // 3) Último intento: alguna "orden_produccion" pura
        foreach ($all as $t) {
            $low = strtolower($t);
            if (str_contains($low,'orden') && str_contains($low,'produccion')) {
                return $t;
            }
        }

        return null;
    }

    /* ---------------- API pública ---------------- */

    /** Diagnóstico completo para ver qué hay */
    public function scan(): array
    {
        $tables = $this->showTables();
        $scan = [
            'tables' => $tables,
            'preferidas' => $this->basesPreferidas,
            'catalogosEtapa' => $this->catalogosEtapa,
            'empleado' => [
                'name'   => $this->tEmpleado,
                'exists' => $this->tableExists($this->tEmpleado),
                'fields' => $this->getFields($this->tEmpleado),
            ],
            'bases' => [],
        ];

        foreach ($this->basesPreferidas as $t) {
            $scan['bases'][$t] = [
                'exists' => $this->tableExists($t),
                'rows'   => $this->tableExists($t) ? $this->countRows($t) : null,
                'fields' => $this->tableExists($t) ? $this->getFields($t) : [],
            ];
        }

        $chosen = $this->pickBaseTable();
        $scan['chosen_base'] = $chosen;
        if ($chosen) {
            $scan['chosen_fields'] = $this->getFields($chosen);
            $scan['chosen_rows']   = $this->countRows($chosen);
        }

        $scan['preview'] = $this->getListado();
        return $scan;
    }

    /** Devuelve filas: id, etapa, responsable, inicio, fin, progreso */
    public function getListado(): array
    {
        $base = $this->pickBaseTable();
        if (!$base) return [];

        // campos candidatos MUY amplios (camelCase y snake_case)
        $idF   = $this->pick($base, ['id','idProcesoTrabajo','idEtapaOP','id_etapa_op','id_proceso_trabajo']);
        $iniF  = $this->pick($base, ['fechaInicio','fecha_inicio','inicio','ini','fecha_ini']);
        $finF  = $this->pick($base, ['fechaFin','fecha_fin','fin','termino','fecha_termino','fin_estimada','fecha_fin_estimada']);
        $pctF  = $this->pick($base, ['avance','progreso','porcentaje','porcentajeAvance','porcentaje_avance','avance_porcentaje']);
        $empF  = $this->pick($base, ['empleadoId','idEmpleado','empleado_id','id_empleado','responsableId','id_responsable','responsable_id','asignadoId','id_asignado']);
        $catId = $this->pick($base, ['procesoId','idProceso','proceso_id','id_proceso','etapaId','idEtapa','etapa_id','id_etapa','faseId','fase_id','actividadId','idActividad']);
        $etapaTextoF = $this->pick($base, ['etapa','nombreEtapa','nombre_etapa','fase','proceso','actividad','tarea','nombre_fase']);

        $b = $this->db->table("$base b");
        $selects = [];

        $selects[] = $idF  ? "b.$idF AS rowId"    : "NULL AS rowId";
        $selects[] = $iniF ? "b.$iniF AS inicio"  : "NULL AS inicio";
        $selects[] = $finF ? "b.$finF AS fin"     : "NULL AS fin";
        $selects[] = $pctF ? "b.$pctF AS progreso": "NULL AS progreso";

        // etapa desde texto en la base o desde catálogo
        if ($etapaTextoF) {
            $selects[] = "b.$etapaTextoF AS etapa";
        } else {
            $etapaOk = false;
            foreach ($this->catalogosEtapa as $cat) {
                if (!$this->tableExists($cat)) continue;
                $nom = $this->pick($cat, ['nombre','etapa','proceso','descripcion','nombre_etapa','nombreEtapa']);
                if ($nom && $catId) {
                    $selects[] = "$cat.$nom AS etapa";
                    $b->join("$cat", "$cat.id = b.$catId", 'left');
                    $etapaOk = true;
                    break;
                }
            }
            if (!$etapaOk) $selects[] = "'—' AS etapa";
        }

        // responsable
        if ($empF && $this->tableExists($this->tEmpleado)) {
            $nomEmp = $this->pick($this->tEmpleado, ['nombres','nombre','nombreCompleto','nombre_completo']);
            if ($nomEmp) {
                $selects[] = "e.$nomEmp AS responsable";
                $b->join($this->tEmpleado.' e', "e.id = b.$empF", 'left');
            } else {
                $selects[] = "'—' AS responsable";
            }
        } else {
            $selects[] = "'—' AS responsable";
        }

        $b->select(implode(', ', $selects), false)
            ->orderBy($idF ? "b.$idF" : 'rowId', 'ASC');

        $rows = $b->get()->getResultArray();

        foreach ($rows as &$r) {
            $r['id'] = $r['rowId'] ?? null;
            if ($r['progreso'] === null || $r['progreso'] === '') {
                $r['progreso'] = $this->pctFromDates($r['inicio'] ?? null, $r['fin'] ?? null);
            } else {
                $r['progreso'] = $this->clamp($r['progreso']);
            }
        }
        unset($r);

        return $rows;
    }

    /** Actualiza % de avance en la base elegida automáticamente */
    public function updateAvance(int $id, int $avance): bool
    {
        $base = $this->pickBaseTable();
        if (!$base) return false;

        $idF  = $this->pick($base, ['id','idProcesoTrabajo','idEtapaOP','id_etapa_op','id_proceso_trabajo']);
        $pctF = $this->pick($base, ['avance','progreso','porcentaje','porcentajeAvance','porcentaje_avance','avance_porcentaje']);
        if (!$idF || !$pctF) return false;

        return (bool)$this->db->table($base)->where($idF, $id)->update([$pctF => $avance]);
    }
}
