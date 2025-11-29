<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

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
}

