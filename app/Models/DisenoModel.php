<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DisenoModel
 * 
 * Acceso a datos para Diseños y sus Versiones.
 * Provee:
 * - Catálogo de diseños con su última versión y materiales.
 * - Detalle de un diseño (última versión + materiales).
 * 
 * La implementación contempla nombres alternos de tablas (con/ sin guiones bajos y mayúsculas)
 * para mayor tolerancia entre entornos.
 */
class DisenoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'diseno';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    /**
     * getCatalogoDisenos
     * 
     * Retorna el catálogo de diseños con la última versión detectada por subconsulta
     * y un arreglo de materiales legibles.
     * 
     * @return array Lista de diseños [{id, nombre, descripcion, version, materiales[]}, ...]
     */
    public function getCatalogoDisenos(): array
    {
        $db = $this->db;

        // Subconsulta: obtener la última versión por diseño (tabla diseno_version)
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
     * getDisenoDetalle
     * 
     * Obtiene detalle de un diseño: nombre, descripción y la última versión detectada,
     * incluyendo fecha, notas y archivos, además de los materiales asociados en formato legible.
     * 
     * @param int $id ID del diseño
     * @return array|null Estructura del diseño o null si no existe
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
                       d.codigo,
                       d.nombre,
                       d.descripcion,
                       dv.version,
                       dv.fecha,
                       dv.notas,
                       dv.archivoCadUrl,
                       dv.archivoPatronUrl,
                       dv.aprobado,
                       GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '||') AS materiales_concat
                FROM diseno d
                LEFT JOIN ($sub) dvsel ON dvsel.disenoId = d.id
                LEFT JOIN diseno_version dv ON dv.id = dvsel.id
                LEFT JOIN lista_materiales lm ON lm.disenoVersionId = dv.id
                WHERE d.id = ?
                GROUP BY d.id, d.codigo, d.nombre, d.descripcion, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl, dv.aprobado";

        try {
            $row = $db->query($sql, [$id])->getRowArray();
        } catch (\Throwable $e) {
            $sub2 = "SELECT dv1.disenoId, dv1.id FROM disenoversion dv1 LEFT JOIN disenoversion dv2 ON dv1.disenoId = dv2.disenoId AND ((dv1.fecha < dv2.fecha) OR (dv1.fecha = dv2.fecha AND dv1.id < dv2.id)) WHERE dv2.id IS NULL";
            $sql2 = "SELECT d.id, d.codigo, d.nombre, d.descripcion, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl, dv.aprobado,
                             GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '||') AS materiales_concat
                      FROM diseno d
                      LEFT JOIN ($sub2) dvsel ON dvsel.disenoId = d.id
                      LEFT JOIN disenoversion dv ON dv.id = dvsel.id
                      LEFT JOIN listamateriales lm ON lm.disenoVersionId = dv.id
                      WHERE d.id = ?
                      GROUP BY d.id, d.codigo, d.nombre, d.descripcion, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl, dv.aprobado";
            try {
                $row = $db->query($sql2, [$id])->getRowArray();
            } catch (\Throwable $e2) {
                return null;
            }
        }

        if (!$row) return null;

        // Expandir materiales en arreglo
        $row['materiales'] = [];
        if (!empty($row['materiales_concat'])) {
            foreach (explode('||', $row['materiales_concat']) as $m) {
                if ($m !== '') { $row['materiales'][] = $m; }
            }
        }
        unset($row['materiales_concat']);

        // Normalizar archivos múltiples (si existen variantes separadas por coma/pipe)
        $split = function ($val) {
            if (!$val) return [];
            if (is_array($val)) return $val;
            // admitir separadores ",", "|", ";"
            $parts = preg_split('/[|,;]+/u', (string)$val);
            $out = [];
            foreach ($parts as $p) { $p = trim($p); if ($p !== '') $out[] = $p; }
            return array_values(array_unique($out));
        };

        // Archivos CAD (array)
        $row['archivosCad'] = [];
        if (!empty($row['archivoCadUrl'])) {
            $row['archivosCad'] = $split($row['archivoCadUrl']);
        } elseif (!empty($row['archivoCadUrls'])) {
            $row['archivosCad'] = $split($row['archivoCadUrls']);
        }

        // Archivos Patrón (array)
        $row['archivosPatron'] = [];
        if (!empty($row['archivoPatronUrl'])) {
            $row['archivosPatron'] = $split($row['archivoPatronUrl']);
        } elseif (!empty($row['archivoPatronUrls'])) {
            $row['archivosPatron'] = $split($row['archivoPatronUrls']);
        }

        // Imágenes (array) si tuvieras columnas imagenUrl/imagenUrls
        $row['imagenes'] = [];
        if (!empty($row['imagenUrl'])) {
            $row['imagenes'] = $split($row['imagenUrl']);
        } elseif (!empty($row['imagenUrls'])) {
            $row['imagenes'] = $split($row['imagenUrls']);
        }

        return $row;
    }
}


