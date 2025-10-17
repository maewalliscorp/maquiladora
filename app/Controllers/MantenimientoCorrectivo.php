<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MttoModel;
use Config\Database;

class MantenimientoCorrectivo extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /** Verifica si una columna existe en una tabla */
    private function tableHas(string $table, string $col): bool
    {
        foreach ($this->db->getFieldData($table) as $f) {
            if ($f->name === $col) return true;
        }
        return false;
    }

    /** Catálogo de máquinas con selección de columnas defensiva */
    private function catalogoMaquinas(): array
    {
        $tbl = 'maquina';
        $select = ['id']; // siempre
        // agrega solo si existen estas columnas
        foreach (['codigo','clave','serie','modelo','nombre','descripcion'] as $c) {
            if ($this->tableHas($tbl, $c)) $select[] = $c;
        }

        return $this->db->table($tbl)
            ->select(implode(',', $select))
            ->orderBy($this->tableHas($tbl,'codigo') ? 'codigo' : 'id', 'ASC')
            ->get()->getResultArray();
    }

    /** Catálogo de empleados con selección de columnas defensiva */
    private function catalogoEmpleados(): array
    {
        $tbl = 'empleado';
        $select = ['id']; // siempre
        foreach (['noEmpleado','numeroEmpleado','nombre','nombres','apellido','apellidos','activo'] as $c) {
            if ($this->tableHas($tbl, $c)) $select[] = $c;
        }

        $builder = $this->db->table($tbl)
            ->select(implode(',', $select))
            ->orderBy($this->tableHas($tbl,'nombre') ? 'nombre' : 'id', 'ASC');

        if ($this->tableHas($tbl, 'apellido')) {
            $builder->orderBy('apellido', 'ASC');
        } elseif ($this->tableHas($tbl, 'apellidos')) {
            $builder->orderBy('apellidos', 'ASC');
        }

        // filtra solo activos si existe la columna
        if ($this->tableHas($tbl, 'activo')) {
            $builder->where('activo', 1);
        }

        return $builder->get()->getResultArray();
    }

    /** Listado principal */
    public function index()
    {
        $mtto = new MttoModel();
        $rows = $mtto->getListado();

        if (!is_array($rows) || !$rows) {
            $rows = $mtto->getListadoSimple();
            foreach ($rows as &$r) { $r['Horas'] = 0; }
        }

        return view('modulos/mantenimiento_correctivo', [
            'title'     => 'Mantenimiento Correctivo',
            'tableId'   => 'tablaMtto',
            'columns'   => ['Folio','Apertura','Máquina','Tipo','Estatus','Descripción','Cierre','Horas','Acciones'],
            'rows'      => $rows,
            'maquinas'  => $this->catalogoMaquinas(),
            'empleados' => $this->catalogoEmpleados(),
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
            $horas = ($post['d_horas'] !== '' && $post['d_horas'] !== null) ? (float)$post['d_horas'] : null;
            $m->insertDetalle((int)$id, $post['d_accion'] ?? null, $post['d_repuestos'] ?? null, $horas);
        }

        return redirect()->to(site_url('mantenimiento/correctivo'))
            ->with('success','Orden de mantenimiento registrada.');
    }

    /** Actualiza orden (desde modal Editar) */
    public function actualizar($id)
    {
        $id = (int)$id;
        $post = $this->request->getPost([
            'fechaApertura','maquinaId','responsableId','tipo','estatus','descripcion','fechaCierre'
        ]);

        if (!$id || empty($post['fechaApertura']) || empty($post['maquinaId']) || empty($post['tipo']) || empty($post['estatus'])) {
            return redirect()->back()->with('error','Completa los campos obligatorios.');
        }

        $m = new MttoModel();
        $ok = $m->update($id, [
            'fechaApertura' => $post['fechaApertura'],
            'maquinaId'     => (int)$post['maquinaId'],
            'responsableId' => $post['responsableId'] ?: null,
            'tipo'          => trim($post['tipo']),
            'estatus'       => trim($post['estatus']),
            'descripcion'   => trim($post['descripcion'] ?? ''),
            'fechaCierre'   => $post['fechaCierre'] ?: null,
        ]);

        return redirect()->to(site_url('mantenimiento/correctivo'))
            ->with($ok ? 'success' : 'error', $ok ? 'Orden actualizada.' : 'No se pudo actualizar.');
    }

    /** Diagnóstico rápido (opcional) */
    public function diag()
    {
        $info = $this->db->query("
            SELECT
              DATABASE() AS db,
              (SELECT COUNT(*) FROM mtto) AS mttoCount,
              (SELECT COUNT(*) FROM mtto_detectado) AS detCount
        ")->getRowArray();
        return $this->response->setJSON($info);
    }

    /** Probe con SQL (opcional) */
    public function probe()
    {
        $m = new MttoModel();
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
            'db'            => $this->db->database,
            'counts'        => [
                'mtto'           => (int)$this->db->query('SELECT COUNT(*) c FROM mtto')->getRow('c'),
                'mtto_detectado' => (int)$this->db->query('SELECT COUNT(*) c FROM mtto_detectado')->getRow('c'),
            ],
            'sql_join'      => $sqlJoin,
            'result_join'   => $this->db->query($sqlJoin)->getResultArray(),
            'result_simple' => $m->getListadoSimple(),
        ];
        return $this->response->setJSON($data);
    }
}
