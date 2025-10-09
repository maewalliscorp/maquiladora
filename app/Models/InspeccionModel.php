<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionModel extends Model
{
    protected $table         = 'inspeccion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ordenProduccionId','puntoInspeccionId','inspectorId',
        'fecha','resultado','observaciones'
    ];

    protected $validationRules = [
        'ordenProduccionId' => 'required|integer',
        'fecha'             => 'required|valid_date',
        'resultado'         => 'required|string',
        'observaciones'     => 'permit_empty|string'
    ];

    /**
     * Devuelve listado para la vista de inspección.
     * Fallback simple: solo tabla inspeccion ordenada por fecha desc.
     */
    public function getListado(): array
    {
        try {
            return $this->orderBy('fecha', 'DESC')->findAll();
        } catch (\Throwable $e) {
            // En caso de error de tabla/consulta, devolver arreglo vacío
            return [];
        }
    }
}
