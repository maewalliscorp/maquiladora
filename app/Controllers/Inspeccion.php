<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Inspeccion extends BaseController
{
    public function index()
    {
        $m    = new InspeccionModel();
        $rows = $m->getListado();

        $lista = [];
        $n = 1;
        foreach ($rows as $r) {
            // Si venimos del fallback, empresa/descripcion/estatus podrían venir vacíos
            $empresa     = $r['empresa']     ?? '';
            $descripcion = $r['descripcion'] ?? '';
            $estatus     = $r['estatus']     ?? '';

            // Rellenos amigables en caso de fallback
            if ($empresa === '' && !empty($r['ordenProduccionId'])) {
                $empresa = '—';
            }
            if ($descripcion === '' && !empty($r['ordenProduccionId'])) {
                $descripcion = 'OP #' . $r['ordenProduccionId'];
            }
            if ($estatus === '' && !empty($r['resultado'])) {
                $estatus = $r['resultado']; // algo informativo
            }

            $lista[] = [
                'num'         => $n++,
                'id'          => $r['id'],
                'empresa'     => $empresa,
                'descripcion' => $descripcion,
                'estatus'     => $estatus,
                'fecha'       => $r['fecha'] ?? null,
                'resultado'   => $r['resultado'] ?? null,
            ];
        }

        return view('modulos/inspeccion', [
            'title' => 'Inspección',
            'lista' => $lista,
        ]);
    }

    public function evaluar(int $id)
    {
        $m  = new InspeccionModel();
        $i  = $m->getDetalle($id);
        if (!$i) {
            throw new PageNotFoundException('Inspección no encontrada');
        }

        return view('modulos/inspeccion_evaluar', [
            'title' => 'Evaluación de inspección',
            'i'     => $i,
        ]);
    }

    public function guardarEvaluacion(int $id)
    {
        $m   = new InspeccionModel();
        $row = $m->find($id);
        if (!$row) throw new PageNotFoundException('Inspección no encontrada');

        $data = $this->request->getPost(['resultado','observaciones','fecha']);

        if (empty($data['fecha'])) {
            $data['fecha'] = date('Y-m-d');
        } elseif (strpos($data['fecha'], '/') !== false) {
            [$d,$mth,$y] = explode('/', $data['fecha']);
            if (@checkdate((int)$mth,(int)$d,(int)$y)) {
                $data['fecha'] = sprintf('%04d-%02d-%02d', $y,$mth,$d);
            }
        }

        $m->update($id, [
            'resultado'     => $data['resultado'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'fecha'         => $data['fecha'],
        ]);

        return redirect()->to(base_url('modulo3/inspeccion'))
            ->with('success', 'Evaluación guardada correctamente.');
    }

    // Debug opcional (útil ahora mismo)
    public function json()
    {
        $m = new InspeccionModel();
        return $this->response->setJSON($m->getListado());
    }
}
