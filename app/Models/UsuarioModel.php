<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'username',
        'correo',
        'password',
        'maquiladoraIdFK',
        'status',
        'status_message',
        'active',
        'last_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Desactivar protección de campos para inserciones masivas
    protected $allowCallbacks = true;

    // Configuración de fechas
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $dateFormat    = 'datetime';
    
    // Callbacks para el manejo de fechas y contraseñas
    protected $beforeInsert = ['setTimestamps', 'hashPassword'];
    protected $beforeUpdate = ['updateTimestamps', 'hashPassword'];
  
    /**
     * Establece las marcas de tiempo al insertar
     */
    protected function setTimestamps(array $data)
    {
        $currentDate = date('Y-m-d H:i:s');
        $data['data']['created_at'] = $currentDate;
        $data['data']['updated_at'] = $currentDate;
        return $data;
    }
    
    /**
     * Actualiza la marca de tiempo al actualizar
     */
    protected function updateTimestamps(array $data)
    {
        $data['data']['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }
    
    /**
     * Hashea la contraseña antes de insertar/actualizar
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT);
        }
        return $data;
    }

    // Desactivar protección de campos para inserciones masivas
    protected $allowCallbacks = true;

    // Reglas de validación
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
        'correo' => 'required|valid_email|is_unique[users.correo]',
        'password' => 'required|min_length[6]',
        'maquiladoraIdFK' => 'required|integer',
        'active' => 'permit_empty|in_list[0,1]',
        'status' => 'permit_empty|in_list[0,1]'
        'maquiladoraIdFK' => 'required|integer'
    ];

    protected $validationMessages = [
        'username' => [
            'required'   => 'El nombre de usuario es obligatorio.',
            'min_length' => 'Debe tener al menos 3 caracteres.',
            'max_length' => 'No puede superar los 30 caracteres.',
        ],
        'correo' => [
            'required'    => 'El correo es obligatorio.',
            'valid_email' => 'El formato del correo no es válido.',
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria.',
            'min_length' => 'Debe tener al menos 6 caracteres.',
        ],
        'maquiladoraIdFK' => [
            'required' => 'Debe seleccionar una maquiladora',
            'integer'  => 'La maquiladora seleccionada no es válida'
        ]
    ];


    /**
     * Autenticación de usuario
     */
    public function authenticate($email, $password)
    {
        // Buscar el usuario por correo
        $user = $this->where('correo', $email)
                    ->where('active', 1)
                    ->first();

        if (!$user) {
            return false;
        }

        // Verificar la contraseña
        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Eliminar la contraseña del array antes de devolver
        unset($user['password']);
        return $user;
    }

    /**
     * Busca usuarios por nombre o correo
     */
    public function buscarUsuarios($termino)
    {
        return $this->groupStart()
            ->like('username', $termino)
            ->orLike('correo', $termino)
            ->groupEnd()
            ->findAll();
    }
}
