<?php
namespace App\Controllers;

use App\Models\MaquinaModel;

class Maquinaria extends BaseController
{
    public function index()
    {
        // PRUEBA: quita el comentario 1 vez para ver si llegas aquí
        // dd('Estoy en Maquinaria::index()');

        $model = new \App\Models\MaquinaModel();
        $maquinas = $model->orderBy('codigo','ASC')->findAll();

        // Si quieres verificar lo que viene de la BD:
        // dd($maquinas);

        $maq = array_map(function ($m) {
            return [
                'cod'    => $m['codigo'] ?? '',
                'modelo' => $m['modelo'] ?? '',
                'compra' => $m['fechaCompra'] ?? '',
                'ubic'   => $m['ubicacion'] ?? '',
                'estado' => ($m['activa'] ?? 1) ? 'Operativa' : 'En reparación',
            ];
        }, $maquinas);

        return view('modulos/mantenimiento_inventario', [
            'title' => 'Inventario de Maquinaria',
            'maq'   => $maq
        ]);
    }


}
