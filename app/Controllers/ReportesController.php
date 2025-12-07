<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrdenProduccionModel;
use App\Models\InspeccionModel;

class ReportesController extends BaseController
{
    protected $ordenProduccionModel;
    protected $inspeccionModel;

    public function __construct()
    {
        $this->ordenProduccionModel = new OrdenProduccionModel();
        // Note: InspeccionModel might not exist or might be named differently. 
        // Based on previous search, 'InspeccionModel.php' exists.
        // We'll check if we need to import it or if it's autoloaded.
        // Assuming standard CI4 autoloading.
    }

    public function index()
    {
        if (!can('menu.reportes')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        // Reuse the logic from Modulos::reportes but cleaner
        $maquiladoraId = session()->get('maquiladora_id');
        $maquiladora = [];

        if ($maquiladoraId) {
            $db = \Config\Database::connect();
            $maquiladora = $db->table('maquiladora')
                ->where('idmaquiladora', $maquiladoraId)
                ->get()
                ->getRowArray();

            if ($maquiladora && !empty($maquiladora['logo'])) {
                $maquiladora['logo_base64'] = base64_encode($maquiladora['logo']);
            }
        }

        return view('modulos/reportes', [
            'title' => 'Reportes',
            'maquiladora' => $maquiladora,
            'notifCount' => 0 // You might want to fetch real notification count
        ]);
    }

    /**
     * Reporte de Eficiencia: Producción Planeada vs Real
     */
    public function produccionEficiencia()
    {
        $maquiladoraId = session()->get('maquiladora_id');

        // Mock logic for now, replacing with real DB calls
        // In a real scenario, we'd query OrdenProduccionModel
        // and compare cantidadPlan vs actual produced quantity (if tracked).
        // Since we only see 'cantidadPlan' in the model, we might need to sum up
        // completed items from another table or assume status='Terminado' means full quantity.

        $ordenes = $this->ordenProduccionModel->getListado($maquiladoraId);

        $labels = [];
        $dataPlan = [];
        $dataReal = [];

        // Limit to last 10 orders for readability
        $ordenes = array_slice($ordenes, 0, 10);

        foreach ($ordenes as $orden) {
            $labels[] = $orden['op']; // Folio
            // We need to fetch details to get quantity. getListado doesn't return quantity.
            // Let's optimize this in the future, for now fetching detail for each is slow but works.
            $detalle = $this->ordenProduccionModel->getDetalleBasico($orden['opId']);

            $plan = $detalle['cantidadPlan'] ?? 0;
            $real = ($orden['estatus'] === 'Terminado') ? $plan : 0; // Simplification

            $dataPlan[] = $plan;
            $dataReal[] = $real;
        }

        return $this->response->setJSON([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Planeado',
                    'data' => $dataPlan,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Real',
                    'data' => $dataReal,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    /**
     * Reporte Mensual: Producción por mes
     */
    public function produccionMensual()
    {
        $maquiladoraId = session()->get('maquiladora_id');
        $db = \Config\Database::connect();

        // Group by month of fechaInicioPlan
        $query = $db->table('orden_produccion')
            ->select("DATE_FORMAT(fechaInicioPlan, '%Y-%m') as mes, COUNT(*) as total_ordenes, SUM(cantidadPlan) as total_prendas")
            ->where('maquiladoraID', $maquiladoraId)
            ->groupBy('mes')
            ->orderBy('mes', 'ASC')
            ->limit(12)
            ->get();

        $results = $query->getResultArray();

        $labels = [];
        $dataOrdenes = [];
        $dataPrendas = [];

        foreach ($results as $row) {
            $labels[] = $row['mes'];
            $dataOrdenes[] = $row['total_ordenes'];
            $dataPrendas[] = $row['total_prendas'];
        }

        return $this->response->setJSON([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Prendas',
                    'data' => $dataPrendas,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Total Órdenes',
                    'data' => $dataOrdenes,
                    'borderColor' => 'rgb(53, 162, 235)',
                    'backgroundColor' => 'rgba(53, 162, 235, 0.5)',
                    'yAxisID' => 'y1',
                ]
            ]
        ]);
    }

    /**
     * Control de Calidad: Estatus de Inspecciones
     */
    public function calidadControl()
    {
        $maquiladoraId = session()->get('maquiladora_id');
        $db = \Config\Database::connect();

        try {
            // Unir con orden_produccion para filtrar por maquiladoraID
            $query = $db->table('inspeccion')
                ->select('inspeccion.resultado, COUNT(*) as total')
                ->join('orden_produccion', 'orden_produccion.id = inspeccion.ordenProduccionId')
                ->where('orden_produccion.maquiladoraID', $maquiladoraId)
                ->groupBy('inspeccion.resultado')
                ->get();
            $results = $query->getResultArray();
        } catch (\Exception $e) {
            $results = [];
        }

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($results as $row) {
            $label = $row['resultado'] ?: 'Sin Clasificar';
            $labels[] = $label;
            $data[] = $row['total'];

            // Assign colors
            if (stripos($label, 'Aprobado') !== false)
                $colors[] = '#28a745'; // Green
            elseif (stripos($label, 'Rechazado') !== false)
                $colors[] = '#dc3545'; // Red
            else
                $colors[] = '#ffc107'; // Yellow
        }

        if (empty($data)) {
            $labels = ['Sin Datos'];
            $data = [0];
            $colors = ['#e9ecef'];
        }

        return $this->response->setJSON([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ]
            ]
        ]);
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportar($tipo)
    {
        $filename = 'reporte_' . $tipo . '_' . date('Ymd') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");

        $out = fopen('php://output', 'w');

        // Add BOM for Excel UTF-8 compatibility
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if ($tipo === 'eficiencia') {
            fputcsv($out, ['Folio', 'Planeado', 'Real', 'Diferencia']);

            // Re-using logic (should be refactored to a service for DRY)
            $maquiladoraId = session()->get('maquiladora_id');
            $ordenes = $this->ordenProduccionModel->getListado($maquiladoraId);
            $ordenes = array_slice($ordenes, 0, 50); // Export more rows

            foreach ($ordenes as $orden) {
                $detalle = $this->ordenProduccionModel->getDetalleBasico($orden['opId']);
                $plan = $detalle['cantidadPlan'] ?? 0;
                $real = ($orden['estatus'] === 'Terminado') ? $plan : 0;

                fputcsv($out, [
                    $orden['op'],
                    $plan,
                    $real,
                    $plan - $real
                ]);
            }
        } elseif ($tipo === 'mensual') {
            fputcsv($out, ['Mes', 'Total Ordenes', 'Total Prendas']);

            $maquiladoraId = session()->get('maquiladora_id');
            $db = \Config\Database::connect();
            $query = $db->table('orden_produccion')
                ->select("DATE_FORMAT(fechaInicioPlan, '%Y-%m') as mes, COUNT(*) as total_ordenes, SUM(cantidadPlan) as total_prendas")
                ->where('maquiladoraID', $maquiladoraId)
                ->groupBy('mes')
                ->orderBy('mes', 'ASC')
                ->get();

            foreach ($query->getResultArray() as $row) {
                fputcsv($out, [
                    $row['mes'],
                    $row['total_ordenes'],
                    $row['total_prendas']
                ]);
            }
        } elseif ($tipo === 'calidad') {
            fputcsv($out, ['Resultado', 'Cantidad']);

            $db = \Config\Database::connect();
            $maquiladoraId = session()->get('maquiladora_id');
            try {
                $query = $db->table('inspeccion')
                    ->select('inspeccion.resultado, COUNT(*) as total')
                    ->join('orden_produccion', 'orden_produccion.id = inspeccion.ordenProduccionId')
                    ->where('orden_produccion.maquiladoraID', $maquiladoraId)
                    ->groupBy('inspeccion.resultado')
                    ->get();
                foreach ($query->getResultArray() as $row) {
                    fputcsv($out, [
                        $row['resultado'] ?: 'Sin Clasificar',
                        $row['total']
                    ]);
                }
            } catch (\Exception $e) {
                fputcsv($out, ['Error', 'No se pudieron obtener datos de inspección']);
            }
        }

        fclose($out);
        exit;
    }
    /**
     * Vista: Gestor de Hojas de Costos
     */
    public function costos()
    {
        $plantillaModel = new \App\Models\PlantillaOperacionModel();
        $maquiladoraId = session()->get('maquiladora_id');

        $plantillas = $plantillaModel->where('idmaquiladora', $maquiladoraId)->findAll();

        return view('modulos/reportes_costos', [
            'title' => 'Gestor de Costos',
            'plantillas' => $plantillas
        ]);
    }

    /**
     * Ver hoja de costos (Redirecciona al editor por ahora)
     */
    public function verCosto($id)
    {
        return redirect()->to(base_url("modulo3/control-bultos/plantillas/editor/$id"));
    }

    /**
     * Descargar / Imprimir hoja de costos
     */
    public function descargarCosto($id)
    {
        $plantillaModel = new \App\Models\PlantillaOperacionModel();
        $plantilla = $plantillaModel->find($id);

        if (!$plantilla) {
            return redirect()->back()->with('error', 'Hoja de costos no encontrada');
        }

        return view('modulos/reportes_costos_print', ['plantilla' => $plantilla]);
    }
}
