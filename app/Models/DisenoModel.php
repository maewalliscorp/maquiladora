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
                           d.codigo,
                           d.nombre,
                           d.descripcion,
                           d.precio_unidad,
                           dv.version,
                           dv.fecha,
                           dv.aprobado,
                           GROUP_CONCAT(
                             CONCAT(
                               COALESCE(a.nombre, CONCAT('Art ', lm.articuloId)),
                               ' x ',
                               COALESCE(lm.cantidadPorUnidad, 0)
                             )
                             SEPARATOR '||'
                           ) AS materiales_concat
                    FROM diseno d
                    LEFT JOIN ($sub) dvsel ON dvsel.disenoId = d.id
                    LEFT JOIN diseno_version dv ON dv.id = dvsel.id
                    LEFT JOIN lista_materiales lm ON lm.disenoVersionId = dv.id
                    LEFT JOIN articulo a ON a.id = lm.articuloId
                    GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.version, dv.fecha, dv.aprobado
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
                                  d.codigo,
                                  d.nombre,
                                  d.descripcion,
                                  d.precio_unidad,
                                  dv.version,
                                  dv.fecha,
                                  dv.aprobado,
                                  GROUP_CONCAT(CONCAT(COALESCE(a.nombre, CONCAT('Art ', lm.articuloId)),' x ',COALESCE(lm.cantidadPorUnidad, 0)) SEPARATOR '||') AS materiales_concat
                           FROM diseno d
                           LEFT JOIN ($sub2) dvsel ON dvsel.disenoId = d.id
                           LEFT JOIN disenoversion dv ON dv.id = dvsel.id
                           LEFT JOIN listamateriales lm ON lm.disenoVersionId = dv.id
                           LEFT JOIN Articulo a ON a.id = lm.articuloId
                           GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.version, dv.fecha, dv.aprobado
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
         * getCatalogoDisenosTodasVersiones
         * 
         * Retorna el catálogo con TODAS las versiones de cada diseño.
         * Cada fila representa una versión distinta con sus materiales.
         * Mantiene el mismo esquema esperado por la vista `catalogodisenos.php`:
         *   id (del diseño), nombre, descripcion, version, materiales[]
         * 
         * @return array
         */
        public function getCatalogoDisenosTodasVersiones(): array
        {
            $db = $this->db;

            // Consulta base: una fila por versión
            $sql = "SELECT d.id,
                           d.codigo,
                           d.nombre,
                           d.descripcion,
                           d.precio_unidad,
                           dv.id   AS disenoVersionId,
                           dv.version,
                           dv.fecha,
                           dv.aprobado,
                           GROUP_CONCAT(
                             CONCAT(
                               COALESCE(a.nombre, CONCAT('Art ', lm.articuloId)),
                               ' x ',
                               COALESCE(lm.cantidadPorUnidad, 0)
                             )
                             SEPARATOR '||'
                           ) AS materiales_concat
                    FROM diseno d
                    LEFT JOIN diseno_version dv ON dv.disenoId = d.id
                    LEFT JOIN lista_materiales lm ON lm.disenoVersionId = dv.id
                    LEFT JOIN articulo a ON a.id = lm.articuloId
                    WHERE dv.id IS NOT NULL
                    GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.id, dv.version, dv.fecha, dv.aprobado
                    ORDER BY d.id, dv.fecha DESC, dv.id DESC";

            try {
                $result = $db->query($sql)->getResultArray();
            } catch (\Throwable $e) {
                // Fallback sin guiones bajos / variantes de nombres
                $sql2 = "SELECT d.id,
                                 d.codigo,
                                 d.nombre,
                                 d.descripcion,
                                 d.precio_unidad,
                                 dv.id   AS disenoVersionId,
                                 dv.version,
                                 dv.fecha,
                                 dv.aprobado,
                                 GROUP_CONCAT(CONCAT(COALESCE(a.nombre, CONCAT('Art ', lm.articuloId)),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '||') AS materiales_concat
                          FROM diseno d
                          LEFT JOIN disenoversion dv ON dv.disenoId = d.id
                          LEFT JOIN listamateriales lm ON lm.disenoVersionId = dv.id
                          LEFT JOIN Articulo a ON a.id = lm.articuloId
                          WHERE dv.id IS NOT NULL
                          GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.id, dv.version, dv.fecha, dv.aprobado
                          ORDER BY d.id, dv.fecha DESC, dv.id DESC";
                try {
                    $result = $db->query($sql2)->getResultArray();
                } catch (\Throwable $e2) {
                    return [];
                }
            }

            // Normalizar materiales a arreglo de strings legibles
            foreach ($result as &$row) {
                $row['materiales'] = [];
                if (!empty($row['materiales_concat'])) {
                    $parts = explode('||', (string)$row['materiales_concat']);
                    foreach ($parts as $p) { if ($p !== '') { $row['materiales'][] = $p; } }
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
                           d.precio_unidad,
                           d.clienteId,
                           d.idSexoFK,
                           d.IdTallasFK,
                           d.idTipoCorteFK,
                           d.idTipoRopaFK,
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
                    GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, d.clienteId, d.idSexoFK, d.IdTallasFK, d.idTipoCorteFK, d.idTipoRopaFK, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl, dv.aprobado";
    
            try {
                $row = $db->query($sql, [$id])->getRowArray();
            } catch (\Throwable $e) {
                $sub2 = "SELECT dv1.disenoId, dv1.id FROM disenoversion dv1 LEFT JOIN disenoversion dv2 ON dv1.disenoId = dv2.disenoId AND ((dv1.fecha < dv2.fecha) OR (dv1.fecha = dv2.fecha AND dv1.id < dv2.id)) WHERE dv2.id IS NULL";
                $sql2 = "SELECT d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, d.clienteId, d.idSexoFK, d.IdTallasFK, d.idTipoCorteFK, d.idTipoRopaFK, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl, dv.aprobado,
                                 GROUP_CONCAT(CONCAT(COALESCE(lm.articuloId,'Art'),' x ',COALESCE(lm.cantidadPorUnidad,0)) SEPARATOR '||') AS materiales_concat
                          FROM diseno d
                          LEFT JOIN ($sub2) dvsel ON dvsel.disenoId = d.id
                          LEFT JOIN disenoversion dv ON dv.id = dvsel.id
                          LEFT JOIN listamateriales lm ON lm.disenoVersionId = dv.id
                          WHERE d.id = ?
                          GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, d.clienteId, d.idSexoFK, d.IdTallasFK, d.idTipoCorteFK, d.idTipoRopaFK, dv.version, dv.fecha, dv.notas, dv.archivoCadUrl, dv.archivoPatronUrl, dv.aprobado";
                try {
                    $row = $db->query($sql2, [$id])->getRowArray();
                } catch (\Throwable $e2) {
                    return null;
                }
            }
    
            if (!$row) return null;
    
            // Expandir materiales: intento 1 (concatenado básico)
            $row['materiales'] = [];
            if (!empty($row['materiales_concat'])) {
                foreach (explode('||', $row['materiales_concat']) as $m) {
                    if ($m !== '') {
                        // Parsear "ArtId x Cant" a objeto
                        $parts = preg_split('/\s+x\s+/i', (string)$m);
                        $nombre = trim((string)($parts[0] ?? ''));
                        $cant = trim((string)($parts[1] ?? ''));
                        $obj = [
                            'nombre' => $nombre ?: 'Material',
                            'cantidad' => $cant !== '' ? $cant : null,
                            'merma' => null,
                        ];
                        $row['materiales'][] = $obj;
                    }
                }
            }
            unset($row['materiales_concat']);

            // Expandir materiales: intento 2 (consulta detallada con JOIN si es posible)
            try {
                // Detectar tabla de LM y columnas
                $lmTables = ['lista_materiales','listamateriales','ListaMateriales'];
                $dvId = null;
                // Necesitamos el id de la última versión utilizada en el SELECT anterior
                // Si no está explícito, intentamos recuperarlo mediante subconsulta del mismo patrón
                $dvId = $db->query(
                    "SELECT dv.id FROM diseno_version dv
                     WHERE dv.disenoId = ?
                     ORDER BY dv.fecha DESC, dv.id DESC
                     LIMIT 1",
                    [$row['id']]
                )->getRow('id');

                if (!$dvId) {
                    // Fallback a variantes de tabla
                    $dvId = $db->query(
                        "SELECT dv.id FROM disenoversion dv
                         WHERE dv.disenoId = ?
                         ORDER BY dv.fecha DESC, dv.id DESC
                         LIMIT 1",
                        [$row['id']]
                    )->getRow('id');
                }

                if ($dvId) {
                    $detallados = [];
                    foreach ($lmTables as $t) {
                        try {
                            // Intento unir con tabla de artículos para obtener nombre si existe
                            $joinSqls = [
                                // articulo (snake)
                                "SELECT lm.articuloId, lm.cantidadPorUnidad, lm.mermaPct, a.nombre AS artNombre
                                   FROM $t lm
                                   LEFT JOIN articulo a ON a.id = lm.articuloId
                                  WHERE lm.disenoVersionId = ?",
                                // Articulo (Camel)
                                "SELECT lm.articuloId, lm.cantidadPorUnidad, lm.mermaPct, a.nombre AS artNombre
                                   FROM $t lm
                                   LEFT JOIN Articulo a ON a.id = lm.articuloId
                                  WHERE lm.disenoVersionId = ?",
                                // producto como alternativa
                                "SELECT lm.articuloId, lm.cantidadPorUnidad, lm.mermaPct, p.nombre AS artNombre
                                   FROM $t lm
                                   LEFT JOIN producto p ON p.id = lm.articuloId
                                  WHERE lm.disenoVersionId = ?",
                            ];
                            $rowsLM = [];
                            foreach ($joinSqls as $js) {
                                try {
                                    $rowsLM = $db->query($js, [$dvId])->getResultArray();
                                    if ($rowsLM) break;
                                } catch (\Throwable $e) { /* intentar siguiente */ }
                            }
                            if (!$rowsLM) {
                                // Sin JOIN posible, leer crudo
                                $rowsLM = $db->query("SELECT articuloId, cantidadPorUnidad, mermaPct FROM $t WHERE disenoVersionId = ?", [$dvId])->getResultArray();
                            }
                            if ($rowsLM) {
                                foreach ($rowsLM as $rLM) {
                                    $nombre = $rLM['artNombre'] ?? null;
                                    if (!$nombre && isset($rLM['articuloId'])) {
                                        $nombre = 'Art ' . $rLM['articuloId'];
                                    }
                                    $detallados[] = [
                                        'articuloId'        => isset($rLM['articuloId']) ? (int)$rLM['articuloId'] : null,
                                        'nombre'            => $nombre ?: 'Material',
                                        'cantidadPorUnidad' => $rLM['cantidadPorUnidad'] ?? null,
                                        'mermaPct'          => $rLM['mermaPct'] ?? null,
                                    ];
                                }
                                break; // ya lo logramos con esta tabla
                            }
                        } catch (\Throwable $e) { /* probar siguiente nombre de tabla */ }
                    }

                    if ($detallados) {
                        // Mantener 'materiales' legible y además devolver materialesDet con IDs
                        $row['materiales'] = array_map(function($r){
                            return [
                                'nombre'   => $r['nombre'],
                                'cantidad' => $r['cantidadPorUnidad'],
                                'merma'    => $r['mermaPct'],
                            ];
                        }, $detallados);
                        $row['materialesDet'] = $detallados;
                    }
                }
            } catch (\Throwable $e) {
                // Ignorar errores; al menos dejamos la lista básica si existe
            }
    
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
    
    
