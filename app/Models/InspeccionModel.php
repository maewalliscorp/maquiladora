<?php
namespace App\Models;

use CodeIgniter\Model;

class InspeccionModel extends Model
{
    protected $table         = 'inspeccion';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    // NO requerimos numero_inspeccion en la BD
    protected $allowedFields = [
        // 'numero_inspeccion',  // <- la omitimos por ahora
        'ordenProduccionId',
        'puntoInspeccionId',
        'inspectorId',
        'fecha',
        'resultado',
        'observaciones'
    ];

    protected $validationRules = [
        'ordenProduccionId' => 'permit_empty|integer',
        'puntoInspeccionId' => 'permit_empty|integer',
        'inspectorId'       => 'permit_empty|integer',
        'fecha'             => 'required|valid_date[Y-m-d]',
        'resultado'         => 'required|string|min_length[3]',
        'observaciones'     => 'permit_empty|string',
        // 'numero_inspeccion' => 'permit_empty|string'
    ];

    /** Detecta si la columna existe en la tabla (para que el modelo sea tolerante) */
    private function hasNumeroInspeccion(): bool
    {
        $fields = $this->db->getFieldNames($this->table);
        return in_array('numero_inspeccion', $fields, true);
    }

    /** Genera el siguiente folio SOLO si la columna existe; si no, devuelve string vacío */
    public function generarNumeroInspeccion(): string
    {
        if (!$this->hasNumeroInspeccion()) {
            return ''; // omitimos por completo
        }

        $prefix = 'INSP-' . date('Y') . '-';
        $last = $this->like('numero_inspeccion', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->first();

        if ($last && !empty($last['numero_inspeccion'])) {
            $lastNum = (int) str_replace($prefix, '', $last['numero_inspeccion']);
            return $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    /** Crea inspección; solo guarda numero_inspeccion si la columna existe */
    public function crearInspeccion(array $data): int
    {
        if ($this->hasNumeroInspeccion()) {
            $folio = $this->generarNumeroInspeccion();
            if ($folio !== '') {
                $data['numero_inspeccion'] = $folio;
                // Si agregas la columna en el futuro, recuerda también
                // añadir 'numero_inspeccion' a $allowedFields.
            }
        }

        $this->insert($data, true); // true => retorna ID
        return (int) $this->getInsertID();
    }

    // --- Tus métodos de listado/detalle pueden quedarse igual,
    //     pero SIN asumir que existe numero_inspeccion. ---

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

        $resultados = [];
        foreach ($inspecciones as $inspeccion) {
            $resultados[] = [
                'id' => $inspeccion['id'],
                // Si la columna no existe, no intentes mostrarla:
                // 'numero_inspeccion' => $inspeccion['numero_inspeccion'] ?? null,
                'orden_produccion' => $inspeccion['orden_produccion'],
                'punto_inspeccion' => $inspeccion['punto_inspeccion'],
                'inspector'        => $inspeccion['inspector'],
                'fecha'            => $inspeccion['fecha'],
                'resultado'        => $inspeccion['resultado'],
                'observaciones'    => $inspeccion['observaciones']
            ];
        }
        return $resultados;
    }

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
        if (!$inspeccion) return null;

        $defectos = $db->table('inspeccion_defecto id')
            ->select('d.id, d.nombre as tipo, d.descripcion as descripcion_defecto, 
                      id.descripcion, id.cantidad, id.accion_correctiva, id.fecha_registro')
            ->join('defecto d', 'd.id = id.defecto_id')
            ->where('id.inspeccion_id', $id)
            ->get()->getResultArray();

        return [
            'id'               => $inspeccion['id'],
            // 'numero_inspeccion' => $inspeccion['numero_inspeccion'] ?? null,
            'orden_produccion' => $inspeccion['orden_produccion'],
            'punto_inspeccion' => $inspeccion['punto_inspeccion'],
            'inspector'        => $inspeccion['inspector'],
            'fecha'            => $inspeccion['fecha'],
            'resultado'        => $inspeccion['resultado'],
            'observaciones'    => $inspeccion['observaciones'],
            'defectos'         => $defectos
        ];
    }
}
