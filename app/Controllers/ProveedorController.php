<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProveedorModel;
use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;

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

            // Última OC por proveedor (para botón PDF)
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
     * ELIMINAR
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
     * CREAR ORDEN + PDF
     * ========================= */
    public function crearOrden()
    {
        $idProveedor = (int)$this->request->getPost('proveedor_id');
        $fecha       = $this->request->getPost('fecha') ?: date('Y-m-d');
        $prioridad   = trim((string)$this->request->getPost('prioridad'));
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
            $db->transStart();

            $dataOc = [
                'id_proveedor' => $idProveedor,
                'fecha'        => $fecha,
                'prioridad'    => $prioridad,
                'descripcion'  => $descripcion,
                'estatus'      => 'Pendiente',
            ];

            $db->table('proveedor_oc')->insert($dataOc);
            $idOc = (int)$db->insertID();

            // ---- Generar PDF (protegido) ----
            $pdfPath = null;

            if (class_exists(\Dompdf\Dompdf::class) && class_exists(\Dompdf\Options::class)) {
                try {
                    $pdfPath = $this->generarPdfOrdenProveedor($idOc);
                } catch (\Throwable $ePdf) {
                    log_message('error', '[OC PDF] Error al generar PDF de la orden {id}: {msg}', [
                        'id'  => $idOc,
                        'msg' => $ePdf->getMessage(),
                    ]);
                }
            } else {
                log_message('error', '[OC PDF] Dompdf no está disponible. Se omite generación de PDF.');
            }

            if ($pdfPath) {
                $db->table('proveedor_oc')
                    ->where('id_proveedorOC', $idOc)
                    ->update(['pdf_path' => $pdfPath]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('No se pudo guardar la orden (transacción fallida).');
            }

            $msg = 'Orden de pedido registrada correctamente.';
            if ($pdfPath) {
                $msg .= ' PDF generado correctamente.';
            } else {
                $msg .= ' (El PDF no pudo generarse, revisa los logs).';
            }

            return redirect()
                ->to(site_url('proveedores'))
                ->with('success', $msg);

        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

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
     * VER ORDEN (HTML simple)
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
            ->select('oc.*, p.nombre AS proveedor_nombre, p.codigo AS proveedor_codigo, p.email AS proveedor_email, p.telefono AS proveedor_telefono, p.direccion AS proveedor_direccion')
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
     * DESCARGAR PDF
     * ========================= */
    public function ordenPdf($idOc = null)
    {
        $idOc = (int)$idOc;

        if ($idOc <= 0) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'Orden no válida.');
        }

        $db = Database::connect();

        $row = $db->table('proveedor_oc AS oc')
            ->select('oc.*, p.nombre AS proveedor_nombre, p.codigo AS proveedor_codigo, p.email AS proveedor_email, p.telefono AS proveedor_telefono, p.direccion AS proveedor_direccion')
            ->join('proveedor AS p', 'p.id_proveedor = oc.id_proveedor', 'inner')
            ->where('oc.id_proveedorOC', $idOc)
            ->get()
            ->getRowArray();

        if (!$row) {
            return redirect()
                ->to(site_url('proveedores'))
                ->with('error', 'Orden no encontrada.');
        }

        // Si ya hay PDF guardado y existe, lo devolvemos
        if (!empty($row['pdf_path'])) {
            $filePath = WRITEPATH . $row['pdf_path'];
            if (is_file($filePath)) {
                return $this->response->download($filePath, null);
            }
        }

        // Si no hay PDF o se perdió, lo generamos de nuevo (protegido)
        $pdfRelPath = null;
        if (class_exists(\Dompdf\Dompdf::class) && class_exists(\Dompdf\Options::class)) {
            try {
                $pdfRelPath = $this->generarPdfOrdenProveedor($idOc);
            } catch (\Throwable $ePdf) {
                log_message('error', '[OC PDF] Error al regenerar PDF de la orden {id}: {msg}', [
                    'id'  => $idOc,
                    'msg' => $ePdf->getMessage(),
                ]);
            }
        } else {
            log_message('error', '[OC PDF] Dompdf no está disponible al intentar regenerar PDF.');
        }

        if ($pdfRelPath) {
            $db->table('proveedor_oc')
                ->where('id_proveedorOC', $idOc)
                ->update(['pdf_path' => $pdfRelPath]);

            $filePath = WRITEPATH . $pdfRelPath;
            if (is_file($filePath)) {
                return $this->response->download($filePath, null);
            }
        }

        return redirect()
            ->to(site_url('proveedores'))
            ->with('error', 'No se pudo generar el PDF de la orden.');
    }

    /* =========================
     * GENERAR PDF (Dompdf)
     * ========================= */
    protected function generarPdfOrdenProveedor(int $idOc): ?string
    {
        try {
            // Si no está Dompdf instalado, salimos sin reventar
            if (!class_exists(\Dompdf\Dompdf::class) || !class_exists(\Dompdf\Options::class)) {
                log_message('error', '[OC PDF] Dompdf no está instalado / cargado.');
                return null;
            }

            $db = Database::connect();

            $row = $db->table('proveedor_oc AS oc')
                ->select('oc.*, p.nombre AS proveedor_nombre, p.codigo AS proveedor_codigo, p.email AS proveedor_email, p.telefono AS proveedor_telefono, p.direccion AS proveedor_direccion')
                ->join('proveedor AS p', 'p.id_proveedor = oc.id_proveedor', 'inner')
                ->where('oc.id_proveedorOC', $idOc)
                ->get()
                ->getRowArray();

            if (!$row) {
                return null;
            }

            $data = [
                'orden' => $row,
            ];

            $html = view('modulos/orden_proveedor', $data);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();

            $dir = WRITEPATH . 'ordenes_proveedor';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $fileName = 'orden_proveedor_' . $idOc . '.pdf';
            $fullPath = $dir . DIRECTORY_SEPARATOR . $fileName;

            file_put_contents($fullPath, $output);

            return 'ordenes_proveedor/' . $fileName;
        } catch (\Throwable $e) {
            log_message('error', '[OC PDF] Excepción al generar PDF: {msg}', [
                'msg' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
