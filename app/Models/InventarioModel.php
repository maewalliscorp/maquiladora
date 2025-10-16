<?php
namespace App\Models;

use CodeIgniter\Model;

class InventarioModel extends Model
{
    protected $table      = 'stock';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /** Almacenes activos para el spinner */
    public function obtenerAlmacenesActivos(): array
    {
        return $this->db->table('almacen')
            ->select('id, codigo, nombre')
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->get()->getResultArray();
    }

    /** Inventario (puedes dejar los campos de lote aunque ya no se muestren en la tabla principal) */
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
            ->where('a.activo', 1)
            ->where('u.activo', 1)
            ->where('ar.activo', 1);

        if (!empty($almacenId)) $b->where('a.id', (int)$almacenId);

        return $b->orderBy('a.nombre, u.codigo, ar.nombre, l.fechaCaducidad', 'ASC')
            ->get()->getResultArray();
    }

    /** Movimientos (para el modal Ver) */
    public function obtenerMovimientos(int $articuloId, ?int $loteId = null, ?int $ubicacionId = null, int $limit = 50): array
    {
        $b = $this->db->table('movimiento_inventario mi')
            ->select('mi.id, mi.fecha, mi.tipo, mi.cantidad, mi.refTipo, mi.refId, mi.notas, mi.origenUbicacionId, mi.destinoUbicacionId')
            ->where('mi.articuloId', $articuloId);

        if (!empty($loteId))       $b->where('mi.loteId', $loteId);
        if (!empty($ubicacionId))  $b->groupStart()->where('mi.origenUbicacionId', $ubicacionId)->orWhere('mi.destinoUbicacionId', $ubicacionId)->groupEnd();

        return $b->orderBy('mi.fecha', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /** Ubicaciones activas (opcionalmente por almacén) */
    public function obtenerUbicacionesActivas(?int $almacenId = null): array
    {
        $b = $this->db->table('ubicacion')->select('id, codigo, almacenId')->where('activo', 1);
        if (!empty($almacenId)) $b->where('almacenId', $almacenId);
        return $b->orderBy('codigo','ASC')->get()->getResultArray();
    }

    /**
     * Lotes del artículo.
     * Devuelve lotes (aunque no tengan stock) y si se filtra por almacén/ubicación
     * solo mostrará aquellos lotes que tengan stock en esa relación.
     */
    public function obtenerLotesArticulo(int $articuloId, ?int $almacenId = null, ?int $ubicacionId = null): array
    {
        // Subconsulta con stock por lote/articulo/ubicacion/almacen
        $sb = $this->db->table('stock s')
            ->select('s.loteId, SUM(s.cantidad) AS cantidad, u.id AS ubicacionId, a.id AS almacenId')
            ->join('ubicacion u', 'u.id = s.ubicacionId', 'left')
            ->join('almacen a',   'a.id = u.almacenId',  'left')
            ->where('s.articuloId', $articuloId)
            ->groupBy('s.loteId, u.id, a.id');

        if (!empty($almacenId))   $sb->where('a.id', $almacenId);
        if (!empty($ubicacionId)) $sb->where('u.id', $ubicacionId);

        $subSQL = $sb->getCompiledSelect();

        // Lotes del artículo + info de stock (si aplica el filtro)
        $b = $this->db->table('lote l')
            ->select("
                l.id AS loteId,
                l.codigo AS loteCodigo,
                l.fechaFabricacion,
                l.fechaCaducidad,
                DATEDIFF(l.fechaCaducidad, CURDATE()) AS diasCaduca,
                s2.cantidad
            ")
            ->join("($subSQL) s2", 's2.loteId = l.id', empty($almacenId) && empty($ubicacionId) ? 'left' : 'inner')
            ->where('l.articuloId', $articuloId)
            ->orderBy('l.fechaCaducidad', 'ASC');

        return $b->get()->getResultArray();
    }
}
