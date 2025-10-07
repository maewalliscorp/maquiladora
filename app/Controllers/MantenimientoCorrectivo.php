<?php
namespace App\Controllers;

use App\Models\MttoModel;
use Config\Database;

class MantenimientoCorrectivo extends BaseController
{
    public function index()
    {
        $mtto = new MttoModel();
        $rows = $mtto->getListado();

        if (!is_array($rows) || !$rows) {
            $rows = $mtto->getListadoSimple();
            foreach ($rows as &$r) { $r['Horas'] = 0; }
        }

        return view('modulos/mantenimiento_correctivo', [
            'title'   => 'Mantenimiento Correctivo',
            'tableId' => 'tablaMtto',
            'columns' => ['Folio','Apertura','Máquina','Tipo','Estatus','Descripción','Cierre','Horas'],
            'rows'    => $rows,
        ]);
    }

    /** Inserta orden + detalle opcional */
    public function crear()
    {
        $post = $this->request->getPost([
            'fechaApertura','maquinaId','responsableId','tipo','estatus','descripcion','fechaCierre',
            'd_accion','d_repuestos','d_horas'
        ]);

        if (empty($post['fechaApertura']) || empty($post['maquinaId']) || empty($post['tipo']) || empty($post['estatus'])) {
            return redirect()->back()->with('error','Completa los campos obligatorios.')->withInput();
        }

        $m = new MttoModel();
        $id = $m->insert([
            'fechaApertura' => $post['fechaApertura'],
            'maquinaId'     => (int)$post['maquinaId'],
            'responsableId' => $post['responsableId'] ?: null,
            'tipo'          => trim($post['tipo']),
            'estatus'       => trim($post['estatus']),
            'descripcion'   => trim($post['descripcion'] ?? ''),
            'fechaCierre'   => $post['fechaCierre'] ?: null,
        ], true);

        if ($id) {
            $horas = $post['d_horas'] !== '' ? (float)$post['d_horas'] : null;
            $m->insertDetalle((int)$id, $post['d_accion'] ?? null, $post['d_repuestos'] ?? null, $horas);
        }

        return redirect()->to(site_url('mantenimiento/correctivo'))
            ->with('success','Orden de mantenimiento registrada.');
    }

    /** Diagnóstico simple */
    public function diag()
    {
        $db = Database::connect();
        $info = $db->query("
            SELECT
              DATABASE() AS db,
              (SELECT COUNT(*) FROM mtto) AS mttoCount,
              (SELECT COUNT(*) FROM mtto_detectado) AS detCount
        ")->getRowArray();
        return $this->response->setJSON($info);
    }

    /** Probe: muestra SQL y resultados de join y simple */
    public function probe()
    {
        $m = new MttoModel();
        $db = \Config\Database::connect();
        $sqlJoin = "SELECT m.id AS Folio, m.fechaApertura AS Apertura,
                    COALESCE(mx.codigo, m.maquinaId) AS Maquina, m.tipo AS Tipo,
                    m.estatus AS Estatus, m.descripcion AS Descripcion, m.fechaCierre AS Cierre,
                    COUNT(d.id) AS Acciones, COALESCE(SUM(d.tiempoHoras),0) AS Horas
                    FROM mtto m
                    LEFT JOIN maquina mx ON mx.id = m.maquinaId
                    LEFT JOIN mtto_detectado d ON d.otMttoId = m.id
                    GROUP BY m.id, m.fechaApertura, mx.codigo, m.maquinaId, m.tipo, m.estatus, m.descripcion, m.fechaCierre
                    ORDER BY m.fechaApertura DESC";
        $data = [
            'db'          => $db->database,
            'counts'      => [
                'mtto'            => (int)$db->query('SELECT COUNT(*) c FROM mtto')->getRow('c'),
                'mtto_detectado'  => (int)$db->query('SELECT COUNT(*) c FROM mtto_detectado')->getRow('c'),
            ],
            'sql_join'     => $sqlJoin,
            'result_join'  => $db->query($sqlJoin)->getResultArray(),
            'sql_simple'   => 'SELECT m.id Folio, m.fechaApertura Apertura, COALESCE(mx.codigo, m.maquinaId) Maquina, m.tipo Tipo, m.estatus Estatus, m.descripcion Descripcion, m.fechaCierre Cierre FROM mtto m LEFT JOIN maquina mx ON mx.id = m.maquinaId ORDER BY m.fechaApertura DESC',
            'result_simple'=> $m->getListadoSimple(),
        ];
        return $this->response->setJSON($data);
    }
}
