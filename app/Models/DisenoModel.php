<?php

namespace App\Models;

use CodeIgniter\Model;

class DisenoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'diseno';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    public function getCatalogoDisenos(): array
    {
        $db = $this->db;

        // Subconsulta: última versión por diseño según tu esquema (diseno_version)
        $sub = "SELECT dv1.disenoId, dv1.id
                FROM diseno_version dv1
                LEFT JOIN diseno_version dv2
                  ON dv1.disenoId = dv2.disenoId
                 AND (
                      (dv1.fecha < dv2.fecha)
                   OR (dv1.fecha = dv2.fecha AND dv1.id < dv2.id)
                 )
                WHERE dv2.id IS NULL";

        $sql = "SELECT d.id,
                       d.nombre,
                       d.descripcion,
                       dv.version,
                       GROUP_CONCAT(
                         CONCAT(
                           COALESCE(lm.articuloId, 'Art'),
                           ' x ',
                           COALESCE(lm.cantidadPorUnidad, 0)
                         )
                         SEPARATOR '||'
                       ) AS materiales_concat
                FROM diseno d
                LEFT JOIN ($sub) dvsel ON dvsel.disenoId = d.id
                LEFT JOIN diseno_version dv ON dv.id = dvsel.id
                LEFT JOIN lista_materiales lm ON lm.disenoVersionId = dv.id
                GROUP BY d.id, d.nombre, d.descripcion, dv.version
                ORDER BY d.id";

        try {
            $result = $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            // Fallback: intenta con nombres sin guion bajo
            $sub2 = "SELECT dv1.disenoId, dv1.id
                     FROM disenoversion dv1
                     LEFT JOIN disenoversion dv2
                       ON dv1.disenoId = dv2.disenoId
                      AND (
                           (dv1.fecha < dv2.fecha)
                        OR (dv1.fecha = dv2.fecha AND dv1.id < dv2.id)
                      )
                     WHERE dv2.id IS NULL";

            $sql2 = "SELECT d.id,
                              d.nombre,
                              d.descripcion,
                              dv.version,
                              GROUP_CONCAT(CONCAT(COALESCE(lm.articulo_id, 'Art'),' x ',COALESCE(lm.cantidadPorUnidad, 0)) SEPARATOR '||') AS materiales_concat
                       FROM diseno d
                       LEFT JOIN ($sub2) dvsel ON dvsel.disenoId = d.id
                       LEFT JOIN disenoversion dv ON dv.id = dvsel.id
                       LEFT JOIN listamateriales lm ON lm.disenoVersionId = dv.id
                       GROUP BY d.id, d.nombre, d.descripcion, dv.version
                       ORDER BY d.id";
            try {
                $result = $db->query($sql2)->getResultArray();
            } catch (\Throwable $e2) {
                return [];
            }
        }

        // Transformar materiales_concat en arreglo de strings legibles
        foreach ($result as &$row) {
            $row['materiales'] = [];
            if (!empty($row['materiales_concat'])) {
                $parts = explode('||', (string)$row['materiales_concat']);
                foreach ($parts as $p) {
                    if ($p === '') { continue; }
                    $row['materiales'][] = $p;
                }
            }
            unset($row['materiales_concat']);
        }

        return $result;
    }

    /**
     * Obtiene detalle de un diseño: nombre, descripcion, version (última),
     * notas (como cortes), archivos y lista de materiales.
     */
    public function getDisenoDetalle(int $id): ?array
    {
        $db = $this->db;

        $sub = "SELECT dv1.disenoId, dv1.id
                FROM diseno_version dv1
                LEFT JOIN diseno_version dv2
                  ON dv1.disenoId = dv2.disenoId
                 AND (
                      (dv1.fecha < dv2.fecha)
                   OR (dv1.fecha = dv2.fecha AND dv1.id < dv2.id)
                 )
                WHERE dv2.id IS NULL";

        $sql = "SELECT d.id,
                       d.nombre,
                       d.descripcion,
                       dv.version,
                       dv.fecha,
                       dv.notas,
                       dv.archivoCadUrl,
                       dv.archivoPatronUrl,
                       GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '||') AS materiales_concat
                FROM diseno d
                LEFT JOIN ($sub) dvsel ON dvsel.disenoId = d.id
                LEFT JOIN diseno_version dv ON dv.id = dvsel.id
                LEFT JOIN lista_materiales lm ON lm.disenoVersionId = dv.id
                WHERE d.id = ?
                GROUP BY d.id, d.nombre, d.descripcion, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl";

        try {
            $row = $db->query($sql, [$id])->getRowArray();
        } catch (\Throwable $e) {
            $sub2 = "SELECT dv1.disenoId, dv1.id FROM disenoversion dv1 LEFT JOIN disenoversion dv2 ON dv1.disenoId = dv2.disenoId AND ((dv1.fecha < dv2.fecha) OR (dv1.fecha = dv2.fecha AND dv1.id < dv2.id)) WHERE dv2.id IS NULL";
            $sql2 = "SELECT d.id, d.nombre, d.descripcion, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl,
                             GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '||') AS materiales_concat
                      FROM diseno d
                      LEFT JOIN ($sub2) dvsel ON dvsel.disenoId = d.id
                      LEFT JOIN disenoversion dv ON dv.id = dvsel.id
                      LEFT JOIN listamateriales lm ON lm.disenoVersionId = dv.id
                      WHERE d.id = ?
                      GROUP BY d.id, d.nombre, d.descripcion, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl";
            try {
                $row = $db->query($sql2, [$id])->getRowArray();
            } catch (\Throwable $e2) {
                return null;
            }
        }

        if (!$row) return null;

        // Expandir materiales
        $row['materiales'] = [];
        if (!empty($row['materiales_concat'])) {
            foreach (explode('||', $row['materiales_concat']) as $m) {
                if ($m !== '') { $row['materiales'][] = $m; }
            }
        }
        unset($row['materiales_concat']);

        return $row;
    }
}


