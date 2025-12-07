<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class MaquiladorasAPI extends ResourceController
{
    protected $modelName = 'App\Models\MaquiladoraModel';
    protected $format    = 'json';

    /**
     * Listar todas las maquiladoras
     */
    public function listar()
    {
        try {
            $maquiladoras = $this->model->findAll();
            
            // Convertir los logos BLOB a base64 para mostrarlos
            foreach ($maquiladoras as &$maquiladora) {
                if (!empty($maquiladora['logo'])) {
                    $maquiladora['logo'] = base64_encode($maquiladora['logo']);
                }
            }
            
            return $this->respond($maquiladoras);
        } catch (\Exception $e) {
            return $this->failServerError('Error al obtener las maquiladoras: ' . $e->getMessage());
        }
    }

    /**
     * Obtener una maquiladora por ID
     */
    public function obtener($id = null)
    {
        try {
            $maquiladora = $this->model->find($id);
            
            if (!$maquiladora) {
                return $this->failNotFound('Maquiladora no encontrada');
            }
            
            // Convertir el logo BLOB a base64
            if (!empty($maquiladora['logo'])) {
                $maquiladora['logo'] = base64_encode($maquiladora['logo']);
            }
            
            return $this->respond($maquiladora);
        } catch (\Exception $e) {
            return $this->failServerError('Error al obtener la maquiladora: ' . $e->getMessage());
        }
    }

    /**
     * Crear una nueva maquiladora
     */
    public function crear()
    {
        try {
            $data = [
                'Nombre_Maquila' => $this->request->getPost('Nombre_Maquila'),
                'Dueno'          => $this->request->getPost('Dueno'),
                'Telefono'       => $this->request->getPost('Telefono'),
                'Correo'         => $this->request->getPost('Correo'),
                'Domicilio'      => $this->request->getPost('Domicilio'),
                'tipo'           => $this->request->getPost('tipo'),
                'status'         => $this->request->getPost('status') ?: 1,
            ];

            // Validar campos requeridos
            if (empty($data['Nombre_Maquila']) || empty($data['Dueno'])) {
                return $this->fail('El nombre y el dueño son obligatorios', 400);
            }

            // Manejar el archivo de logo
            $logoFile = $this->request->getFile('logo');
            if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
                // Validar que sea una imagen
                if (!$logoFile->getClientMimeType() || strpos($logoFile->getClientMimeType(), 'image/') !== 0) {
                    return $this->fail('El archivo debe ser una imagen', 400);
                }
                
                // Validar tamaño (2MB)
                if ($logoFile->getSize() > 2 * 1024 * 1024) {
                    return $this->fail('El logo no debe superar los 2MB', 400);
                }
                
                // Leer el archivo y convertirlo a binario
                $data['logo'] = file_get_contents($logoFile->getTempName());
            }

            $id = $this->model->insert($data);

            if ($id) {
                // Crear roles fijos por defecto para esta maquiladora
                try {
                    $db = \Config\Database::connect();
                    $rolesData = [
                        [
                            'maquiladoraID' => (int) $id,
                            'nombre'        => 'Jefe',
                            'descripcion'   => 'Acceso completo al sistema.',
                            'es_fijo'       => 1,
                        ],
                        [
                            'maquiladoraID' => (int) $id,
                            'nombre'        => 'Administrador',
                            'descripcion'   => 'Puede operar máquinas y registrar datos',
                            'es_fijo'       => 1,
                        ],
                        [
                            'maquiladoraID' => (int) $id,
                            'nombre'        => 'Empleado',
                            'descripcion'   => 'Trabajador',
                            'es_fijo'       => 1,
                        ],
                        [
                            'maquiladoraID' => (int) $id,
                            'nombre'        => 'Corte',
                            'descripcion'   => 'A recortar la tela para la costura',
                            'es_fijo'       => 1,
                        ],
                    ];

                    $db->table('rol')->insertBatch($rolesData);
                } catch (\Throwable $e) {
                    // Si falla la creación de roles, registramos el error pero no impedimos la creación de la maquiladora
                    log_message('error', 'Error al crear roles por defecto para maquiladora ' . $id . ': ' . $e->getMessage());
                }

                return $this->respondCreated([
                    'success' => true,
                    'message' => 'Maquiladora creada exitosamente',
                    'id' => $id
                ]);
            } else {
                return $this->fail('No se pudo crear la maquiladora', 400);
            }
        } catch (\Exception $e) {
            return $this->failServerError('Error al crear la maquiladora: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar una maquiladora existente
     */
    public function actualizar()
    {
        try {
            $id = $this->request->getPost('idmaquiladora');
            
            if (!$id) {
                return $this->fail('ID de maquiladora no proporcionado', 400);
            }

            $maquiladora = $this->model->find($id);
            if (!$maquiladora) {
                return $this->failNotFound('Maquiladora no encontrada');
            }

            $data = [
                'Nombre_Maquila' => $this->request->getPost('Nombre_Maquila'),
                'Dueno'          => $this->request->getPost('Dueno'),
                'Telefono'       => $this->request->getPost('Telefono'),
                'Correo'         => $this->request->getPost('Correo'),
                'Domicilio'      => $this->request->getPost('Domicilio'),
                'tipo'           => $this->request->getPost('tipo'),
                'status'         => $this->request->getPost('status'),
            ];

            // Validar campos requeridos
            if (empty($data['Nombre_Maquila']) || empty($data['Dueno'])) {
                return $this->fail('El nombre y el dueño son obligatorios', 400);
            }

            // Manejar el archivo de logo si se subió uno nuevo
            $logoFile = $this->request->getFile('logo');
            if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
                // Validar que sea una imagen
                if (!$logoFile->getClientMimeType() || strpos($logoFile->getClientMimeType(), 'image/') !== 0) {
                    return $this->fail('El archivo debe ser una imagen', 400);
                }
                
                // Validar tamaño (2MB)
                if ($logoFile->getSize() > 2 * 1024 * 1024) {
                    return $this->fail('El logo no debe superar los 2MB', 400);
                }
                
                // Leer el archivo y convertirlo a binario
                $data['logo'] = file_get_contents($logoFile->getTempName());
            }

            $updated = $this->model->update($id, $data);
            
            if ($updated) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Maquiladora actualizada exitosamente'
                ]);
            } else {
                return $this->fail('No se pudo actualizar la maquiladora', 400);
            }
        } catch (\Exception $e) {
            return $this->failServerError('Error al actualizar la maquiladora: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una maquiladora
     */
    public function eliminar($id = null)
    {
        try {
            if (!$id) {
                return $this->fail('ID de maquiladora no proporcionado', 400);
            }

            $maquiladora = $this->model->find($id);
            if (!$maquiladora) {
                return $this->failNotFound('Maquiladora no encontrada');
            }

            $deleted = $this->model->delete($id);
            
            if ($deleted) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Maquiladora eliminada exitosamente'
                ]);
            } else {
                return $this->fail('No se pudo eliminar la maquiladora', 400);
            }
        } catch (\Exception $e) {
            return $this->failServerError('Error al eliminar la maquiladora: ' . $e->getMessage());
        }
    }

    /**
     * Listar usuarios de una maquiladora (por maquiladoraIdFK)
     */
    public function usuarios($id = null)
    {
        try {
            if (!$id) {
                return $this->fail('ID de maquiladora no proporcionado', 400);
            }

            $db = \Config\Database::connect();

            $usuarios = $db->table('users u')
                ->select('u.*, r.id AS rol_id, r.nombre AS rol_nombre')
                ->join('usuario_rol ur', 'ur.usuarioIdFK = u.id', 'left')
                ->join('rol r', 'r.id = ur.rolIdFK', 'left')
                ->where('u.maquiladoraIdFK', (int) $id)
                ->get()
                ->getResultArray();

            return $this->respond($usuarios);
        } catch (\Exception $e) {
            return $this->failServerError('Error al obtener los usuarios de la maquiladora: ' . $e->getMessage());
        }
    }

    /**
     * Listar roles de una maquiladora
     */
    public function roles($id = null)
    {
        try {
            if (!$id) {
                return $this->fail('ID de maquiladora no proporcionado', 400);
            }

            $db = \Config\Database::connect();

            $roles = $db->table('rol')
                ->where('maquiladoraID', (int) $id)
                ->orderBy('nombre', 'ASC')
                ->get()
                ->getResultArray();

            return $this->respond($roles);
        } catch (\Exception $e) {
            return $this->failServerError('Error al obtener los roles de la maquiladora: ' . $e->getMessage());
        }
    }

    /**
     * Crear un nuevo usuario para una maquiladora
     */
    public function crearUsuario($maquiladoraId = null)
    {
        try {
            if (!$maquiladoraId) {
                return $this->fail('ID de maquiladora no proporcionado', 400);
            }

            $username = trim((string) $this->request->getPost('username'));
            $correo   = trim((string) $this->request->getPost('correo'));
            $active   = $this->request->getPost('active');
            $rolId    = $this->request->getPost('rolId');

            if ($username === '' || $correo === '') {
                return $this->fail('Nombre y correo son obligatorios', 400);
            }

            $active = (int) $active === 0 ? 0 : 1;

            $userModel = new UserModel();

            $userData = [
                'username'        => $username,
                'correo'          => $correo,
                'maquiladoraIdFK' => (int) $maquiladoraId,
                'active'          => $active,
                'password'        => '123456',
            ];

            $userId = $userModel->insert($userData);

            if (!$userId) {
                return $this->fail('No se pudo crear el usuario', 400);
            }

            // Asignar rol si se proporcionó
            if (!empty($rolId)) {
                $db      = \Config\Database::connect();
                $builder = $db->table('usuario_rol');

                $builder->insert([
                    'usuarioIdFK'   => (int) $userId,
                    'rolIdFK'       => (int) $rolId,
                    'maquiladoraID' => (int) $maquiladoraId,
                ]);
            }

            return $this->respondCreated([
                'success'        => true,
                'message'        => 'Usuario creado correctamente',
                'id'             => (int) $userId,
                'maquiladoraId'  => (int) $maquiladoraId,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar el status (active) de un usuario
     */
    public function actualizarUsuarioStatus($id = null)
    {
        try {
            if (!$id) {
                return $this->fail('ID de usuario no proporcionado', 400);
            }

            $active = $this->request->getPost('active');

            if ($active === null) {
                return $this->fail('Valor de status no proporcionado', 400);
            }

            $active = (int) $active === 1 ? 1 : 0;

            $userModel = new UserModel();
            $usuario   = $userModel->find($id);

            if (!$usuario) {
                return $this->failNotFound('Usuario no encontrado');
            }

            $updated = $userModel->update($id, ['active' => $active]);

            if (!$updated) {
                return $this->fail('No se pudo actualizar el status del usuario', 400);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Status de usuario actualizado correctamente',
                'id'      => (int) $id,
                'active'  => $active,
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Error al actualizar el status del usuario: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar el rol de un usuario (tabla usuario_rol)
     */
    public function actualizarUsuarioRol($id = null)
    {
        try {
            if (!$id) {
                return $this->fail('ID de usuario no proporcionado', 400);
            }

            $rolId         = $this->request->getPost('rolId');
            $maquiladoraId = $this->request->getPost('maquiladoraId');

            if (!$maquiladoraId) {
                return $this->fail('Maquiladora no proporcionada', 400);
            }

            $db      = \Config\Database::connect();
            $builder = $db->table('usuario_rol');

            // Eliminar cualquier relación previa para este usuario y maquiladora
            $builder
                ->where('usuarioIdFK', (int) $id)
                ->where('maquiladoraID', (int) $maquiladoraId)
                ->delete();

            // Si se envió un rolId válido, insertar la nueva relación
            if (!empty($rolId)) {
                $builder->insert([
                    'usuarioIdFK'   => (int) $id,
                    'rolIdFK'       => (int) $rolId,
                    'maquiladoraID' => (int) $maquiladoraId,
                ]);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Rol de usuario actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Error al actualizar el rol del usuario: ' . $e->getMessage());
        }
    }
}

