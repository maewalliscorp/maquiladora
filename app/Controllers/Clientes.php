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

        foreach ($clienteTables as $ct) {
            foreach ($dirTables as $dt) {
                try {
                    $sql = "SELECT c.id,
                                   c.nombre,
                                   c.email,
                                   c.telefono,
                                   d.calle,
                                   d.numExt,
                                   d.numInt,
                                   d.ciudad,
                                   d.estado,
                                   d.cp,
                                   d.pais
                            FROM $ct c
                            LEFT JOIN $dt d
                                   ON d.clienteId = c.id
                                  AND (d.esPrincipal = 1 OR d.esPrincipal IS NULL)
                            ORDER BY c.nombre";
                    $rows = $db->query($sql)->getResultArray();
                    if (is_array($rows)) { break 2; }
                } catch (\Throwable $e) {
                    // Probar siguiente combinación
                }
            }
        }

        $out = array_map(function($r){
            return [
                'id'       => $r['id'] ?? null,
                'nombre'   => $r['nombre'] ?? '',
                'email'    => $r['email'] ?? '',
                'telefono' => $r['telefono'] ?? '',
                'direccion'=> [
                    'calle'  => $r['calle'] ?? '',
                    'numExt' => $r['numExt'] ?? '',
                    'numInt' => $r['numInt'] ?? '',
                    'ciudad' => $r['ciudad'] ?? '',
                    'estado' => $r['estado'] ?? '',
                    'cp'     => $r['cp'] ?? '',
                    'pais'   => $r['pais'] ?? '',
                ],
            ];
        }, $rows ?: []);

        return $this->response->setJSON($out);
    }
}
