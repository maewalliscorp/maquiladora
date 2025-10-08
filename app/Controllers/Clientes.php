<?php

namespace App\Controllers;

class Clientes extends BaseController
{
    // Devuelve catálogo de clientes con su dirección principal
    public function json_catalogo()
    {
        $db = \Config\Database::connect();
        $rows = [];
        $clienteTables = ['cliente','Cliente'];
        $dirTables     = ['cliente_direccion','ClienteDireccion'];
        $claTables     = ['cliente_clasificacion','ClienteClasificacion'];

        foreach ($clienteTables as $ct) {
            foreach ($dirTables as $dt) {
                foreach ($claTables as $cct) {
                    try {
                        // Subconsulta para tomar la última clasificación por cliente (por id máximo)
                        $subCla = "SELECT cc1.* FROM $cct cc1 
                                   JOIN (SELECT clienteId, MAX(id) AS maxid FROM $cct GROUP BY clienteId) m
                                     ON m.clienteId = cc1.clienteId AND m.maxid = cc1.id";

                        $sql = "SELECT c.id,
                                       c.nombre,
                                       c.email,
                                       c.telefono,
                                       d.*,
                                       cc.clasificacionId AS clasificacionId,
                                       cc.descripcion      AS cla_desc
                                FROM $ct c
                                LEFT JOIN $dt d
                                       ON d.clienteId = c.id
                                      AND (d.esPrincipal = 1 OR d.esPrincipal IS NULL)
                                LEFT JOIN ($subCla) cc
                                       ON cc.clienteId = c.id
                                ORDER BY c.nombre";
                        $rows = $db->query($sql)->getResultArray();
                        if (is_array($rows)) { throw new \Exception('ok'); }
                    } catch (\Throwable $e) {
                        // Intentar siguiente combinación; si lanzamos 'ok', salimos de los bucles
                        if ($e instanceof \Exception && $e->getMessage() === 'ok') { break 3; }
                    }
                }
            }
        }

        $out = array_map(function($r){
            // Helper para obtener valor por múltiples posibles claves
            $getv = function(array $row, array $keys, $default = '') {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $row) && $row[$k] !== null) {
                        return $row[$k];
                    }
                }
                return $default;
            };

            $dir = [
                'calle'  => $getv($r, ['calle', 'Calle']),
                'numExt' => $getv($r, ['numExt', 'num_ext', 'numext', 'numeroExterior', 'numero_exterior', 'NumeroExterior']),
                'numInt' => $getv($r, ['numInt', 'num_int', 'numint', 'numeroInterior', 'numero_interior', 'NumeroInterior']),
                'ciudad' => $getv($r, ['ciudad', 'Ciudad']),
                'estado' => $getv($r, ['estado', 'Estado', 'provincia', 'Provincia']),
                'cp'     => $getv($r, ['cp', 'CP', 'codigo_postal', 'codigoPostal', 'CodigoPostal', 'zip', 'ZIP']),
                'pais'   => $getv($r, ['pais', 'País', 'Pais', 'country', 'Country']),
            ];

            $claDesc = $getv($r, ['cla_desc', 'descripcion', 'Descripcion']);
            $claNombre = $getv($r, ['cla_nombre', 'nombre_clasificacion', 'NombreClasificacion']);
            if ($claNombre === '' && $claDesc !== '') { $claNombre = $claDesc; }

            return [
                'id'       => $r['id'] ?? null,
                'nombre'   => $getv($r, ['nombre', 'Nombre'], ''),
                'email'    => $getv($r, ['email', 'Email', 'correo', 'Correo'], ''),
                'telefono' => $getv($r, ['telefono', 'Telefono', 'tel', 'Tel'], ''),
                'direccion_detalle'=> $dir,
                'clasificacion' => [
                    'id'          => $getv($r, ['clasificacionId', 'clasificacion_id', 'ClasificacionId'], null),
                    'nombre'      => $claNombre,
                    'descripcion' => $claDesc,
                ],
            ];
        }, $rows ?: []);

        return $this->response->setJSON($out);
    }

    // Catálogo de clasificaciones disponibles (distintas en la BD)
    public function json_clasificaciones()
    {
        $db = \Config\Database::connect();
        $rows = [];
        foreach (['cliente_clasificacion','ClienteClasificacion'] as $t) {
            try {
                $sql = "SELECT DISTINCT clasificacionId AS id, descripcion FROM $t WHERE clasificacionId IS NOT NULL ORDER BY descripcion";
                $rows = $db->query($sql)->getResultArray();
                if ($rows) break;
            } catch (\Throwable $e) {
                // intentar siguiente nombre de tabla
            }
        }

        // Normalizar salida; si no hay nombre, usaremos la descripción como nombre
        $out = array_map(function($r){
            $desc = $r['descripcion'] ?? '';
            return [
                'id'          => $r['id'] ?? null,
                'nombre'      => $r['nombre'] ?? $desc,
                'descripcion' => $desc,
            ];
        }, $rows ?: []);

        return $this->response->setJSON($out);
    }
}
