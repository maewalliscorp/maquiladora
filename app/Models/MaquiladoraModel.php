<?php

namespace App\Models;

use CodeIgniter\Model;

class MaquiladoraModel extends Model
{
    protected $table            = 'maquiladora';
    protected $primaryKey       = 'idmaquiladora';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'Nombre_Maquila',
        'Dueno',
        'Telefono',
        'Correo',
        'activa',
        'fechaCreacion',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'Nombre_Maquila' => 'required|min_length[2]|max_length[100]',
        'Dueno'          => 'permit_empty|max_length[100]',
        'Telefono'       => 'permit_empty|max_length[20]',
        'Correo'         => 'permit_empty|valid_email|max_length[100]',
        'activa'         => 'required|in_list[0,1]',
        'fechaCreacion'  => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'Nombre_Maquila' => [
            'required'   => 'El nombre de la maquiladora es obligatorio',
            'min_length' => 'El nombre debe tener al menos 2 caracteres',
            'max_length' => 'El nombre no puede superar los 100 caracteres',
        ],
        'Dueno' => [
            'max_length' => 'El dueño no puede superar los 100 caracteres',
        ],
        'Telefono' => [
            'max_length' => 'El teléfono no puede superar los 20 caracteres',
        ],
        'Correo' => [
            'valid_email' => 'El correo debe tener un formato válido',
            'max_length'  => 'El correo no puede superar los 100 caracteres',
        ],
        'activa' => [
            'required' => 'El campo activa es obligatorio',
            'in_list'  => 'El valor de activa debe ser 0 o 1',
        ],
        'fechaCreacion' => [
            'valid_date' => 'La fecha de creación no es válida',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    /**
     * Obtiene todas las maquiladoras activas
     */
    public function getMaquiladorasActivas()
    {
        return $this->where('activa', 1)->findAll();
    }

    /**
     * Obtiene todas las maquiladoras ordenadas por nombre
     */
    public function getMaquiladorasOrdenadas()
    {
        return $this->orderBy('Nombre_Maquila', 'ASC')->findAll();
    }

    /**
     * Obtiene una maquiladora por ID
     */
    public function getMaquiladoraPorId($id)
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Busca maquiladoras por nombre
     */
    public function buscarMaquiladoras($termino)
    {
        return $this->like('Nombre_Maquila', $termino)
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->findAll();
    }
}
