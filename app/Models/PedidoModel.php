<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'empresa',
        'contacto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'total',
        'fecha_creacion',
        'fecha_entrega',
        'estatus',
        'prioridad',
        'observaciones',
        'usuario_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'empresa' => 'required|max_length[100]',
        'contacto' => 'required|max_length[100]',
        'descripcion' => 'required|max_length[500]',
        'cantidad' => 'required|integer|greater_than[0]',
        'precio_unitario' => 'required|decimal|greater_than[0]',
        'fecha_entrega' => 'required|valid_date',
        'estatus' => 'required|in_list[Pendiente,En proceso,Completado,Cancelado]',
        'prioridad' => 'required|in_list[Baja,Media,Alta,Urgente]',
        'observaciones' => 'permit_empty|max_length[1000]',
        'usuario_id' => 'required|integer'
    ];

    protected $validationMessages = [
        'empresa' => [
            'required' => 'La empresa es obligatoria',
            'max_length' => 'El nombre de la empresa no puede exceder 100 caracteres'
        ],
        'cantidad' => [
            'required' => 'La cantidad es obligatoria',
            'integer' => 'La cantidad debe ser un número entero',
            'greater_than' => 'La cantidad debe ser mayor a 0'
        ],
        'precio_unitario' => [
            'required' => 'El precio unitario es obligatorio',
            'decimal' => 'El precio debe ser un número decimal',
            'greater_than' => 'El precio debe ser mayor a 0'
        ],
        'estatus' => [
            'required' => 'El estatus es obligatorio',
            'in_list' => 'El estatus debe ser Pendiente, En proceso, Completado o Cancelado'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['calculateTotal'];
    protected $beforeUpdate = ['calculateTotal'];

    protected function calculateTotal(array $data)
    {
        if (isset($data['data']['cantidad']) && isset($data['data']['precio_unitario'])) {
            $data['data']['total'] = $data['data']['cantidad'] * $data['data']['precio_unitario'];
        }
        return $data;
    }

    public function getPedidosByEstatus($estatus)
    {
        return $this->where('estatus', $estatus)->findAll();
    }

    public function getPedidosByUsuario($usuario_id)
    {
        return $this->where('usuario_id', $usuario_id)->findAll();
    }

    public function getPedidosVencidos()
    {
        return $this->where('fecha_entrega <', date('Y-m-d'))
                    ->where('estatus !=', 'Completado')
                    ->findAll();
    }
}
