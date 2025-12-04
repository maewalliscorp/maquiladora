<?php

namespace App\Controllers;

class Clientes extends BaseController
{
    // Devuelve catálogo de clientes con su dirección principal (filtrado por maquiladora)
    public function json_catalogo()
    {
        $db = \Config\Database::connect();
        $rows = [];
        
        try {
            $maquiladoraId = session()->get('maquiladora_id');
            
            $sql = "SELECT 
                       c.id AS clienteId,
                       c.nombre,
                       c.email,
                       c.telefono,
                       c.rfc,
                       c.tipo_persona,
                       c.fechaRegistro,
                       c.clasificacionId,
                       cc.nombre_cla,
                       cc.descripcion AS cla_desc,
                       d.calle,
                       d.numExt,
                       d.numInt,
                       d.ciudad,
                       d.estado,
                       d.cp,
                       d.pais
                FROM cliente c
                LEFT JOIN cliente_clasificacion cc ON cc.id = c.clasificacionId
                LEFT JOIN cliente_direccion d ON d.clienteId = c.id";
            
            // Filtrar por maquiladora si existe en la sesión
            if ($maquiladoraId) {
                $sql .= " INNER JOIN Cliente_Maquiladora cm ON cm.clienteFK = c.id
                          WHERE cm.maquiladoraFK = " . (int)$maquiladoraId;
            }
            
            $sql .= " ORDER BY c.nombre";

            log_message('debug', 'SQL Query: ' . $sql);
            $rows = $db->query($sql)->getResultArray();
            log_message('debug', 'Número de filas encontradas: ' . count($rows));

            // Formatear la salida
            $out = array_map(function($row) {
                $direccion = [
                    'calle'  => $row['calle'] ?? '',
                    'numExt' => $row['numExt'] ?? '',
                    'numInt' => $row['numInt'] ?? '',
                    'ciudad' => $row['ciudad'] ?? '',
                    'estado' => $row['estado'] ?? '',
                    'cp'     => $row['cp'] ?? '',
                    'pais'   => $row['pais'] ?? ''
                ];

                $clasificacion = [
                    'id'          => $row['clasificacionId'] ?? null,
                    'nombre'      => $row['nombre_cla'] ?? '',
                    'descripcion' => $row['cla_desc'] ?? ''
                ];

                return [
                    'id'               => $row['clienteId'] ?? null,
                    'nombre'           => $row['nombre'] ?? '',
                    'email'            => $row['email'] ?? '',
                    'telefono'         => $row['telefono'] ?? '',
                    'rfc'              => $row['rfc'] ?? '',
                    'tipo_persona'     => $row['tipo_persona'] ?? '',
                    'fechaRegistro'    => $row['fechaRegistro'] ?? null,
                    'direccion_detalle' => $direccion,
                    'clasificacion'    => $clasificacion
                ];
            }, $rows);

            log_message('debug', 'Datos a devolver: ' . print_r($out, true));
            return $this->response->setJSON($out);

        } catch (\Throwable $e) {
            log_message('error', 'Error al cargar clientes: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Error al cargar los clientes',
                'message' => $e->getMessage()
            ]);
        }
    }

    // Catálogo de clasificaciones disponibles
    public function json_clasificaciones()
    {
        $db = \Config\Database::connect();
        $rows = [];
        
        try {
            $sql = "SELECT 
                       id,
                       nombre_cla AS nombre,
                       descripcion
                FROM cliente_clasificacion
                ORDER BY nombre_cla";
                
            $rows = $db->query($sql)->getResultArray();
            return $this->response->setJSON($rows);
            
        } catch (\Throwable $e) {
            log_message('error', 'Error al cargar clasificaciones: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Error al cargar las clasificaciones']);
        }
    }

    // Obtener detalles de un cliente específico (validando que pertenezca a la maquiladora)
    public function json_detalle($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) { 
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']); 
        }

        $db = \Config\Database::connect();
        
        try {
            $maquiladoraId = session()->get('maquiladora_id');
            
            // Obtener datos del cliente
            $sql = "SELECT 
                       c.id AS clienteId,
                       c.nombre,
                       c.email,
                       c.telefono,
                       c.rfc,
                       c.tipo_persona,
                       c.fechaRegistro,
                       c.clasificacionId,
                       cc.nombre_cla,
                       cc.descripcion AS cla_desc,
                       d.id AS direccionId,
                       d.calle,
                       d.numExt,
                       d.numInt,
                       d.ciudad,
                       d.estado,
                       d.cp,
                       d.pais
                FROM cliente c
                LEFT JOIN cliente_clasificacion cc ON cc.id = c.clasificacionId
                LEFT JOIN cliente_direccion d ON d.clienteId = c.id
                WHERE c.id = ?";
            
            // Si hay maquiladora en sesión, validar que el cliente pertenezca a esa maquiladora
            if ($maquiladoraId) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM Cliente_Maquiladora cm 
                    WHERE cm.clienteFK = c.id 
                    AND cm.maquiladoraFK = " . (int)$maquiladoraId . "
                )";
            }
            
            $sql .= " ORDER BY d.id DESC LIMIT 1";

            $row = $db->query($sql, [$id])->getRowArray();
            
            if (!$row) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Cliente no encontrado o no tiene acceso']);
            }

            // Formatear la respuesta
            $direccion = [
                'id'      => $row['direccionId'] ?? null,
                'calle'   => $row['calle'] ?? '',
                'numExt'  => $row['numExt'] ?? '',
                'numInt'  => $row['numInt'] ?? '',
                'ciudad'  => $row['ciudad'] ?? '',
                'estado'  => $row['estado'] ?? '',
                'cp'      => $row['cp'] ?? '',
                'pais'    => $row['pais'] ?? ''
            ];

            $clasificacion = [
                'id'          => $row['clasificacionId'] ?? null,
                'nombre'      => $row['nombre_cla'] ?? '',
                'descripcion' => $row['cla_desc'] ?? ''
            ];

            $response = [
                'id'                => $row['clienteId'] ?? null,
                'nombre'            => $row['nombre'] ?? '',
                'email'             => $row['email'] ?? '',
                'telefono'          => $row['telefono'] ?? '',
                'rfc'               => $row['rfc'] ?? '',
                'tipo_persona'      => $row['tipo_persona'] ?? '',
                'fechaRegistro'     => $row['fechaRegistro'] ?? null,
                'direccion_detalle' => $direccion,
                'clasificacion'     => $clasificacion
            ];

            log_message('debug', 'Datos del cliente a devolver: ' . print_r($response, true));
            return $this->response->setJSON($response);
            
        } catch (\Throwable $e) {
            log_message('error', 'Error al cargar cliente: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al cargar el cliente',
                'message' => $e->getMessage()
            ]);
        }
    }

    // Crear un nuevo cliente
    public function crear()
    {
        $db = \Config\Database::connect();
        
        // Validar datos de entrada
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nombre' => 'required|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email|max_length[255]',
            'telefono' => 'permit_empty|max_length[20]',
            'rfc' => 'permit_empty|max_length[20]',
            'tipo_persona' => 'permit_empty|in_list[FISICA,MORAL]',
            'calle' => 'permit_empty|max_length[255]',
            'numExt' => 'permit_empty|max_length[20]',
            'numInt' => 'permit_empty|max_length[20]',
            'ciudad' => 'permit_empty|max_length[100]',
            'estado' => 'permit_empty|max_length[100]',
            'cp' => 'permit_empty|max_length[10]',
            'pais' => 'permit_empty|max_length[100]',
            'clasificacionId' => 'required|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Datos inválidos',
                'errors' => $validation->getErrors()
            ]);
        }

        $db->transStart();
        
        try {
            // Helper para convertir strings vacíos a null
            $toNull = function($value) {
                return ($value === '' || $value === null) ? null : $value;
            };
            
            // Insertar cliente
            $clienteData = [
                'nombre' => $this->request->getPost('nombre'),
                'email' => $toNull($this->request->getPost('email')),
                'telefono' => $toNull($this->request->getPost('telefono')),
                'rfc' => $toNull($this->request->getPost('rfc')),
                'tipo_persona' => $toNull($this->request->getPost('tipo_persona')),
                'fechaRegistro' => date('Y-m-d H:i:s'),
                'clasificacionId' => (int)$this->request->getPost('clasificacionId')
            ];

            log_message('debug', 'Datos del cliente a insertar: ' . print_r($clienteData, true));
            
            if (!$db->table('cliente')->insert($clienteData)) {
                $error = $db->error();
                throw new \Exception('Error al insertar cliente: ' . ($error['message'] ?? 'Error desconocido'));
            }
            
            $clienteId = $db->insertID();
            
            if (!$clienteId) {
                throw new \Exception('No se pudo obtener el ID del cliente insertado');
            }
            
            log_message('debug', 'Cliente insertado con ID: ' . $clienteId);

            // Insertar dirección solo si hay al menos un campo de dirección
            $calle = $toNull($this->request->getPost('calle'));
            $numExt = $toNull($this->request->getPost('numExt'));
            $numInt = $toNull($this->request->getPost('numInt'));
            $ciudad = $toNull($this->request->getPost('ciudad'));
            $estado = $toNull($this->request->getPost('estado'));
            $cp = $toNull($this->request->getPost('cp'));
            $pais = $toNull($this->request->getPost('pais'));
            
            // Solo insertar dirección si hay al menos un campo con valor
            if ($calle || $numExt || $numInt || $ciudad || $estado || $cp || $pais) {
                // Intentar primero con clienteId, si falla intentar con clienteld
                $direccionData = [
                    'clienteId' => $clienteId,
                    'calle' => $calle,
                    'numExt' => $numExt,
                    'numInt' => $numInt,
                    'ciudad' => $ciudad,
                    'estado' => $estado,
                    'cp' => $cp,
                    'pais' => $pais
                ];
                
                log_message('debug', 'Datos de dirección a insertar: ' . print_r($direccionData, true));
                
                $inserted = $db->table('cliente_direccion')->insert($direccionData);
                
                // Si falla con clienteId, intentar con clienteld
                if (!$inserted) {
                    $error = $db->error();
                    log_message('debug', 'Error al insertar con clienteId, intentando con clienteld. Error: ' . print_r($error, true));
                    
                    // Intentar con clienteld
                    $direccionDataAlt = $direccionData;
                    unset($direccionDataAlt['clienteId']);
                    $direccionDataAlt['clienteld'] = $clienteId;
                    
                    if (!$db->table('cliente_direccion')->insert($direccionDataAlt)) {
                        $error = $db->error();
                        throw new \Exception('Error al insertar dirección: ' . ($error['message'] ?? 'Error desconocido'));
                    }
                }
            }

            // Relacionar con maquiladora si existe en la sesión
            $maquiladoraId = session()->get('maquiladora_id');
            if ($maquiladoraId) {
                $db->table('Cliente_Maquiladora')->insert([
                    'clienteFK' => $clienteId,
                    'maquiladoraFK' => $maquiladoraId
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                $error = $db->error();
                $errorMsg = 'Error en la transacción';
                if ($error && is_array($error)) {
                    $errorMsg .= ': ' . ($error['message'] ?? '');
                    if (isset($error['code'])) {
                        $errorMsg .= ' (Código: ' . $error['code'] . ')';
                    }
                } else {
                    // Intentar obtener el último error de la conexión
                    $lastError = $db->connID->error ?? null;
                    if ($lastError) {
                        $errorMsg .= ': ' . (is_array($lastError) ? ($lastError['message'] ?? '') : $lastError);
                    }
                }
                throw new \Exception($errorMsg);
            }

            return $this->response->setJSON([
                'success' => true,
                'id' => $clienteId,
                'message' => 'Cliente creado correctamente'
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            $error = $db->error();
            $errorMsg = $e->getMessage();
            
            // Agregar información del error de la base de datos si está disponible
            if ($error && is_array($error) && !empty($error['message'])) {
                $errorMsg .= ' - DB: ' . $error['message'];
                if (isset($error['code'])) {
                    $errorMsg .= ' (Código: ' . $error['code'] . ')';
                }
            }
            
            // Intentar obtener el último error de la conexión si no hay error en el array
            if (empty($errorMsg) || $errorMsg === $e->getMessage()) {
                try {
                    $connError = $db->connID->error ?? null;
                    if ($connError) {
                        if (is_array($connError)) {
                            $errorMsg .= ' - Conn: ' . ($connError['message'] ?? '');
                        } else {
                            $errorMsg .= ' - Conn: ' . $connError;
                        }
                    }
                } catch (\Exception $connEx) {
                    // Ignorar si no se puede obtener el error de conexión
                }
            }
            
            log_message('error', 'Error al crear cliente: ' . $errorMsg);
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            log_message('error', 'Error array: ' . print_r($error, true));
            
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al crear el cliente',
                'message' => $errorMsg,
                'debug' => [
                    'exception' => $e->getMessage(),
                    'db_error' => $error,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    // Actualizar un cliente existente
    public function actualizar($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) { 
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']); 
        }

        $db = \Config\Database::connect();
        
        // Validar datos de entrada
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nombre' => 'required|min_length[3]|max_length[255]',
            'email' => 'valid_email|max_length[255]',
            'telefono' => 'max_length[20]',
            'rfc' => 'max_length[20]',
            'tipo_persona' => 'in_list[FISICA,MORAL]',
            'calle' => 'max_length[255]',
            'numExt' => 'max_length[20]',
            'numInt' => 'max_length[20]',
            'ciudad' => 'max_length[100]',
            'estado' => 'max_length[100]',
            'cp' => 'max_length[10]',
            'pais' => 'max_length[100]',
            'clasificacionId' => 'integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Datos inválidos',
                'errors' => $validation->getErrors()
            ]);
        }

        $db->transStart();
        
        try {
            // Verificar que el cliente existe
            $cliente = $db->table('cliente')->where('id', $id)->get()->getRowArray();
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Actualizar cliente
            $clienteData = [
                'nombre' => $this->request->getPost('nombre'),
                'email' => $this->request->getPost('email'),
                'telefono' => $this->request->getPost('telefono'),
                'rfc' => $this->request->getPost('rfc'),
                'tipo_persona' => $this->request->getPost('tipo_persona'),
                'clasificacionId' => $this->request->getPost('clasificacionId')
            ];

            $db->table('cliente')->where('id', $id)->update($clienteData);

            // Actualizar o insertar dirección
            $direccionData = [
                'calle' => $this->request->getPost('calle'),
                'numExt' => $this->request->getPost('numExt'),
                'numInt' => $this->request->getPost('numInt'),
                'ciudad' => $this->request->getPost('ciudad'),
                'estado' => $this->request->getPost('estado'),
                'cp' => $this->request->getPost('cp'),
                'pais' => $this->request->getPost('pais')
            ];

            $direccion = $db->table('cliente_direccion')
                           ->where('clienteId', $id)
                           ->get()
                           ->getRowArray();

            if ($direccion) {
                $db->table('cliente_direccion')
                   ->where('id', $direccion['id'])
                   ->update($direccionData);
            } else {
                $direccionData['clienteId'] = $id;
                $db->table('cliente_direccion')->insert($direccionData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al actualizar el cliente');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cliente actualizado correctamente'
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al actualizar cliente: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al actualizar el cliente',
                'message' => $e->getMessage()
            ]);
        }
    }

    // Eliminar un cliente
    public function eliminar($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) { 
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']); 
        }

        $db = \Config\Database::connect();
        
        $db->transStart();
        
        try {
            // Verificar que el cliente existe
            $cliente = $db->table('cliente')->where('id', $id)->get()->getRowArray();
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Eliminar relaciones primero
            $db->table('Cliente_Maquiladora')->where('clienteFK', $id)->delete();
            $db->table('cliente_direccion')->where('clienteId', $id)->delete();
            
            // Finalmente, eliminar el cliente
            $db->table('cliente')->where('id', $id)->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al eliminar el cliente');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cliente eliminado correctamente'
            ]);

        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Error al eliminar cliente: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al eliminar el cliente',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene la lista de clasificaciones de clientes filtradas por maquiladora
     */
    public function getClasificaciones()
    {
        $db = \Config\Database::connect();
        
        try {
            $maquiladoraId = session()->get('maquiladora_id');
            
            $query = $db->table('cliente_clasificacion')
                       ->select('id, nombre_cla as nombre, descripcion, maquiladoraID');
            
            // Filtrar por maquiladoraID de la sesión
            if ($maquiladoraId) {
                $query->where('maquiladoraID', $maquiladoraId);
            } else {
                // Si no hay maquiladora_id en sesión, no mostrar ninguna clasificación
                // o mostrar solo las que no tienen maquiladoraID asignado
                $query->where('maquiladoraID IS NULL');
            }
            
            $query->orderBy('nombre_cla', 'ASC');
            $clasificaciones = $query->get()->getResultArray();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $clasificaciones
            ]);
            
        } catch (\Throwable $e) {
            log_message('error', 'Error al obtener clasificaciones: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error al cargar las clasificaciones',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Crea una nueva clasificación
     */
    public function crearClasificacion()
    {
        $db = \Config\Database::connect();
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nombre' => 'required|min_length[1]|max_length[255]',
            'descripcion' => 'permit_empty|max_length[500]',
            'maquiladoraID' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'Datos inválidos',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            $data = [
                'nombre_cla' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion') ?: null,
                'maquiladoraID' => $this->request->getPost('maquiladoraID') ? (int)$this->request->getPost('maquiladoraID') : null
            ];

            $db->table('cliente_clasificacion')->insert($data);
            $id = $db->insertID();

            return $this->response->setJSON([
                'success' => true,
                'id' => $id,
                'message' => 'Clasificación creada correctamente'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Error al crear clasificación: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error al crear la clasificación',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Actualiza una clasificación existente
     */
    public function editarClasificacion($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'ID inválido'
            ]);
        }

        $db = \Config\Database::connect();
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nombre' => 'required|min_length[1]|max_length[255]',
            'descripcion' => 'permit_empty|max_length[500]',
            'maquiladoraID' => 'permit_empty|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'Datos inválidos',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // Verificar que existe
            $clasificacion = $db->table('cliente_clasificacion')->where('id', $id)->get()->getRowArray();
            if (!$clasificacion) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'error' => 'Clasificación no encontrada'
                ]);
            }

            $data = [
                'nombre_cla' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion') ?: null,
                'maquiladoraID' => $this->request->getPost('maquiladoraID') ? (int)$this->request->getPost('maquiladoraID') : null
            ];

            $db->table('cliente_clasificacion')->where('id', $id)->update($data);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Clasificación actualizada correctamente'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Error al actualizar clasificación: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error al actualizar la clasificación',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina una clasificación
     */
    public function eliminarClasificacion($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'ID inválido'
            ]);
        }

        $db = \Config\Database::connect();
        
        try {
            // Verificar que existe
            $clasificacion = $db->table('cliente_clasificacion')->where('id', $id)->get()->getRowArray();
            if (!$clasificacion) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'error' => 'Clasificación no encontrada'
                ]);
            }

            // Verificar si hay clientes usando esta clasificación
            $clientes = $db->table('cliente')->where('clasificacionId', $id)->countAllResults();
            if ($clientes > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'error' => 'No se puede eliminar la clasificación porque hay ' . $clientes . ' cliente(s) que la están usando'
                ]);
            }

            $db->table('cliente_clasificacion')->where('id', $id)->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Clasificación eliminada correctamente'
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Error al eliminar clasificación: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error' => 'Error al eliminar la clasificación',
                'message' => $e->getMessage()
            ]);
        }
    }
}