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

                        $sql = "SELECT c.id AS clienteId,
                                       c.nombre,
                                       c.email,
                                       c.telefono,
                                       c.rfc,
                                       c.tipo_persona,
                                       c.fechaRegistro,
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
            
            // Log the raw row for debugging
            log_message('debug', 'Raw client row in catalog: ' . json_encode($r));

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
                'id'       => $getv($r, ['clienteId', 'id', 'ID'], null),
                'nombre'   => $getv($r, ['nombre', 'Nombre'], ''),
                'email'    => $getv($r, ['email', 'Email', 'correo', 'Correo'], ''),
                'telefono' => $getv($r, ['telefono', 'Telefono', 'tel', 'Tel'], ''),
                'rfc'      => $getv($r, ['rfc', 'RFC'], ''),
                'tipo_persona' => $getv($r, ['tipo_persona', 'tipoPersona'], ''),
                'fechaRegistro' => $getv($r, ['fechaRegistro', 'fecha', 'created_at'], ''),
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

    public function json_detalle($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) { return $this->response->setStatusCode(400)->setJSON(['error'=>'ID inválido']); }
        $db = \Config\Database::connect();
        $row = null; $addr = null;
        foreach (['cliente','Cliente'] as $t) {
            try { $row = $db->table($t)->where('id',$id)->get()->getRowArray(); if ($row) break; } catch (\Throwable $e) {}
        }
        if (!$row) { return $this->response->setStatusCode(404)->setJSON(['error'=>'No encontrado']); }
        foreach (['cliente_direccion','ClienteDireccion'] as $t) {
            try {
                $addr = $db->table($t)->where('clienteId',$id)->orderBy('esPrincipal','DESC')->orderBy('id','DESC')->get()->getRowArray();
                if ($addr) break;
            } catch (\Throwable $e) {}
        }
        $out = [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'] ?? '',
            'email' => $row['email'] ?? '',
            'telefono' => $row['telefono'] ?? '',
            'rfc' => $row['rfc'] ?? $row['RFC'] ?? '',
            'tipo_persona' => $row['tipo_persona'] ?? $row['tipo_persona'] ?? '',
            'fechaRegistro' => $row['fechaRegistro'] ?? null,
            'direccion' => $addr ? [
                'id' => (int)($addr['id'] ?? 0),
                'calle' => $addr['calle'] ?? '',
                'numExt' => $addr['numExt'] ?? '',
                'numInt' => $addr['numInt'] ?? '',
                'ciudad' => $addr['ciudad'] ?? '',
                'estado' => $addr['estado'] ?? '',
                'cp' => $addr['cp'] ?? '',
                'pais' => $addr['pais'] ?? '',
                'esPrincipal' => (int)($addr['esPrincipal'] ?? 0),
            ] : null,
        ];
        return $this->response->setJSON($out);
    }

    public function actualizar($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) { return $this->response->setStatusCode(400)->setJSON(['ok'=>false]); }
        $db = \Config\Database::connect();
        $nombre = trim((string)$this->request->getPost('nombre'));
        $email = trim((string)$this->request->getPost('email'));
        $telefono = trim((string)$this->request->getPost('telefono'));
        $rfc = trim((string)$this->request->getPost('rfc'));
        $tipo_persona = trim((string)$this->request->getPost('tipo_persona'));
        $fechaRegistro = $this->request->getPost('fechaRegistro');
        if ($fechaRegistro) {
            $fr = trim((string)$fechaRegistro);
            if (strpos($fr, '/') !== false) {
                $parts = preg_split('#[\/\-]#', $fr);
                if (count($parts) === 3) {
                    $d = (int)$parts[0]; $m = (int)$parts[1]; $y = (int)$parts[2];
                    if ($d > 0 && $m > 0 && $y > 0) { $fechaRegistro = sprintf('%04d-%02d-%02d', $y, $m, $d); }
                }
            } else {
                $ts = strtotime($fr);
                if ($ts) { $fechaRegistro = date('Y-m-d', $ts); }
            }
        }
        $calle = trim((string)$this->request->getPost('calle'));
        $numExt = trim((string)$this->request->getPost('numExt'));
        $numInt = trim((string)$this->request->getPost('numInt'));
        $ciudad = trim((string)$this->request->getPost('ciudad'));
        $estado = trim((string)$this->request->getPost('estado'));
        $cp = trim((string)$this->request->getPost('cp'));
        $pais = trim((string)$this->request->getPost('pais'));
        $db->transStart();
        $ok1 = false; $ok2 = false; $aff1 = 0; $aff2 = 0;
        foreach (['cliente','Cliente'] as $t) {
            try {
                $data = [];
                if ($nombre !== '') $data['nombre'] = $nombre;
                if ($email !== '') $data['email'] = $email;
                if ($telefono !== '') $data['telefono'] = $telefono;
                if ($rfc !== '') $data['rfc'] = $rfc;
                else $data['rfc'] = null;
                if ($tipo_persona !== '') $data['tipo_persona'] = $tipo_persona;
                else $data['tipo_persona'] = null;
                if ($data) { $db->table($t)->where('id',$id)->update($data); $aff1 = $db->affectedRows(); }
                $ok1 = true; break;
            } catch (\Throwable $e) {}
        }
        $addrTable = null;
        foreach (['cliente_direccion','ClienteDireccion'] as $t) {
            try {
                $db->query("SELECT 1 FROM $t LIMIT 1");
                $addrTable = $t; break;
            } catch (\Throwable $e) { /* try next */ }
        }
        if ($addrTable === null) { $addrTable = 'cliente_direccion'; }
        try {
            $ex = null;
            try { $ex = $db->query("SELECT id FROM $addrTable WHERE clienteId = ? ORDER BY esPrincipal DESC, id DESC LIMIT 1", [$id])->getRowArray(); } catch (\Throwable $e) { $ex = null; }
            $addrData = [
                'clienteId' => $id,
                'calle' => $calle,
                'numExt' => $numExt,
                'numInt' => $numInt,
                'ciudad' => $ciudad,
                'estado' => $estado,
                'cp' => $cp,
                'pais' => $pais,
                'esPrincipal' => 1,
            ];
            if ($ex && isset($ex['id'])) {
                $db->table($addrTable)->where('id', (int)$ex['id'])->update($addrData);
                $aff2 = $db->affectedRows();
            } else {
                $db->table($addrTable)->insert($addrData);
                $aff2 = $db->affectedRows();
            }
            $ok2 = true;
        } catch (\Throwable $e) { $ok2 = false; }
        $db->transComplete();
        if ($db->transStatus() === false || !$ok1) {
            $err = $db->error();
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'error'=>($err['message'] ?? 'Transacción fallida')]);
        }
        return $this->response->setJSON(['ok'=>true, 'updatedCliente'=>$aff1, 'updatedDireccion'=>$aff2]);
    }

    public function crear()
    {
        $db = \Config\Database::connect();
        $nombre = trim((string)$this->request->getPost('nombre'));
        $email = trim((string)$this->request->getPost('email'));
        $telefono = trim((string)$this->request->getPost('telefono'));
        $rfc = trim((string)$this->request->getPost('rfc'));
        $tipo_persona = trim((string)$this->request->getPost('tipo_persona'));
        $fechaRegistro = $this->request->getPost('fechaRegistro');
        if ($fechaRegistro) {
            $fr = trim((string)$fechaRegistro);
            if (strpos($fr, '/') !== false) {
                $parts = preg_split('#[\/\-]#', $fr);
                if (count($parts) === 3) {
                    $d = (int)$parts[0]; $m = (int)$parts[1]; $y = (int)$parts[2];
                    if ($d > 0 && $m > 0 && $y > 0) { $fechaRegistro = sprintf('%04d-%02d-%02d', $y, $m, $d); }
                }
            } else {
                $ts = strtotime($fr);
                if ($ts) { $fechaRegistro = date('Y-m-d', $ts); }
            }
        }
        $calle = trim((string)$this->request->getPost('calle'));
        $numExt = trim((string)$this->request->getPost('numExt'));
        $numInt = trim((string)$this->request->getPost('numInt'));
        $ciudad = trim((string)$this->request->getPost('ciudad'));
        $estado = trim((string)$this->request->getPost('estado'));
        $cp = trim((string)$this->request->getPost('cp'));
        $pais = trim((string)$this->request->getPost('pais'));

        if ($nombre === '') { return $this->response->setStatusCode(422)->setJSON(['ok'=>false, 'error'=>'Nombre requerido']); }

        $db->transStart();
        $clienteTable = null;
        foreach (['cliente','Cliente'] as $t) {
            try { $db->query("SELECT 1 FROM $t LIMIT 1"); $clienteTable = $t; break; } catch (\Throwable $e) {}
        }
        if (!$clienteTable) { $clienteTable = 'cliente'; }

        $db->table($clienteTable)->insert([
            'nombre' => $nombre,
            'email' => $email !== '' ? $email : null,
            'telefono' => $telefono !== '' ? $telefono : null,
            'rfc' => $rfc !== '' ? $rfc : null,
            'tipo_persona' => $tipo_persona !== '' ? $tipo_persona : null,
            'fechaRegistro' => date('Y-m-d'),
        ]);
        $newId = (int)$db->insertID();

        $addrTable = null;
        foreach (['cliente_direccion','ClienteDireccion'] as $t) {
            try { $db->query("SELECT 1 FROM $t LIMIT 1"); $addrTable = $t; break; } catch (\Throwable $e) {}
        }
        if ($addrTable) {
            $db->table($addrTable)->insert([
                'clienteId' => $newId,
                'calle' => $calle,
                'numExt' => $numExt,
                'numInt' => $numInt,
                'ciudad' => $ciudad,
                'estado' => $estado,
                'cp' => $cp,
                'pais' => $pais,
                'esPrincipal' => 1,
            ]);
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            $err = $db->error();
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'error'=>($err['message'] ?? 'Transacción fallida')]);
        }
        return $this->response->setJSON(['ok'=>true, 'id'=>$newId]);
    }

    public function eliminar($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) { return $this->response->setStatusCode(400)->setJSON(['ok'=>false, 'error'=>'ID inválido']); }
        $db = \Config\Database::connect();
        $db->transStart();
        // eliminar direcciones primero
        $addrTable = null;
        foreach (['cliente_direccion','ClienteDireccion'] as $t) {
            try { $db->query("SELECT 1 FROM $t LIMIT 1"); $addrTable = $t; break; } catch (\Throwable $e) {}
        }
        if ($addrTable) {
            try { $db->table($addrTable)->where('clienteId', $id)->delete(); } catch (\Throwable $e) {}
        }
        // eliminar cliente
        $cliTable = null;
        foreach (['cliente','Cliente'] as $t) {
            try { $db->query("SELECT 1 FROM $t LIMIT 1"); $cliTable = $t; break; } catch (\Throwable $e) {}
        }
        if ($cliTable) {
            try { $db->table($cliTable)->where('id', $id)->delete(); } catch (\Throwable $e) {}
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            $err = $db->error();
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false, 'error'=>($err['message'] ?? 'Transacción fallida')]);
        }
        return $this->response->setJSON(['ok'=>true]);
    }
}
