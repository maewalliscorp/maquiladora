<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'username',
        'correo',
        'password',
        'maquiladoraIdFK',
        'status',
        'active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true; // auto-manage created_at/updated_at

    // Register callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    // Hash password on insert/update
    protected function hashPassword(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }
        if (array_key_exists('password', $data['data'])) {
            $pwd = (string)$data['data']['password'];
            if ($pwd !== '') {
                $data['data']['password'] = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 10]);
            } else {
                unset($data['data']['password']);
            }
        }
        return $data;
    }
}
