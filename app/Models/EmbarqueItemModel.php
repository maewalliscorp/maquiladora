<?php

namespace App\Models;

use CodeIgniter\Model;

class EmbarqueItemModel extends Model
{
    protected $table      = 'embarque_item';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Solo las columnas seguras que sabemos que existen
    protected $allowedFields = [
        'maquiladoraID',
        'embarqueId',
        'ordenCompraId',
        // Si en tu tabla tienes más columnas (ordenCompraItemId, productoId, etc.)
        // y quieres manejarlas, las agregas aquí luego.
    ];
}
