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
        $db = \Config\Database::connect();
        $builder = $db->table('inspeccion i')
            ->select('i.*, pi.tipo as punto_inspeccion, u.nombre as inspector, o.numero as orden_produccion')
            ->join('punto_inspeccion pi', 'pi.id = i.puntoInspeccionId', 'left')
            ->join('usuarios u', 'u.id = i.inspectorId', 'left')
            ->join('orden_produccion o', 'o.id = i.ordenProduccionId', 'left')
            ->orderBy('i.fecha', 'DESC')
            ->orderBy('i.id', 'DESC');

        $inspecciones = $builder->get()->getResultArray();

        // Formatear los datos para la respuesta
        $resultados = [];
        foreach ($inspecciones as $inspeccion) {
            $resultados[] = [
                'id' => $inspeccion['id'],
                'numero_inspeccion' => 'INSP-' . str_pad($inspeccion['id'], 5, '0', STR_PAD_LEFT),
                'orden_produccion' => $inspeccion['orden_produccion'],
                'punto_inspeccion' => $inspeccion['punto_inspeccion'],
                'inspector' => $inspeccion['inspector'],
                'fecha' => $inspeccion['fecha'],
                'resultado' => $inspeccion['resultado'],
                'observaciones' => $inspeccion['observaciones']
            ];
        }

        return $resultados;
    }

    /**
     * Obtiene los detalles de una inspección específica
     */
    public function getDetalle($id): ?array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('inspeccion i')
            ->select('i.*, pi.tipo as punto_inspeccion, u.nombre as inspector, o.numero as orden_produccion')
            ->join('punto_inspeccion pi', 'pi.id = i.puntoInspeccionId', 'left')
            ->join('usuarios u', 'u.id = i.inspectorId', 'left')
            ->join('orden_produccion o', 'o.id = i.ordenProduccionId', 'left')
            ->where('i.id', $id);

        $inspeccion = $builder->get()->getRowArray();

        if ($inspeccion) {
            // Obtener los defectos de la inspección
            $defectos = $db->table('inspeccion_defecto id')
                ->select('d.id, d.nombre as tipo, d.descripcion as descripcion_defecto, 
                         id.descripcion, id.cantidad, id.accion_correctiva, id.fecha_registro')
                ->join('defecto d', 'd.id = id.defecto_id')
                ->where('id.inspeccion_id', $id)
                ->get()
                ->getResultArray();

            $inspeccion['defectos'] = $defectos;

            // Formatear los datos para la respuesta
            return [
                'id' => $inspeccion['id'],
                'numero_inspeccion' => 'INSP-' . str_pad($inspeccion['id'], 5, '0', STR_PAD_LEFT),
                'orden_produccion' => $inspeccion['orden_produccion'],
                'punto_inspeccion' => $inspeccion['punto_inspeccion'],
                'inspector' => $inspeccion['inspector'],
                'fecha' => $inspeccion['fecha'],
                'resultado' => $inspeccion['resultado'],
                'observaciones' => $inspeccion['observaciones'],
                'defectos' => $defectos
            ];
        }

        return null;
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
