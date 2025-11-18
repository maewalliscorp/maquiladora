<?php
namespace App\Controllers;

use App\Models\IncidenciaModel;
use App\Models\EmpleadoModel;
use App\Models\OrdenProduccionModel;

class Incidencias extends BaseController
{
    public function index()
    {
        $maquiladoraId = session()->get('maquiladora_id');

        // Catálogos para el modal, filtrados por maquiladora si aplica
        $empBuilder = (new EmpleadoModel())
            ->select('id,nombre,apellido')
            ->where('activo', 1);
        if ($maquiladoraId) {
            $empBuilder->where('maquiladoraID', (int)$maquiladoraId);
        }
        $empleados = $empBuilder->orderBy('nombre','ASC')->findAll();

        $opModel = new OrdenProduccionModel();
        $opModel = $opModel->select('id,folio');
        if ($maquiladoraId) {
            $opModel = $opModel->where('maquiladoraID', (int)$maquiladoraId);
        }
        $ops = $opModel->orderBy('folio','DESC')->findAll();

        try {
            $db = db_connect(); // ✅ CI4
            $builder = $db->table('incidencia i')
                ->select(
                    'i.id as Ide,' .
                    'DATE_FORMAT(i.fecha, "%Y-%m-%d") as Fecha,' .
                    'op.folio as OP,' .
                    'i.tipo as Tipo,' .
                    'i.prioridad as Prioridad,' .
                    'CONCAT(COALESCE(e.nombre,""), " ", COALESCE(e.apellido,"")) as Empleado,' .
                    'i.descripcion as Descripcion,' .
                    'i.accion as Accion'
                )
                ->join('orden_produccion op', 'op.id = i.ordenProduccionFK', 'left')
                ->join('empleado e', 'e.id = i.empleadoFK', 'left');

            // Filtrar incidencias por maquiladora de la incidencia, la OP o el empleado
            if ($maquiladoraId) {
                $builder->groupStart()
                    ->where('i.maquiladoraID', (int)$maquiladoraId)
                    ->orWhere('op.maquiladoraID', (int)$maquiladoraId)
                    ->orWhere('e.maquiladoraID', (int)$maquiladoraId)
                ->groupEnd();
            }

            $rows = $builder->orderBy('i.fecha', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            $rows = [];
            session()->setFlashdata('error', 'No fue posible consultar incidencias ('.$e->getMessage().')');
        }

        return view('modulos/incidencias', [
            'title'     => 'Incidencias',
            'lista'     => $rows,
            'empleados' => $empleados,
            'ops'       => $ops,
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();
        $data = [
            'ordenProduccionFK' => (int)($post['ordenProduccionFK'] ?? 0),
            'empleadoFK'        => ($post['empleadoFK'] ?? '') !== '' ? (int)$post['empleadoFK'] : null,
            'tipo'              => trim($post['tipo'] ?? ''),
            'prioridad'         => trim($post['prioridad'] ?? 'Baja'),
            'fecha'             => $post['fecha'] ?? date('Y-m-d'),
            'descripcion'       => trim($post['descripcion'] ?? ''),
            'accion'            => trim($post['accion'] ?? ''),
        ];
        if ($data['ordenProduccionFK'] <= 0 || $data['tipo'] === '' || $data['fecha'] === '') {
            return redirect()->back()->with('error','Faltan campos obligatorios (OP, tipo, fecha).');
        }
        (new IncidenciaModel())->insert($data);
        return redirect()->to(site_url('modulo3/incidencias'))->with('ok','Incidencia registrada.');
    }

    public function delete($id)
    {
        (new IncidenciaModel())->delete((int)$id);
        return redirect()->back()->with('ok','Incidencia eliminada.');
    }

    public function modal()
    {
        // Catálogos mínimos para el modal de alta
        $empleados = (new EmpleadoModel())
            ->select('id,nombre,apellido')
            ->where('activo', 1)
            ->orderBy('nombre','ASC')->findAll();

        $ops = (new OrdenProduccionModel())
            ->select('id,folio')
            ->orderBy('folio','DESC')->findAll();

        return view('modulos/incidencias_modal', [
            'empleados' => $empleados,
            'ops'       => $ops,
        ]);
    }
}
