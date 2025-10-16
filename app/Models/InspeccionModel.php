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
        'ordenProduccionId',
        'puntoInspeccionId',
        'inspectorId',
        'fecha',
        'resultado',
        'observaciones'
    ];

    /**
     * Obtiene el listado de inspecciones con datos relacionados
     */
    public function getListadoCompleto(): array
    {
        return $this->select([
            'i.id',
            'i.ordenProduccionId',
            'i.puntoInspeccionId',
            'i.inspectorId',
            'i.fecha',
            'i.resultado',
            'i.observaciones'
        ])
        ->from('inspeccion i')
        ->orderBy('i.fecha', 'DESC')
        ->orderBy('i.id', 'DESC')
        ->get()
        ->getResultArray();
    }

    /**
     * Obtiene los detalles de una inspección específica
     */
    public function getDetalle($id): ?array
    {
        return $this->select([
            'i.id',
            'i.ordenProduccionId',
            'i.puntoInspeccionId',
            'i.inspectorId',
            'i.fecha',
            'i.resultado',
            'i.observaciones'
        ])
        ->from('inspeccion i')
        ->where('i.id', $id)
        ->get()
        ->getRowArray();
    }

    /**
     * Genera el siguiente número de inspección
     */
    public function generarNumeroInspeccion(): string
    {
        $prefix = 'INSP-' . date('Y') . '-';
        $last = $this->like('numero_inspeccion', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last) {
            $lastNum = (int) str_replace($prefix, '', $last['numero_inspeccion']);
            return $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    /**
     * Crea una nueva inspección
     */
    public function crearInspeccion(array $data): bool
    {
        $data['numero_inspeccion'] = $this->generarNumeroInspeccion();
        return $this->insert($data);
    }

    /**
     * Actualiza una inspección existente
     */
    public function actualizarInspeccion(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Elimina una inspección
     */
    public function eliminarInspeccion(int $id): bool
    {
        return $this->delete($id);
    }
}
