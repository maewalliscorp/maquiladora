<?php

namespace App\Controllers;

class Disenos extends BaseController
{
    // Devuelve catálogo completo de diseños con su versión más reciente
    public function json_catalogo()
    {
        try {
            $disenoModel = new \App\Models\DisenoModel();
            $catalogo = $disenoModel->getCatalogoDisenos();
            $out = array_map(function($r){
                return [
                    'id'          => $r['id'] ?? null,
                    'codigo'      => $r['codigo'] ?? null,
                    'nombre'      => $r['nombre'] ?? null,
                    'descripcion' => $r['descripcion'] ?? null,
                    'version'     => $r['version'] ?? null,
                    'fecha'       => $r['fecha'] ?? null,
                    'aprobado'    => $r['aprobado'] ?? null,
                ];
            }, $catalogo ?: []);
            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            return $this->response->setJSON([]);
        }
    }
}
