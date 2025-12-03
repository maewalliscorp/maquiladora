<?php

namespace App\Models;

use CodeIgniter\Model;

class PlantillaOperacionModel extends Model
{
    protected $table = 'plantillas_operaciones';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'idmaquiladora',
        'tipo_prenda',
        'nombre_plantilla',
        'operaciones',
        'activo'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Obtener plantillas por maquiladora
     */
    public function getPlantillasPorMaquiladora($maquiladoraId, $soloActivas = true)
    {
        $builder = $this->where('idmaquiladora', $maquiladoraId);

        if ($soloActivas) {
            $builder->where('activo', 1);
        }

        return $builder->orderBy('tipo_prenda', 'ASC')
            ->orderBy('nombre_plantilla', 'ASC')
            ->findAll();
    }

    /**
     * Obtener operaciones de una plantilla (decodificadas)
     */
    public function getOperacionesPorTipo($tipoPrenda, $maquiladoraId)
    {
        $plantilla = $this->where('tipo_prenda', $tipoPrenda)
            ->where('idmaquiladora', $maquiladoraId)
            ->where('activo', 1)
            ->first();

        if (!$plantilla) {
            return [];
        }

        return json_decode($plantilla['operaciones'], true) ?? [];
    }

    /**
     * Crear plantilla con operaciones
     */
    public function crearPlantilla($data)
    {
        // Validar que operaciones sea un array
        if (isset($data['operaciones']) && is_array($data['operaciones'])) {
            $data['operaciones'] = json_encode($data['operaciones']);
        }

        return $this->insert($data);
    }

    /**
     * Actualizar plantilla
     */
    public function actualizarPlantilla($id, $data)
    {
        // Validar que operaciones sea un array
        if (isset($data['operaciones']) && is_array($data['operaciones'])) {
            $data['operaciones'] = json_encode($data['operaciones']);
        }

        return $this->update($id, $data);
    }

    /**
     * Obtener tipos de prenda únicos
     */
    public function getTiposPrenda($maquiladoraId)
    {
        return $this->select('DISTINCT tipo_prenda')
            ->where('idmaquiladora', $maquiladoraId)
            ->where('activo', 1)
            ->orderBy('tipo_prenda', 'ASC')
            ->findAll();
    }

    /**
     * Duplicar plantilla
     */
    public function duplicarPlantilla($plantillaId, $nuevoNombre = null)
    {
        $plantilla = $this->find($plantillaId);

        if (!$plantilla) {
            return false;
        }

        $nuevaPlantilla = $plantilla;
        unset($nuevaPlantilla['id']);
        unset($nuevaPlantilla['created_at']);
        unset($nuevaPlantilla['updated_at']);

        if ($nuevoNombre) {
            $nuevaPlantilla['nombre_plantilla'] = $nuevoNombre;
        } else {
            $nuevaPlantilla['nombre_plantilla'] .= ' (Copia)';
        }

        return $this->insert($nuevaPlantilla);
    }
}
