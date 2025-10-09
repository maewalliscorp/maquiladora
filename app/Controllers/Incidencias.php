<?php
namespace App\Controllers;

use App\Models\IncidenciaModel;

class Incidencias extends BaseController
{
    public function index()
    {
        $m = new IncidenciaModel();

        try {
            // Formato SQL con alias (siguiendo tu estilo de “formadesql”):
            // select i.id as Ide, i.op as OP, i.tipo as Tipo, i.fecha as Fecha, i.descripcion as Descripcion from incidencia as i
            $rows = $m->select('id as Ide, op as OP, tipo as Tipo, fecha as Fecha, descripcion as Descripcion')
                ->orderBy('fecha','DESC')
                ->findAll();
            $lista = $rows;
            $error = null;
        } catch (\Throwable $e) {
            // Si hay error de BD/tabla, no romper la vista
            $lista = [];
            $error = 'No fue posible consultar incidencias (' . $e->getMessage() . ')';
        }

        if ($error) {
            session()->setFlashdata('error', $error);
        }

        return view('modulos/incidencias', [
            'title'   => 'Incidencias',
            'lista'   => $lista,
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost([
            'op','tipo','fecha','descripcion'
        ]);

        // Validación simple
        if (empty($data['op']) || empty($data['tipo']) || empty($data['fecha'])) {
            return redirect()->back()->with('error','Faltan campos requeridos.');
        }

        $m = new IncidenciaModel();
        $m->insert($data);

        return redirect()->to(site_url('modulo3/incidencias'))->with('ok','Incidencia registrada.');
    }

    public function delete($id)
    {
        $m = new IncidenciaModel();
        $m->delete($id);
        return redirect()->back()->with('ok','Incidencia eliminada.');
    }
}
