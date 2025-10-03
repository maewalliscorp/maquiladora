<?php namespace App\Controllers;

use App\Models\OrdenProduccionModel;

class Produccion extends BaseController
{
    public function ordenes()
    {
        $model   = new OrdenProduccionModel();
        $ordenes = $model->getListado();

        foreach ($ordenes as &$r) {
            $r['ini'] = $r['ini'] ? date('Y-m-d', strtotime($r['ini'])) : '';
            $r['fin'] = $r['fin'] ? date('Y-m-d', strtotime($r['fin'])) : '';
        }

        return view('modulos/m1_ordenes', [
            'title'   => 'Órdenes de Producción',
            'ordenes' => $ordenes,
        ]);
    }
}
