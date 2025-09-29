<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuario';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'usuario',
        'password',
        'activo',
        'fechaAlta',
        'ultimoAcceso',
        'idMaquiladora',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'usuario'       => 'required|min_length[3]|max_length[100]',
        'password'      => 'required|min_length[6]',
        'activo'        => 'required|in_list[0,1]', // estado: 0 = inactivo, 1 = activo
        'fechaAlta'     => 'permit_empty|valid_date',
        'ultimoAcceso'  => 'permit_empty|valid_date',
        'idMaquiladora' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'usuario' => [
            'required'   => 'El usuario es obligatorio',
            'min_length' => 'El usuario debe tener al menos 3 caracteres',
            'max_length' => 'El usuario no puede superar los 100 caracteres',
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria',
            'min_length' => 'La contraseña debe tener al menos 6 caracteres',
        ],
        'activo' => [
            'required' => 'El campo activo es obligatorio',
            'in_list'  => 'El valor de activo debe ser 0 o 1',
        ],
        'fechaAlta' => [
            'valid_date' => 'La fecha de alta no es válida',
        ],
        'ultimoAcceso' => [
            'valid_date' => 'La fecha de último acceso no es válida',
        ],
        'idMaquiladora' => [
            'integer'  => 'El id de la maquiladora debe ser un número entero',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function authenticate($usuario, $password)
    {
        $user = $this->where('usuario', $usuario)->first();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
