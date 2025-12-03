<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ControlBultosModel;
use App\Models\PlantillaOperacionModel;
use App\Models\OperacionControlModel;
use App\Models\OrdenProduccionModel;
use App\Models\EmpleadoModel;
use App\Models\RegistroProduccionModel;

class ControlBultosController extends BaseController
{
    /**
     * Vista principal
     */
    public function index()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $controlModel = new ControlBultosModel();
        $plantillaModel = new PlantillaOperacionModel();
        $ordenModel = new OrdenProduccionModel();
        $empleadoModel = new EmpleadoModel();

        $data = [
            'controles' => $controlModel->getConMaquiladora($maquiladoraId),
            'plantillas' => $plantillaModel->getPlantillasPorMaquiladora($maquiladoraId),
            'ordenes' => $ordenModel->getListado($maquiladoraId),
            'empleados' => $empleadoModel->getEmpleadosActivos(),
        ];

        return view('modulos/control_bultos', $data);
    }

    /**
     * API: Listar controles
     */
    public function listar()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $controlModel = new ControlBultosModel();
        $controles = $controlModel->getConMaquiladora($maquiladoraId);

        return $this->response->setJSON([
            'ok' => true,
            'data' => $controles
        ]);
    }

    /**
     * API: Detalle de control
     */
    public function detalle($id)
    {
        $controlModel = new ControlBultosModel();
        $control = $controlModel->getDetallado($id);

        if (!$control) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Control no encontrado'
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'data' => $control
        ]);
    }

    /**
     * API: Obtener progreso y estado
     */
    public function progreso($id)
    {
        $controlModel = new ControlBultosModel();
        $operacionModel = new OperacionControlModel();

        $control = $controlModel->find($id);

        if (!$control) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Control no encontrado'
            ]);
        }

        $progresoGeneral = $controlModel->calcularProgresoGeneral($id);
        $listoParaArmado = $controlModel->verificarListoParaArmado($id);
        $estadisticas = $operacionModel->getEstadisticas($id);

        // Obtener lista de operaciones para la vista
        $operaciones = $operacionModel->where('controlBultoId', $id)->orderBy('orden', 'ASC')->findAll();

        return $this->response->setJSON([
            'ok' => true,
            'data' => [
                'estado' => $control['estado'],
                'progreso_general' => $progresoGeneral,
                'listo_para_armado' => $listoParaArmado,
                'estadisticas' => $estadisticas,
                'operaciones' => $operaciones
            ]
        ]);
    }

    /**
     * API: Crear control desde plantilla
     */
    public function crear()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');
        $usuarioId = $session->get('usuario_id') ?? $session->get('id');

        $data = [
            'idmaquiladora' => $maquiladoraId,
            'ordenProduccionId' => $this->request->getPost('ordenProduccionId'),
            'inspeccionId' => $this->request->getPost('inspeccionId'),
            'estilo' => $this->request->getPost('estilo'),
            'orden' => $this->request->getPost('orden'),
            'cantidad_total' => $this->request->getPost('cantidad_total'),
            'plantillaId' => $this->request->getPost('plantillaId'),
            'usuario_creacion' => $usuarioId,
        ];

        // Validaciones
        if (empty($data['ordenProduccionId']) || empty($data['cantidad_total'])) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'La orden de producción y cantidad total son requeridas'
            ]);
        }

        // Si no viene el folio de la orden, buscarlo
        if (empty($data['orden'])) {
            $ordenModel = new OrdenProduccionModel();
            $orden = $ordenModel->find($data['ordenProduccionId']);
            if ($orden) {
                $data['orden'] = $orden['folio'];
            } else {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Orden de producción no válida'
                ]);
            }
        }

        $controlModel = new ControlBultosModel();
        $controlId = $controlModel->crearControl($data);

        if ($controlId) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Control creado correctamente',
                'id' => $controlId
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear el control'
        ]);
    }

    /**
     * API: Editar control
     */
    public function editar($id)
    {
        $controlModel = new ControlBultosModel();

        $data = [
            'estilo' => $this->request->getPost('estilo'),
            'orden' => $this->request->getPost('orden'),
            'cantidad_total' => $this->request->getPost('cantidad_total'),
        ];

        if ($controlModel->update($id, $data)) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Control actualizado correctamente'
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar el control'
        ]);
    }

    /**
     * API: Eliminar control
     */
    public function eliminar($id)
    {
        $controlModel = new ControlBultosModel();

        if ($controlModel->delete($id)) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Control eliminado correctamente'
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo eliminar el control'
        ]);
    }

    /**
     * API: Registrar producción de empleado
     */
    public function registrarProduccion()
    {
        $session = session();
        $usuarioId = $session->get('usuario_id') ?? $session->get('id');

        $data = [
            'operacionControlId' => $this->request->getPost('operacionControlId'),
            'empleadoId' => $this->request->getPost('empleadoId'),
            'cantidad_producida' => $this->request->getPost('cantidad_producida'),
            'fecha_registro' => $this->request->getPost('fecha_registro') ?? date('Y-m-d'),
            'hora_inicio' => $this->request->getPost('hora_inicio'),
            'hora_fin' => $this->request->getPost('hora_fin'),
            'observaciones' => $this->request->getPost('observaciones'),
            'registrado_por' => $usuarioId,
        ];

        // Validaciones
        if (empty($data['operacionControlId']) || empty($data['empleadoId']) || empty($data['cantidad_producida'])) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Operación, empleado y cantidad son requeridos'
            ]);
        }

        // Validar que no se exceda la cantidad requerida
        $operacionModel = new OperacionControlModel();
        $operacion = $operacionModel->find($data['operacionControlId']);

        if ($operacion) {
            $nuevasCantidad = $operacion['piezas_completadas'] + $data['cantidad_producida'];
            if ($nuevasCantidad > $operacion['piezas_requeridas']) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'La cantidad excede las piezas requeridas. Máximo permitido: ' .
                        ($operacion['piezas_requeridas'] - $operacion['piezas_completadas'])
                ]);
            }
        }

        $registroModel = new RegistroProduccionModel();
        $resultado = $registroModel->registrarProduccion($data);

        if (is_array($resultado) && isset($resultado['ok']) && $resultado['ok']) {
            // Obtener estado actualizado
            $controlModel = new ControlBultosModel();
            $nuevoEstado = $controlModel->actualizarEstado($operacion['controlBultoId']);

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Producción registrada correctamente',
                'id' => $resultado['id'],
                'nuevo_estado' => $nuevoEstado
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo registrar la producción',
            'debug' => $resultado // Return full debug info
        ]);
    }

    /**
     * API: Ver registros de producción
     */
    public function registrosProduccion($controlId)
    {
        $registroModel = new RegistroProduccionModel();
        $registros = $registroModel->getRegistrosPorControl($controlId);

        return $this->response->setJSON([
            'ok' => true,
            'data' => $registros
        ]);
    }

    /**
     * API: Exportar a Excel
     */
    public function exportExcel($id)
    {
        $controlModel = new ControlBultosModel();
        $control = $controlModel->getDetallado($id);

        if (!$control) {
            return redirect()->back()->with('error', 'Control no encontrado');
        }

        // Aquí implementarías la lógica de exportación a Excel
        // usando PhpSpreadsheet o similar

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'Exportación a Excel en desarrollo'
        ]);
    }

    /**
     * API: Listar plantillas
     */
    public function listarPlantillas()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $plantillaModel = new PlantillaOperacionModel();
        $plantillas = $plantillaModel->getPlantillasPorMaquiladora($maquiladoraId);

        return $this->response->setJSON([
            'ok' => true,
            'data' => $plantillas
        ]);
    }

    /**
     * API: Crear plantilla
     */
    public function crearPlantilla()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $operaciones = $this->request->getPost('operaciones');

        // Si viene como JSON string, decodificar
        if (is_string($operaciones)) {
            $operaciones = json_decode($operaciones, true);
        }

        $data = [
            'idmaquiladora' => $maquiladoraId,
            'tipo_prenda' => $this->request->getPost('tipo_prenda'),
            'nombre_plantilla' => $this->request->getPost('nombre_plantilla'),
            'operaciones' => $operaciones,
            'activo' => 1,
        ];

        $plantillaModel = new PlantillaOperacionModel();
        $plantillaId = $plantillaModel->crearPlantilla($data);

        if ($plantillaId) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Plantilla creada correctamente',
                'id' => $plantillaId
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear la plantilla'
        ]);
    }

    /**
     * API: Editar plantilla
     */
    public function editarPlantilla($id)
    {
        $operaciones = $this->request->getPost('operaciones');

        // Si viene como JSON string, decodificar
        if (is_string($operaciones)) {
            $operaciones = json_decode($operaciones, true);
        }

        $data = [
            'tipo_prenda' => $this->request->getPost('tipo_prenda'),
            'nombre_plantilla' => $this->request->getPost('nombre_plantilla'),
            'operaciones' => $operaciones,
        ];

        $plantillaModel = new PlantillaOperacionModel();

        if ($plantillaModel->actualizarPlantilla($id, $data)) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Plantilla actualizada correctamente'
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar la plantilla'
        ]);
    }
    /**
     * Vista: Editor de Plantilla (Nueva)
     */
    public function nuevaPlantilla()
    {
        $plantillaModel = new PlantillaOperacionModel();
        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        $operacionesUnicas = $plantillaModel->getOperacionesUnicas($maquiladoraId);

        return view('modulos/plantilla_editor', [
            'plantilla' => [],
            'operacionesUnicas' => $operacionesUnicas
        ]);
    }

    /**
     * Vista: Editor de Plantilla (Existente)
     */
    public function editorPlantilla($id)
    {
        $plantillaModel = new PlantillaOperacionModel();
        $plantilla = $plantillaModel->find($id);

        if (!$plantilla) {
            return redirect()->back()->with('error', 'Plantilla no encontrada');
        }

        // Decodificar operaciones si es string
        if (is_string($plantilla['operaciones'])) {
            $plantilla['operaciones'] = json_decode($plantilla['operaciones'], true);
        }

        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        $operacionesUnicas = $plantillaModel->getOperacionesUnicas($maquiladoraId);

        return view('modulos/plantilla_editor', [
            'plantilla' => $plantilla,
            'operacionesUnicas' => $operacionesUnicas
        ]);
    }

    /**
     * API: Guardar Plantilla Completa
     */
    public function guardarPlantillaCompleta()
    {
        $plantillaModel = new PlantillaOperacionModel();

        $id = $this->request->getPost('id');
        $data = [
            'nombre_plantilla' => $this->request->getPost('nombre_plantilla'),
            'tipo_prenda' => $this->request->getPost('tipo_prenda'),
            'operaciones' => $this->request->getPost('operaciones'), // Ya viene como JSON string del frontend o array
            'idmaquiladora' => session()->get('maquiladora_id') ?? session()->get('maquiladoraID')
        ];

        if (empty($id)) {
            $plantillaModel->insert($data);
        } else {
            $plantillaModel->update($id, $data);
        }

        return $this->response->setJSON(['ok' => true, 'message' => 'Plantilla guardada correctamente']);
    }
}
