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

    /* ===== VISTA ===== */
    public function inventario()
    {
        $data = [
            'title'     => 'Inventario de Almacenes',
            'almacenes' => $this->inv->obtenerAlmacenesActivos(),
        ];
        return view('modulos/almacen_inventario', $data);
    }

    /* ===== CATÁLOGOS ===== */
    public function apiAlmacenes()
    {
        return $this->response->setJSON(['data'=>$this->inv->obtenerAlmacenesActivos()]);
    }

    public function apiUbicaciones()
    {
        $almacenId = (int) ($this->request->getGet('almacenId') ?? 0);
        return $this->response->setJSON(['data'=>$this->inv->obtenerUbicacionesActivas($almacenId)]);
    }

    /* ===== INVENTARIO ===== */
    public function apiInventario()
    {
        $almacenId = $this->request->getGet('almacenId');
        return $this->response->setJSON(['data'=>$this->inv->obtenerInventario($almacenId ? (int)$almacenId : null)]);
    }

    public function apiLotes()
    {
        $articuloId  = (int) ($this->request->getGet('articuloId') ?? 0);
        $almacenId   = $this->request->getGet('almacenId');
        $ubicacionId = $this->request->getGet('ubicacionId');
        if (!$articuloId) return $this->response->setJSON(['data'=>[]]);

        $rows = $this->inv->obtenerLotesArticulo(
            $articuloId,
            $almacenId   !== null && $almacenId   !== '' ? (int)$almacenId : null,
            $ubicacionId !== null && $ubicacionId !== '' ? (int)$ubicacionId : null
        );
        return $this->response->setJSON(['data'=>$rows]);
    }

    public function apiMovimientos($articuloId)
    {
        $loteId      = $this->request->getGet('loteId');
        $ubicacionId = $this->request->getGet('ubicacionId');
        $data = $this->inv->obtenerMovimientos((int)$articuloId, $loteId ? (int)$loteId : null, $ubicacionId ? (int)$ubicacionId : null);
        return $this->response->setJSON(['data'=>$data]);
    }

    /* ===== EDITAR ===== */
    public function apiEditar()
    {
        $in = $this->request->getJSON(true) ?: $this->request->getPost();
        if (empty($in['stockId']) || empty($in['articuloId'])) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'Faltan IDs']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Artículo
        $art = [];
        foreach (['articuloNombre'=>'nombre','unidadMedida'=>'unidadMedida','stockMin'=>'stockMin','stockMax'=>'stockMax'] as $k=>$col) {
            if (array_key_exists($k,$in)) $art[$col] = $in[$k];
        }
        if ($art) $db->table('articulo')->update($art, ['id'=>(int)$in['articuloId']]);

        // Lote
        $loteId   = !empty($in['loteId']) ? (int)$in['loteId'] : null;
        $loteData = [];
        foreach (['loteCodigo'=>'codigo','fechaFabricacion'=>'fechaFabricacion','fechaCaducidad'=>'fechaCaducidad','loteNotas'=>'notas'] as $k=>$col) {
            if (array_key_exists($k,$in)) $loteData[$col] = $in[$k] ?: null;
        }
        if ($loteId) {
            if ($loteData) $db->table('lote')->update($loteData, ['id'=>$loteId]);
        } elseif (!empty($loteData)) {
            $loteData['articuloId'] = (int)$in['articuloId'];
            $db->table('lote')->insert($loteData);
            $loteId = (int)$db->insertID();
        }

        // Stock
        $stockSet = [];
        if (array_key_exists('cantidad',$in))   $stockSet['cantidad']   = $in['cantidad'];
        if (array_key_exists('ubicacionId',$in) && $in['ubicacionId'] !== '')
            $stockSet['ubicacionId'] = (int)$in['ubicacionId'];
        if ($loteId) $stockSet['loteId'] = $loteId;
        if ($stockSet) $db->table('stock')->update($stockSet, ['id'=>(int)$in['stockId']]);

        $db->transComplete();
        if (!$db->transStatus()) return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'No se pudo guardar']);
        return $this->response->setJSON(['ok'=>true,'message'=>'Guardado','loteId'=>$loteId]);
    }

    /* ===== AGREGAR / UPSERT ===== */
    public function apiAgregar()
    {
        $in = $this->request->getJSON(true) ?: $this->request->getPost();

        $ubicacionId = (int)($in['ubicacionId'] ?? 0);
        $cantidad    = isset($in['cantidad']) ? (float)$in['cantidad'] : null;
        $operacion   = in_array(($in['operacion'] ?? 'sumar'), ['sumar','restar','reemplazar']) ? $in['operacion'] : 'sumar';

        $articuloId    = isset($in['articuloId']) && is_numeric($in['articuloId']) ? (int)$in['articuloId'] : null;
        $sku           = trim((string)($in['sku'] ?? ''));
        $articuloTexto = trim((string)($in['articuloTexto'] ?? $in['articulo'] ?? ''));
        $unidadMedida  = trim((string)($in['unidadMedida'] ?? ''));
        $stockMin      = $in['stockMin'] ?? null;
        $stockMax      = $in['stockMax'] ?? null;
        $autoCrear     = $in['autoCrear'] ?? true;

        if (!$ubicacionId || $cantidad === null) {
            return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>'ubicacionId y cantidad son obligatorios']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1) Resolver o crear artículo
        $resArt = $this->inv->resolverOCrearArticulo($articuloId, $sku, $articuloTexto, $unidadMedida ?: null, $stockMin, $stockMax, (bool)$autoCrear);
        if (!$resArt['ok']) {
            $db->transComplete();
            return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>$resArt['message'] ?? 'No se pudo resolver el artículo']);
        }
        $articuloId = (int)$resArt['articuloId'];

        // si mandan UM/min/max, actualizar
        $upd = [];
        if ($unidadMedida !== '') $upd['unidadMedida'] = $unidadMedida;
        if ($stockMin !== null)   $upd['stockMin']     = $stockMin === '' ? null : (float)$stockMin;
        if ($stockMax !== null)   $upd['stockMax']     = $stockMax === '' ? null : (float)$stockMax;
        if ($upd) $db->table('articulo')->update($upd, ['id'=>$articuloId]);

        // 2) Lote (opcional)
        $loteId = $this->inv->findOrCreateLote(
            $articuloId,
            trim((string)($in['loteCodigo'] ?? '')),
            ($in['fechaFabricacion'] ?? null) ?: null,
            ($in['fechaCaducidad']   ?? null) ?: null,
            ($in['loteNotas']        ?? null) ?: null
        );

        // 3) Upsert de stock
        $resStock = $this->inv->upsertStock($articuloId, $ubicacionId, $loteId, (float)$cantidad, $operacion);
        if (!$resStock['ok']) {
            $db->transComplete();
            return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>$resStock['message'] ?? 'No se pudo actualizar el stock']);
        }

        $db->transComplete();
        if (!$db->transStatus()) return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'No se pudo guardar']);

        return $this->response->setJSON([
            'ok'=>true,
            'message'=>'Guardado',
            'articuloId'=>$articuloId,
            'stockId'=>$resStock['stockId'],
            'cantidad'=>$resStock['cantidad']
        ]);
    }

    /* ===== ELIMINAR ===== */
    public function apiEliminar($stockId)
    {
        $id = (int)$stockId;
        if(!$id) return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'message'=>'ID inválido']);

        try{
            \Config\Database::connect()->table('stock')->delete(['id'=>$id]);
            return $this->response->setJSON(['ok'=>true]);
        }catch (\Throwable $e){
            return $this->response->setStatusCode(500)->setJSON(['ok'=>false,'message'=>'No se pudo eliminar']);
        }
    }
}
