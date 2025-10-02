<?php
namespace App\Controllers;

use App\Models\WipModel;

class Wip extends BaseController
{
    public function index()
    {
        $m    = new WipModel();
        $rows = $m->getListado();

        $etapas = [];
        foreach ($rows as $r) {
            $etapas[] = [
                'id'    => $r['id'] ?? null,
                'etapa' => $r['etapa'] ?? '—',
                'resp'  => $r['responsable'] ?? '—',
                'ini'   => $r['inicio'] ?? '',
                'fin'   => $r['fin'] ?? '',
                'prog'  => (int)($r['progreso'] ?? 0),
            ];
        }

        return view('modulos/wip', ['etapas' => $etapas]);
    }

    public function actualizar(int $id)
    {
        $avance = (int)($this->request->getPost('avance') ?? 0);
        $avance = max(0, min(100, $avance));

        $m = new WipModel();
        if ($m->updateAvance($id, $avance)) {
            return redirect()->to(base_url('modulo3/wip'))->with('success', 'Avance actualizado.');
        }
        return redirect()->to(base_url('modulo3/wip'))->with('error', 'No se pudo actualizar.');
    }

    // Ver exactamente lo que devuelve el modelo
    public function json()
    {
        $m = new WipModel();
        return $this->response->setJSON($m->getListado());
    }

    // Diagnóstico completo: tablas vistas, campos detectados y cuál eligió
    public function debug()
    {
        $m = new WipModel();
        return $this->response->setJSON($m->scan());
    }
}
