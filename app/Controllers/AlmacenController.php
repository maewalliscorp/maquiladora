<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarioModel;

class AlmacenController extends BaseController
{
    protected InventarioModel $inv;

    public function __construct()
    {
        $this->inv = new InventarioModel();
    }

    // Vista principal
    public function inventario()
    {
        $data = [
            'title'     => 'Inventario de Almacenes',
            'almacenes' => $this->inv->obtenerAlmacenesActivos(),
        ];
        return view('modulos/almacen_inventario', $data); // carpeta "modulos"
    }

    // API: combo de almacenes
    public function apiAlmacenes()
    {
        return $this->response->setJSON(['data' => $this->inv->obtenerAlmacenesActivos()]);
    }

    // API: inventario (para DataTables)
    public function apiInventario()
    {
        $almacenId = $this->request->getGet('almacenId');
        $rows      = $this->inv->obtenerInventario($almacenId ? (int)$almacenId : null);
        return $this->response->setJSON(['data' => $rows]);
    }

    // NUEVO: ubicaciones por almacén (para el modal Editar)
    public function apiUbicaciones()
    {
        $almacenId = (int) ($this->request->getGet('almacenId') ?? 0);
        return $this->response->setJSON(['data' => $this->inv->obtenerUbicacionesActivas($almacenId)]);
    }

    // NUEVO: lotes del artículo (para la tabla de "Ver")
    public function apiLotes()
    {
        $articuloId  = (int) ($this->request->getGet('articuloId') ?? 0);
        $almacenId   = $this->request->getGet('almacenId');
        $ubicacionId = $this->request->getGet('ubicacionId');

        if (!$articuloId) return $this->response->setJSON(['data' => []]);

        $rows = $this->inv->obtenerLotesArticulo(
            $articuloId,
            $almacenId   !== null && $almacenId   !== '' ? (int)$almacenId : null,
            $ubicacionId !== null && $ubicacionId !== '' ? (int)$ubicacionId : null
        );
        return $this->response->setJSON(['data' => $rows]);
    }

    // NUEVO: editar datos de la fila (stock + articulo + lote)
    public function apiEditar()
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($payload['stockId']) || empty($payload['articuloId'])) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'Faltan IDs']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Artículo
        $art = [];
        foreach (['articuloNombre'=>'nombre','unidadMedida'=>'unidadMedida','stockMin'=>'stockMin','stockMax'=>'stockMax'] as $in => $col) {
            if (array_key_exists($in, $payload)) $art[$col] = $payload[$in];
        }
        if (!empty($art)) $db->table('articulo')->update($art, ['id' => (int)$payload['articuloId']]);

        // Lote
        $loteId = !empty($payload['loteId']) ? (int)$payload['loteId'] : null;
        $loteData = [];
        foreach (['loteCodigo'=>'codigo','fechaFabricacion'=>'fechaFabricacion','fechaCaducidad'=>'fechaCaducidad','loteNotas'=>'notas'] as $in => $col) {
            if (array_key_exists($in, $payload)) $loteData[$col] = $payload[$in] ?: null;
        }
        if ($loteId) {
            if (!empty($loteData)) $db->table('lote')->update($loteData, ['id'=>$loteId]);
        } elseif (!empty($loteData['codigo'])) {
            $loteData['articuloId'] = (int)$payload['articuloId'];
            $db->table('lote')->insert($loteData);
            $loteId = (int)$db->insertID();
        }

        // Stock
        $stockSet = [];
        if (array_key_exists('cantidad', $payload))   $stockSet['cantidad']    = $payload['cantidad'];
        if (array_key_exists('ubicacionId', $payload) && $payload['ubicacionId'] !== '')
            $stockSet['ubicacionId'] = (int)$payload['ubicacionId'];
        if ($loteId) $stockSet['loteId'] = $loteId;

        if (!empty($stockSet)) $db->table('stock')->update($stockSet, ['id' => (int)$payload['stockId']]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'No se pudo guardar']);
        }
        return $this->response->setJSON(['ok'=>true,'message'=>'Guardado', 'loteId'=>$loteId]);
    }

    // Historial (opcional)
    public function apiMovimientos($articuloId)
    {
        $loteId      = $this->request->getGet('loteId');
        $ubicacionId = $this->request->getGet('ubicacionId');
        $data        = $this->inv->obtenerMovimientos((int)$articuloId,
            $loteId ? (int)$loteId : null,
            $ubicacionId ? (int)$ubicacionId : null);
        return $this->response->setJSON(['data' => $data]);
    }
}
