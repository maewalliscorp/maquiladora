<?php
namespace App\Models;

use CodeIgniter\Model;

class InventarioModel extends Model
{
    protected $table      = 'stock';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /* =========================
     * CONSULTAS BASE
     * ========================= */
    public function obtenerAlmacenesActivos(): array
    {
        return $this->db->table('almacen')
            ->select('id, codigo, nombre')
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->get()->getResultArray();
    }

    public function obtenerUbicacionesActivas(?int $almacenId = null): array
    {
        $b = $this->db->table('ubicacion')->select('id, codigo, almacenId')->where('activo', 1);
        if ($almacenId) $b->where('almacenId', $almacenId);
        return $b->orderBy('codigo','ASC')->get()->getResultArray();
    }

    public function obtenerInventario(?int $almacenId = null): array
    {
        $b = $this->db->table('stock s');
        $b->select("
            s.id                    AS stockId,
            a.id                    AS almacenId,
            a.codigo                AS almacenCodigo,
            a.nombre                AS almacenNombre,
            u.id                    AS ubicacionId,
            u.codigo                AS ubicacionCodigo,
            ar.id                   AS articuloId,
            ar.sku,
            ar.nombre               AS articuloNombre,
            ar.unidadMedida,
            ar.stockMin,
            ar.stockMax,
            IFNULL(l.id, 0)         AS loteId,
            l.codigo                AS loteCodigo,
            l.fechaFabricacion,
            l.fechaCaducidad,
            l.notas                 AS loteNotas,
            s.cantidad,
            DATEDIFF(l.fechaCaducidad, CURDATE()) AS diasCaduca,
            CASE
              WHEN l.fechaCaducidad IS NULL THEN 'sin_fecha'
              WHEN DATEDIFF(l.fechaCaducidad, CURDATE()) < 0 THEN 'caducado'
              WHEN DATEDIFF(l.fechaCaducidad, CURDATE()) <= 30 THEN 'por_caducar'
              ELSE 'ok'
            END AS estadoCaducidad
        ")
            ->join('ubicacion u', 'u.id = s.ubicacionId')
            ->join('almacen a',   'a.id = u.almacenId')
            ->join('articulo ar', 'ar.id = s.articuloId')
            ->join('lote l',      'l.id = s.loteId', 'left')
            ->where('a.activo', 1)->where('u.activo', 1)->where('ar.activo', 1);

        if ($almacenId) $b->where('a.id', $almacenId);

        return $b->orderBy('a.nombre, u.codigo, ar.nombre, l.fechaCaducidad', 'ASC')->get()->getResultArray();
    }

    public function obtenerMovimientos(int $articuloId, ?int $loteId = null, ?int $ubicacionId = null, int $limit = 50): array
    {
        $b = $this->db->table('movimiento_inventario mi')
            ->select('mi.id, mi.fecha, mi.tipo, mi.cantidad, mi.refTipo, mi.refId, mi.notas, mi.origenUbicacionId, mi.destinoUbicacionId')
            ->where('mi.articuloId', $articuloId);

        if ($loteId)      $b->where('mi.loteId', $loteId);
        if ($ubicacionId) $b->groupStart()->where('mi.origenUbicacionId', $ubicacionId)->orWhere('mi.destinoUbicacionId', $ubicacionId)->groupEnd();

        return $b->orderBy('mi.fecha', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function obtenerLotesArticulo(int $articuloId, ?int $almacenId = null, ?int $ubicacionId = null): array
    {
        $sb = $this->db->table('stock s')
            ->select('s.loteId, SUM(s.cantidad) AS cantidad, u.id AS ubicacionId, a.id AS almacenId')
            ->join('ubicacion u', 'u.id = s.ubicacionId', 'left')
            ->join('almacen a',   'a.id = u.almacenId',  'left')
            ->where('s.articuloId', $articuloId)
            ->groupBy('s.loteId, u.id, a.id');

        if ($almacenId)   $sb->where('a.id', $almacenId);
        if ($ubicacionId) $sb->where('u.id', $ubicacionId);

        $subSQL = $sb->getCompiledSelect();

        return $this->db->table('lote l')
            ->select("l.id AS loteId, l.codigo AS loteCodigo, l.fechaFabricacion, l.fechaCaducidad, DATEDIFF(l.fechaCaducidad, CURDATE()) AS diasCaduca, s2.cantidad")
            ->join("($subSQL) s2", 's2.loteId = l.id', ($almacenId || $ubicacionId) ? 'inner' : 'left')
            ->where('l.articuloId', $articuloId)
            ->orderBy('l.fechaCaducidad', 'ASC')
            ->get()->getResultArray();
    }

    /* =========================
     * SKU AUTOMÁTICO
     * ========================= */

    private function norm(string $txt): string {
        $txt = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
        $txt = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $txt);
        $txt = preg_replace('/\s+/', ' ', trim($txt));
        return strtoupper($txt);
    }
    private function sku_color(string $nombre): ?string {
        $n = ' ' . $this->norm($nombre) . ' ';
        $map = [
            ' NEGRO '=>'NG',' BLACK '=>'NG',
            ' BLANCO '=>'BL',' WHITE '=>'BL',
            ' AZUL '=>'AZ',' BLUE '=>'AZ',
            ' ROJO '=>'RJ',' RED '=>'RJ',
            ' VERDE '=>'VD',' GREEN '=>'VD',
            ' AMARILLO '=>'AM',' YELLOW '=>'AM',
            ' GRIS '=>'GR',' GRAY '=>'GR',
            ' BEIGE '=>'BG',' CAFE '=>'CF',' BROWN '=>'CF',
            ' MORADO '=>'MR',' PURPLE '=>'MR',
            ' ROSA '=>'RS',' PINK '=>'RS',
        ];
        foreach($map as $k=>$v){ if(strpos($n,$k)!==false) return $v; }
        return null;
    }
    private function sku_talla(string $nombre): ?string {
        $n = $this->norm($nombre);
        if(preg_match('/\b(2XL|3XL|4XL|5XL|XL|XS|S|M|L)\b/', $n, $m)) return $m[1];
        if(preg_match('/\b(\d{2})\b/', $n, $m)) return $m[1];
        return null;
    }
    private function sku_presentacion(string $nombre): ?string {
        $n = strtoupper($nombre);
        if(preg_match('/\b(\d+(?:\.\d+)?)\s*(ML|L)\b/i', $n, $m)) {
            $val=(float)$m[1]; $u=strtoupper($m[2]);
            return $u==='L' ? ((fmod($val,1.0)===0.0?(int)$val:str_replace('.','',$val)).'L')
                : ((fmod($val,1.0)===0.0?(int)$val:str_replace('.','',$val)).'ML');
        }
        if(preg_match('/\b(\d+(?:\.\d+)?)\s*(G|KG)\b/i', $n, $m)) {
            $val=(float)$m[1]; $u=strtoupper($m[2]);
            return $u==='KG'? ((fmod($val,1.0)===0.0?(int)$val:str_replace('.','',$val)).'KG')
                : ((fmod($val,1.0)===0.0?(int)$val:str_replace('.','',$val)).'G');
        }
        if(preg_match('/\b(\d+(?:\.\d+)?)\s*(M|CM|MM)\b/i', $n, $m)) {
            $val=(float)$m[1]; $u=strtoupper($m[2]);
            if($u==='CM') return (fmod($val,1.0)===0.0?(int)$val:str_replace('.','',$val)).'CM';
            if($u==='MM') return (fmod($val,1.0)===0.0?(int)$val:str_replace('.','',$val)).'MM';
            if(fmod($val,1.0)!==0.0) return (int)round($val*100).'CM';
            return ((int)$val).'M';
        }
        return null;
    }
    public function sku_prefijoDesdeNombre(string $nombre): string
    {
        $n = $this->norm($nombre);
        if ($n === '') return 'ART';
        $parts = explode(' ', $n);
        $p1 = substr($parts[0] ?? 'ART', 0, 3);
        $p2 = substr($parts[1] ?? '', 0, 2);
        $pref = preg_replace('/[^A-Z0-9]/', '', $p1 . $p2);
        if ($pref === '') $pref = 'ART';
        return substr($pref, 0, 5);
    }
    public function sku_generarUnicoAvanzado(string $nombre, ?string $unidadMedida = null, int $width = 4, string $sep='-'): string
    {
        $pref = $this->sku_prefijoDesdeNombre($nombre);
        $attrs = [];
        if ($c = $this->sku_color($nombre))        $attrs[] = $c;
        if ($t = $this->sku_talla($nombre))        $attrs[] = $t;
        if ($p = $this->sku_presentacion($nombre)) $attrs[] = $p;
        if ($unidadMedida) {
            $um = strtoupper(preg_replace('/\s+/', '', $unidadMedida));
            if (in_array($um, ['KG','G','MTS','M','CM','MM','PZ','PZA','LT','ML','L'])) $attrs[] = $um;
        }
        $base  = $pref . ($attrs ? $sep.implode($sep,$attrs):'');
        $like  = $base . $sep . '%';
        $start = strlen($base) + 2;
        $row = $this->db->query(
            'SELECT MAX(CAST(SUBSTRING(sku, ?) AS UNSIGNED)) AS n FROM articulo WHERE sku LIKE ?',
            [$start, $like]
        )->getRowArray();
        $n = (int)($row['n'] ?? 0) + 1;
        return $base . $sep . str_pad((string)$n, $width, '0', STR_PAD_LEFT);
    }

    /* =========================
     * HELPERS DE NEGOCIO
     * ========================= */

    /**
     * Resuelve o crea un artículo a partir de:
     * - id, sku o nombre (articuloTexto). Si no existe y $autoCrear=true lo crea.
     * Retorna ['ok'=>bool, 'articuloId'=>int, 'created'=>bool, 'message'=>?].
     */
    public function resolverOCrearArticulo(?int $articuloId, string $sku, string $articuloTexto, ?string $unidadMedida, $stockMin, $stockMax, bool $autoCrear=true): array
    {
        $db = $this->db;

        // 1) ID directo
        if ($articuloId) {
            $row = $db->table('articulo')->select('id')->where('id',$articuloId)->where('activo',1)->get()->getRowArray();
            if ($row) return ['ok'=>true,'articuloId'=>(int)$row['id'],'created'=>false];
            $articuloId = null; // invalido, seguimos
        }

        // 2) SKU exacto (sin espacios)
        if (!$articuloId && $sku !== '' && strpos($sku,' ') === false) {
            $row = $db->table('articulo')->select('id')->where('sku',$sku)->where('activo',1)->get()->getRowArray();
            if ($row) return ['ok'=>true,'articuloId'=>(int)$row['id'],'created'=>false];
        }

        // 3) Nombre exacto / sku=nombre
        if (!$articuloId && $articuloTexto !== '') {
            $row = $db->table('articulo')->select('id')
                ->groupStart()->where('nombre',$articuloTexto)->orWhere('sku',$articuloTexto)->groupEnd()
                ->where('activo',1)->get()->getRowArray();
            if ($row) return ['ok'=>true,'articuloId'=>(int)$row['id'],'created'=>false];
        }

        // 4) LIKE por tokens
        if (!$articuloId && $articuloTexto !== '') {
            $qb = $db->table('articulo')->select('id')->where('activo',1);
            $toks = preg_split('/\s+/', $articuloTexto, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if ($toks) {
                $qb->groupStart();
                foreach ($toks as $t) { if (mb_strlen($t)<2) continue; $qb->like('nombre', $t, 'both'); }
                $qb->groupEnd();
                $row = $qb->get()->getRowArray();
                if ($row) return ['ok'=>true,'articuloId'=>(int)$row['id'],'created'=>false];
            }
        }

        // 5) Crear
        if (!$articuloId && $autoCrear && $articuloTexto !== '') {
            $skuAuto = $this->sku_generarUnicoAvanzado($articuloTexto, $unidadMedida ?: null);
            $set = [
                'sku'          => $skuAuto,
                'nombre'       => $articuloTexto,
                'unidadMedida' => $unidadMedida ?: null,
                'stockMin'     => is_numeric($stockMin) ? (float)$stockMin : null,
                'stockMax'     => is_numeric($stockMax) ? (float)$stockMax : null,
                'activo'       => 1,
            ];
            try {
                $db->table('articulo')->insert($set);
            } catch (\Throwable $e) {
                if (stripos($e->getMessage(), 'Duplicate entry') !== false) {
                    // carrera: regenerar sku y reintentar
                    $set['sku'] = $this->sku_generarUnicoAvanzado($articuloTexto, $unidadMedida ?: null);
                    $db->table('articulo')->insert($set);
                } else {
                    return ['ok'=>false,'articuloId'=>0,'created'=>false,'message'=>'No se pudo crear el artículo'];
                }
            }
            return ['ok'=>true,'articuloId'=>(int)$db->insertID(),'created'=>true];
        }

        return ['ok'=>false,'articuloId'=>0,'created'=>false,'message'=>'Artículo no encontrado (id, sku o nombre)'];
    }

    /** Busca o crea lote. Si no hay datos, retorna null (NO crea lote vacío). */
    public function findOrCreateLote(int $articuloId, string $codigo=null, ?string $fechaFab=null, ?string $fechaCad=null, ?string $notas=null): ?int
    {
        $codigo = trim((string)$codigo);
        $tieneDatos = $codigo!=='' || $fechaFab || $fechaCad || $notas;
        if (!$tieneDatos) return null;

        if ($codigo!=='') {
            $row = $this->db->table('lote')->where(['articuloId'=>$articuloId,'codigo'=>$codigo])->get()->getRowArray();
            if ($row) {
                $upd = array_filter([
                    'fechaFabricacion'=>$fechaFab ?: null,
                    'fechaCaducidad'  =>$fechaCad ?: null,
                    'notas'           =>$notas ?: null,
                ], static fn($v)=>$v!==null);
                if ($upd) $this->db->table('lote')->update($upd, ['id'=>(int)$row['id']]);
                return (int)$row['id'];
            }
        }
        $this->db->table('lote')->insert([
            'articuloId'=>$articuloId,
            'codigo'    =>$codigo!=='' ? $codigo : null,
            'fechaFabricacion'=>$fechaFab ?: null,
            'fechaCaducidad'  =>$fechaCad ?: null,
            'notas'           =>$notas ?: null,
        ]);
        return (int)$this->db->insertID();
    }

    /**
     * Inserta o actualiza un registro de stock para (articuloId, ubicacionId, loteId)
     * $operacion: 'sumar' (default), 'restar' (no permite negativo), 'reemplazar' (setea).
     * Retorna ['ok'=>bool, 'stockId'=>int, 'cantidad'=>float, 'message'=>?].
     *
     * **FIX**: cuando $loteId es NULL, se usa `IS NULL` (no `= NULL`) para encontrar el existente.
     */
    public function upsertStock(int $articuloId, int $ubicacionId, ?int $loteId, float $cantidad, string $operacion='sumar'): array
    {
        $b = $this->db->table('stock')
            ->select('id, cantidad')
            ->where('articuloId', $articuloId)
            ->where('ubicacionId', $ubicacionId);

        if ($loteId === null) {
            // MUY IMPORTANTE: comparar por IS NULL para que encuentre el registro existente sin lote
            $b->where('loteId IS NULL', null, false);
        } else {
            $b->where('loteId', $loteId);
        }

        $row = $b->get()->getRowArray();

        if ($row) {
            $stockId = (int)$row['id'];
            $actual  = (float)$row['cantidad'];

            if ($operacion === 'reemplazar') {
                $nuevo = $cantidad;
            } elseif ($operacion === 'restar') {
                $nuevo = $actual - $cantidad;
                if ($nuevo < 0) return ['ok'=>false,'stockId'=>$stockId,'cantidad'=>$actual,'message'=>'La cantidad resultante no puede ser negativa'];
            } else { // sumar
                $nuevo = $actual + $cantidad;
            }

            $this->db->table('stock')->update(['cantidad'=>$nuevo], ['id'=>$stockId]);
            return ['ok'=>true,'stockId'=>$stockId,'cantidad'=>$nuevo];
        }

        // No existe registro: solo crear en SUMAR o REEMPLAZAR
        if ($operacion === 'restar') {
            return ['ok'=>false,'stockId'=>0,'cantidad'=>0,'message'=>'No existe stock para restar en esa ubicación/lote'];
        }

        $this->db->table('stock')->insert([
            'articuloId' => $articuloId,
            'ubicacionId'=> $ubicacionId,
            'loteId'     => $loteId, // puede ser NULL
            'cantidad'   => $cantidad
        ]);

        return ['ok'=>true,'stockId'=>(int)$this->db->insertID(),'cantidad'=>$cantidad];
    }
}
