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

    // Fechas automáticas
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Reglas de validación básicas
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[30]',
        'correo'   => 'required|valid_email',
        'password' => 'required|min_length[6]',
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
    ];

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    /**
     * Hashea la contraseña antes de guardar
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Autenticación de usuario
     */
    public function authenticate($correo, $password)
    {
        $user = $this->where('correo', $correo)->first();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
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
