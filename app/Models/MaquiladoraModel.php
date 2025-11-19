<?php

namespace App\Models;

use CodeIgniter\Model;

class MaquiladoraModel extends Model
{
    protected $table = 'maquiladora';
    protected $primaryKey = 'idmaquiladora';
    protected $allowedFields = [
        'Nombre_Maquila',
        'Dueno',
        'Telefono',
        'Correo',
        'Domicilio',
        'tipo',
        'status',
        'logo'
    ];
}
