<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProveedorModel;
use Config\Database;

class ProveedorController extends BaseController
{
    /* =========================
     * LISTADO
     * ========================= */
    public function index()
    {
        $proveedorModel = new ProveedorModel();

        // Listar proveedores (últimos primero)
        $proveedores = $proveedorModel
            ->orderBy('id_proveedor', 'DESC')
            ->findAll();

        if (!empty($proveedores)) {
            $idsProveedor = array_column($proveedores, 'id_proveedor');

            $db = Database::connect();

            // Objetos que maneja (artículos ligados a sus órdenes)
            $builder = $db->table('proveedor_item AS i');
            $builder->select('oc.id_proveedor, a.nombre AS articulo');
            $builder->join('proveedor_oc AS oc', 'oc.id_proveedorOC = i.id_proveedorOC', 'inner');
            $builder->join('articulo AS a', 'a.id = i.articuloId', 'inner');
            $builder->whereIn('oc.id_proveedor', $idsProveedor);

            $rows = $builder->get()->getResultArray();

            $mapObjetos = [];
            foreach ($rows as $row) {
                $idProv = (int) $row['id_proveedor'];
                $artNom = (string) $row['articulo'];

                if (!isset($mapObjetos[$idProv])) {
                    $mapObjetos[$idProv] = [];
                }

                if (!in_array($artNom, $mapObjetos[$idProv], true)) {
                    $mapObjetos[$idProv][] = $artNom;
                }
            }

            // Última OC por proveedor (no se muestra ahora, pero lo dejamos disponible)
            $ocRows = $db->table('proveedor_oc')
                ->select('id_proveedor, MAX(id_proveedorOC) AS ultima_oc')
                ->whereIn('id_proveedor', $idsProveedor)
                ->groupBy('id_proveedor')
                ->get()
                ->getResultArray();

            $mapUltima = [];
            foreach ($ocRows as $r) {
                $mapUltima[(int)$r['id_proveedor']] = (int)$r['ultima_oc'];
            }

            // Agregar campos extra a cada proveedor
            foreach ($proveedores as &$prov) {
                $idProv = (int)$prov['id_proveedor'];
                $listaObjs = $mapObjetos[$idProv] ?? [];
                $prov['objetos']   = $listaObjs ? implode(', ', $listaObjs) : '—';
                $prov['ultima_oc'] = $mapUltima[$idProv] ?? null;
            }
            unset($prov);
        }

        return view('modulos/proveedores', [
            'title'       => 'Proveedores',
            'proveedores' => $proveedores,
        ]);
    }

    /* =========================
     * ALTA / EDICIÓN
     * ========================= */
    public function store()
    {
        $proveedorModel = new ProveedorModel();
        $id = (int)$this->request->getPost('id');

        $data = [
            'codigo'      => trim((string)$this->request->getPost('codigo')),
            'nombre'      => trim((string)$this->request->getPost('nombre')),
            'rfc'         => trim((string)$this->request->getPost('rfc')),
            'email'       => trim((string)$this->request->getPost('email')),
            'telefono'    => trim((string)$this->request->getPost('telefono')),
            'direccion'   => trim((string)$this->request->getPost('direccion')),
            'tipo_alerta' => trim((string)$this->request->getPost('tipo_alerta')),
        ];

        if ($data['nombre'] === '') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'El nombre / empresa del proveedor es obligatorio.');
        }

        try {
            if ($id > 0) {
                $proveedorModel->update($id, $data);
                $msg = 'Proveedor actualizado correctamente.';
            } else {
                $proveedorModel->insert($data);
                $msg = 'Proveedor agregado correctamente.';
            }

            return redirect()
                ->to(site_url('proveedores'))
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al guardar el proveedor: ' . $e->getMessage());
        }
    }

    /* =========================
     * ELIMINAR PROVEEDOR
     * ========================= */
    public function eliminar($id = null)
    {
        $id = (int)$id;

        if ($id <= 0) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'Proveedor no válido.');
        }

        $proveedorModel = new ProveedorModel();

        try {
            $proveedorModel->delete($id);

            return redirect()
                ->to(site_url('proveedores'))
                ->with('success', 'Proveedor eliminado.');
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'No se pudo eliminar el proveedor (tiene órdenes de compra asociadas).');
        }
    }

    /* =========================
     * CREAR ORDEN (desde modal)
     * ========================= */
    public function crearOrden()
    {
        $idProveedor = (int)$this->request->getPost('proveedor_id');
        $fecha       = $this->request->getPost('fecha') ?: date('Y-m-d');
        $prioridad   = trim((string)$this->request->getPost('prioridad')) ?: 'Normal';
        $descripcion = trim((string)$this->request->getPost('descripcion'));

        if ($idProveedor <= 0) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'Proveedor no válido para la orden.');
        }

        if ($descripcion === '') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Debes especificar los materiales / detalles del pedido.');
        }

        $db = Database::connect();

        try {
            $dataOc = [
                'id_proveedor' => $idProveedor,
                'fecha'        => $fecha,
                'prioridad'    => $prioridad,
                'descripcion'  => $descripcion,
                'estatus'      => 'Pendiente',
            ];

            $db->table('proveedor_oc')->insert($dataOc);

            return redirect()
                ->to(site_url('proveedores'))
                ->with('success', 'Orden de pedido registrada correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al registrar la orden de pedido: ' . $e->getMessage());
        }
    }

    /* =========================
     * HISTORIAL (AJAX)
     * ========================= */
    public function historial($idProveedor = null)
    {
        $idProveedor = (int)$idProveedor;
        if ($idProveedor <= 0) {
            return $this->response->setJSON([]);
        }

        $db = Database::connect();

        $rows = $db->table('proveedor_oc')
            ->select('id_proveedorOC, fecha, prioridad, estatus, descripcion')
            ->where('id_proveedor', $idProveedor)
            ->orderBy('fecha', 'DESC')
            ->orderBy('id_proveedorOC', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($rows);
    }

    /* =========================
     * VER ORDEN EN HTML (opcional)
     * ========================= */
    public function verOrden($idOc = null)
    {
        $idOc = (int)$idOc;

        if ($idOc <= 0) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'Orden no válida.');
        }

        $db = Database::connect();

        $row = $db->table('proveedor_oc AS oc')
            ->select('oc.*, 
                      p.nombre   AS proveedor_nombre, 
                      p.codigo   AS proveedor_codigo, 
                      p.email    AS proveedor_email, 
                      p.telefono AS proveedor_telefono, 
                      p.direccion AS proveedor_direccion')
            ->join('proveedor AS p', 'p.id_proveedor = oc.id_proveedor', 'inner')
            ->where('oc.id_proveedorOC', $idOc)
            ->get()
            ->getRowArray();

        if (!$row) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'Orden no encontrada.');
        }

        return view('modulos/orden_proveedor', [
            'title' => 'Orden de proveedor',
            'orden' => $row,
        ]);
    }

    /* =========================
     * MARCAR ORDEN COMO CUMPLIDA
     * ========================= */
    public function completarOrden($idOc = null)
    {
        $idOc = (int)$idOc;

        if ($idOc <= 0) {
            return redirect()
                ->back()
                ->with('error', 'Orden no válida.');
        }

        $db = Database::connect();

        try {
            $db->table('proveedor_oc')
                ->where('id_proveedorOC', $idOc)
                ->update([
                    'estatus' => 'Cumplida',
                ]);

            return redirect()
                ->back()
                ->with('success', 'Orden marcada como cumplida.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'No se pudo marcar la orden como cumplida: ' . $e->getMessage());
        }
    }

    /* =========================
     * ELIMINAR ORDEN
     * ========================= */
    public function eliminarOrden($idOc = null)
    {
        $idOc = (int)$idOc;

        if ($idOc <= 0) {
            return redirect()
                ->back()
                ->with('error', 'Orden no válida.');
        }

        $db = Database::connect();

        try {
            $db->transStart();

            // 1) Borrar primero los items ligados a la orden (para respetar la FK)
            $db->table('proveedor_item')
                ->where('id_proveedorOC', $idOc)
                ->delete();

            // 2) Borrar la propia orden
            $db->table('proveedor_oc')
                ->where('id_proveedorOC', $idOc)
                ->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transacción fallida al eliminar la orden.');
            }

            return redirect()
                ->back()
                ->with('success', 'Orden eliminada correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'No se pudo eliminar la orden: ' . $e->getMessage());
        }
    }
}
