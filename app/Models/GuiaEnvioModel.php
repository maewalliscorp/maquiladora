<?php
namespace App\Models;

use CodeIgniter\Model;

class GuiaEnvioModel extends Model
{
    protected $table         = 'guia_envio';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    public    $tableName     = 'guia_envio'; // útil para introspección en el controlador
    protected $allowedFields = [
        'embarqueId',
        'transportistaId',
        'numeroGuia',
        'urlSeguimiento',
        // Si en tu tabla existen, se guardarán; si no existen, el controlador los filtrará
        'fechaSalida',
        'estado',
        'maquiladoraID',
    ];
    public $useTimestamps = false;
}
