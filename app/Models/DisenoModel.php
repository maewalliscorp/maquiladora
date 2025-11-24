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
        public function getCatalogoDisenos($maquiladoraId = null): array
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
                    LEFT JOIN articulo a ON a.id = lm.articuloId";
            
            
            // Agregar filtro por maquiladora si está disponible
            $whereClause = '';
            if ($maquiladoraId) {
                // Verificar qué campo existe en la tabla diseno
                try {
                    $fields = $db->getFieldNames('diseno');
                    $fieldToUse = null;
                    
                    if (in_array('maquiladoraID', $fields)) {
                        $fieldToUse = 'maquiladoraID';
                    } elseif (in_array('maquiladoraIdFK', $fields)) {
                        $fieldToUse = 'maquiladoraIdFK';
                    } elseif (in_array('maquiladora_id', $fields)) {
                        $fieldToUse = 'maquiladora_id';
                    }
                    
                    if ($fieldToUse) {
                        // Filtrar SOLO por diseños de esta maquiladora (sin incluir NULL/compartidos)
                        $whereClause = " WHERE d.{$fieldToUse} = " . (int)$maquiladoraId;
                    }
                } catch (\Throwable $e) {
                    // Si hay error al verificar campos, no filtrar
                }
            }
            
            $sql .= $whereClause;
            
            $sql .= " GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.version, dv.fecha, dv.aprobado
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
        public function getCatalogoDisenosTodasVersiones($maquiladoraId = null): array
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
                    WHERE dv.id IS NOT NULL";

            // Filtro por maquiladora si viene desde sesión
            if ($maquiladoraId) {
                $sql .= " AND d.maquiladoraID = " . (int)$maquiladoraId;
            }

            $sql .= " GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.id, dv.version, dv.fecha, dv.aprobado
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
                          WHERE dv.id IS NOT NULL";

                if ($maquiladoraId) {
                    $sql2 .= " AND d.maquiladoraID = " . (int)$maquiladoraId;
                }

                $sql2 .= " GROUP BY d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, dv.id, dv.version, dv.fecha, dv.aprobado
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

        // Consulta completa con JOIN para obtener diseño y su versión
        $sql = "SELECT d.id,
                       d.codigo,
                       d.nombre,
                       d.descripcion,
                       d.precio_unidad,
                       d.clienteId,
                       d.idSexoFK,
                       d.idTallasFK,
                       d.idTipoCorteFK,
                       d.idTipoRopaFK,
                       dv.id AS versionId,
                       dv.version,
                       dv.fecha,
                       dv.notas,
                       dv.foto,
                       dv.patron,
                       dv.aprobado
                FROM diseno d
                LEFT JOIN diseno_version dv ON dv.disenoId = d.id
                WHERE d.id = ?";
        
        $row = $db->query($sql, [$id])->getRowArray();
        
        if (!$row) return null;

        // Obtener materiales usando el ID de la versión
        $row['materiales'] = [];
        $row['materialesDet'] = [];
        $dvId = $row['versionId'] ?? null;

        if ($dvId) {
            // Intentar obtener materiales con nombres de artículos
            $lmTables = ['lista_materiales', 'listamateriales', 'ListaMateriales'];
            $rowsLM = [];

            foreach ($lmTables as $t) {
                try {
                    $sqlLM = "SELECT lm.articuloId, lm.cantidadPorUnidad, lm.mermaPct, a.nombre AS artNombre, a.unidadMedida
                              FROM $t lm
                              LEFT JOIN articulo a ON a.id = lm.articuloId
                              WHERE lm.disenoVersionId = ?";
                    $rowsLM = $db->query($sqlLM, [$dvId])->getResultArray();
                    if ($rowsLM) break;
                } catch (\Throwable $e) {
                    try {
                        $rowsLM = $db->query("SELECT articuloId, cantidadPorUnidad, mermaPct FROM $t WHERE disenoVersionId = ?", [$dvId])->getResultArray();
                        if ($rowsLM) break;
                    } catch (\Throwable $e2) {}
                }
            }

            if ($rowsLM) {
                foreach ($rowsLM as $r) {
                    $nombre = $r['artNombre'] ?? ('Art ' . $r['articuloId']);
                    $det = [
                        'articuloId'        => (int)$r['articuloId'],
                        'nombre'            => $nombre,
                        'cantidadPorUnidad' => $r['cantidadPorUnidad'],
                        'mermaPct'          => $r['mermaPct'],
                        'unidadMedida'      => $r['unidadMedida'] ?? null
                    ];
                    $row['materialesDet'][] = $det;
                    
                    $row['materiales'][] = [
                        'nombre'   => $nombre,
                        'cantidad' => $r['cantidadPorUnidad'],
                        'merma'    => $r['mermaPct']
                    ];
                }
            }
        }

        // Convertir BLOBs a Base64 para JSON
        try {
            if (!empty($row['foto'])) {
                if (is_resource($row['foto'])) {
                    $row['foto'] = stream_get_contents($row['foto']);
                }
                $row['foto'] = base64_encode($row['foto']);
            }
        } catch (\Throwable $e) { $row['foto'] = null; }

        try {
            if (!empty($row['patron'])) {
                if (is_resource($row['patron'])) {
                    $row['patron'] = stream_get_contents($row['patron']);
                }
                $row['patron'] = base64_encode($row['patron']);
            }
        } catch (\Throwable $e) { $row['patron'] = null; }

        // Compatibilidad: crear array de imágenes con foto y patrón
        $row['archivosCad'] = [];
        $row['archivosPatron'] = [];
        $row['imagenes'] = [];
        
        if (!empty($row['foto'])) {
            $row['imagenes'][] = 'data:image/jpeg;base64,' . $row['foto'];
        }
        
        if (!empty($row['patron'])) {
            // Intentar detectar el tipo de archivo del patrón
            // Puede ser PDF, imagen, o DXF
            $row['imagenes'][] = 'data:application/pdf;base64,' . $row['patron'];
        }

        return $row;
    }
    }
    
    
