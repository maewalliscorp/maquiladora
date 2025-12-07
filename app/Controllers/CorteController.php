<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CorteModel;
use App\Models\CorteDetalleModel;
use App\Models\CorteTallaModel;

class CorteController extends BaseController
{
    public function index()
    {
        if (!can('menu.produccion')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $corteModel = new CorteModel();
        $maquiladoraId = session()->get('maquiladora_id');

        $cortes = $corteModel->getCortesPorMaquiladora($maquiladoraId);

        return view('modulos/corte_lista', [
            'title' => 'Gestión de Cortes',
            'cortes' => $cortes
        ]);
    }

    public function nuevo()
    {
        if (!can('menu.produccion')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        return view('modulos/corte_editor', [
            'title' => 'Nuevo Corte',
            'corte' => [],
            'detalles' => [],
            'tallas' => []
        ]);
    }

    public function editar($id)
    {
        $corteModel = new CorteModel();
        $detalleModel = new CorteDetalleModel();
        $tallaModel = new CorteTallaModel();

        $corte = $corteModel->find($id);
        if (!$corte) {
            return redirect()->to(base_url('modulo3/cortes'))->with('error', 'Corte no encontrado');
        }

        $detalles = $detalleModel->getDetallesPorCorte($id);

        // Cargar tallas para cada detalle
        foreach ($detalles as &$detalle) {
            $detalle['tallas'] = $tallaModel->getTallasPorDetalle($detalle['id']);
        }

        return view('modulos/corte_editor', [
            'title' => 'Editar Corte',
            'corte' => $corte,
            'detalles' => $detalles
        ]);
    }

    public function guardar()
    {
        $corteModel = new CorteModel();
        $detalleModel = new CorteDetalleModel();
        $tallaModel = new CorteTallaModel();

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $data = $this->request->getJSON(true); // Recibir JSON desde el frontend
            $maquiladoraId = session()->get('maquiladora_id');

            // 1. Guardar Encabezado
            $corteData = [
                'idmaquiladora' => $maquiladoraId,
                'numero_corte' => $data['numero_corte'],
                'estilo' => $data['estilo'],
                'prenda' => $data['prenda'],
                'cliente' => $data['cliente'] ?? null,
                'color' => $data['color'] ?? null,
                'precio' => $data['precio'] ?? 0,
                'fecha_entrada' => $data['fecha_entrada'] ?? null,
                'fecha_embarque' => $data['fecha_embarque'] ?? null,
                'cortador' => $data['cortador'] ?? null,
                'tendedor' => $data['tendedor'] ?? null,
                'tela' => $data['tela'] ?? null,
                'largo_trazo' => $data['largo_trazo'] ?? 0,
                'ancho_tela' => $data['ancho_tela'] ?? 0,
                'total_prendas' => $data['total_prendas'] ?? 0,
                'total_tela_usada' => $data['total_tela_usada'] ?? 0,
                'consumo_promedio' => $data['consumo_promedio'] ?? 0,
            ];

            if (!empty($data['id'])) {
                if (!$corteModel->update($data['id'], $corteData)) {
                    $errors = $corteModel->errors();
                    $dbError = $db->error();
                    return $this->response->setJSON(['ok' => false, 'message' => 'Error al actualizar Corte: ' . json_encode($errors) . ' ' . $dbError['message']]);
                }
                $corteId = $data['id'];

                // Limpiar detalles anteriores
                $detalleModel->where('corte_id', $corteId)->delete();
            } else {
                $corteId = $corteModel->insert($corteData);
                if (!$corteId) {
                    $errors = $corteModel->errors();
                    $dbError = $db->error();
                    return $this->response->setJSON(['ok' => false, 'message' => 'Error al crear Corte: ' . json_encode($errors) . ' ' . $dbError['message']]);
                }
            }

            // 2. Guardar Detalles (Rollos)
            if (!empty($data['detalles']) && is_array($data['detalles'])) {
                foreach ($data['detalles'] as $detalle) {
                    $detalleData = [
                        'corte_id' => $corteId,
                        'numero_rollo' => $detalle['numero_rollo'],
                        'lote' => $detalle['lote'] ?? null,
                        'color_rollo' => $detalle['color_rollo'] ?? null,
                        'peso_kg' => $detalle['peso_kg'] ?? 0,
                        'longitud_mts' => $detalle['longitud_mts'] ?? 0,
                        'metros_usados' => $detalle['metros_usados'] ?? 0,
                        'merma_danada' => $detalle['merma_danada'] ?? 0,
                        'merma_faltante' => $detalle['merma_faltante'] ?? 0,
                        'merma_desperdicio' => $detalle['merma_desperdicio'] ?? 0,
                        'tela_sobrante' => $detalle['tela_sobrante'] ?? 0,
                        'diferencia' => $detalle['diferencia'] ?? 0,
                        'cantidad_lienzos' => $detalle['cantidad_lienzos'] ?? 0,
                        'total_prendas_rollo' => $detalle['total_prendas_rollo'] ?? 0,
                    ];

                    $detalleId = $detalleModel->insert($detalleData);
                    if (!$detalleId) {
                        $errors = $detalleModel->errors();
                        $dbError = $db->error();
                        return $this->response->setJSON(['ok' => false, 'message' => 'Error al guardar detalle rollo ' . $detalle['numero_rollo'] . ': ' . json_encode($errors) . ' ' . $dbError['message']]);
                    }

                    // 3. Guardar Tallas
                    if (!empty($detalle['tallas']) && is_array($detalle['tallas'])) {
                        foreach ($detalle['tallas'] as $talla) { // Fix: $detalle['tallas'] is array of objects {talla, cantidad}
                            $tallaData = [
                                'corte_detalle_id' => $detalleId,
                                'talla' => $talla['talla'],
                                'cantidad' => $talla['cantidad']
                            ];
                            if (!$tallaModel->insert($tallaData)) {
                                $errors = $tallaModel->errors();
                                $dbError = $db->error();
                                return $this->response->setJSON(['ok' => false, 'message' => 'Error al guardar talla ' . $talla['talla'] . ': ' . json_encode($errors) . ' ' . $dbError['message']]);
                            }
                        }
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                $error = $db->error();
                return $this->response->setJSON(['ok' => false, 'message' => 'Error Transacción DB: ' . $error['message']]);
            }

            return $this->response->setJSON(['ok' => true, 'message' => 'Corte guardado correctamente', 'id' => $corteId]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }
}
