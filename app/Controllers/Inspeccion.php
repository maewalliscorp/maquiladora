<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Inspeccion extends BaseController
{
    /**
     * Muestra la lista de inspecciones registradas.
     */
    protected $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionModel();
        helper(['form', 'url']);
    }
    public function index()
    {
        $db = db_connect();
        $builder = $db->table('inspeccion i')
            ->select('i.id AS inspeccionId, i.ordenProduccionId, i.puntoInspeccionId, i.resultado, i.observaciones, i.inspectorId, i.fecha,')
            ->select('pi.tipo as punto_inspeccion, r.id AS reprocesoId, r.accion, r.cantidad, r.fecha AS fechaReproceso')
            ->join('punto_inspeccion pi', 'pi.id = i.puntoInspeccionId', 'left')
            ->join('reproceso r', 'i.id = r.inspeccionId', 'left')
            ->orderBy('i.id', 'DESC')
            ->orderBy('r.fecha', 'DESC');

        $inspecciones = $builder->get()->getResultArray();

        $lista = [];
        $n = 1;
        $processedIds = [];

        foreach ($inspecciones as $row) {
            // Si ya procesamos esta inspección, la saltamos
            if (in_array($row['inspeccionId'], $processedIds)) {
                continue;
            }

            $item = [
                'num' => $n++,
                'id' => $row['inspeccionId'] ?? '',
                'inspeccionId' => $row['inspeccionId'] ?? '',
                'numero_inspeccion' => 'INSP-' . str_pad($row['inspeccionId'], 5, '0', STR_PAD_LEFT),
                'ordenProduccionId' => $row['ordenProduccionId'] ?? 'N/A',
                'puntoInspeccionId' => $row['punto_inspeccion'] ?? 'N/A',
                'inspectorId' => $row['inspectorId'] ?? 'N/A',
                'fecha' => $row['fecha'] ?? null,
                'resultado' => $row['resultado'] ?? 'Pendiente',
                'observaciones' => $row['observaciones'] ?? ''
            ];

            // Si hay información de reproceso, la agregamos
            if (!empty($row['reprocesoId'])) {
                $item['reprocesoId'] = $row['reprocesoId'];
                $item['accion'] = $row['accion'] ?? '';
                $item['cantidad'] = $row['cantidad'] ?? 0;
                $item['fechaReproceso'] = $row['fechaReproceso'] ?? null;
            }

            $lista[] = $item;
            $processedIds[] = $row['inspeccionId'];
        }

        // Obtener la lista de puntos de inspección para el dropdown
        $puntosInspeccion = $db->table('punto_inspeccion')
            ->select('id, tipo')
            ->orderBy('tipo', 'ASC')
            ->get()
            ->getResultArray();

        return view('modulos/inspeccion', [
            'title' => 'Inspecciones',
            'lista' => $lista,
            'puntosInspeccion' => $puntosInspeccion
        ]);
    }

    public function nueva()
    {
        return view('modulos/inspeccion_form', [
            'title' => 'Nueva Inspección',
            'validation' => \Config\Services::validation()
        ]);
    }

    public function ver($id)
    {
        $inspeccion = $this->inspeccionModel->getDetalle($id);

        if (!$inspeccion) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        return view('modulos/inspeccion_ver', [
            'title' => 'Detalles de Inspección',
            'inspeccion' => $inspeccion
        ]);
    }

    public function editar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);

        if (!$inspeccion) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        return view('modulos/inspeccion_form', [
            'title' => 'Editar Inspección',
            'inspeccion' => $inspeccion,
            'validation' => \Config\Services::validation()
        ]);
    }

    public function actualizarPunto()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida'
            ]);
        }

        $id = $this->request->getPost('id');
        $puntoInspeccionId = $this->request->getPost('puntoInspeccionId');

        if (empty($id) || empty($puntoInspeccionId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        // Obtener el tipo de punto de inspección
        $punto = $this->db->table('punto_inspeccion')
            ->select('tipo')
            ->where('id', $puntoInspeccionId)
            ->get()
            ->getRowArray();

        if (!$punto) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Punto de inspección no encontrado'
            ]);
        }

        // Actualizar la inspección
        $this->db->table('inspeccion')
            ->where('id', $id)
            ->update([
                'puntoInspeccionId' => $puntoInspeccionId,
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'success' => true,
            'punto_tipo' => $punto['tipo']
        ]);
    }

    public function guardar()
    {
        $rules = [
            'orden_produccion_id' => 'required',
            'punto_inspeccion_id' => 'required',
            'fecha' => 'required|valid_date',
            'inspector_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'ordenProduccionId' => $this->request->getPost('orden_produccion_id'),
            'puntoInspeccionId' => $this->request->getPost('punto_inspeccion_id'),
            'inspectorId' => $this->request->getPost('inspector_id'),
            'fecha' => $this->request->getPost('fecha'),
            'resultado' => $this->request->getPost('resultado') ?? 'Pendiente',
            'observaciones' => $this->request->getPost('observaciones'),
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];

        $this->inspeccionModel->insert($data);

        return redirect()->to(base_url('inspeccion'))
            ->with('success', 'Inspección registrada correctamente.');
    }

    public function actualizar($id)
    {
        if (!$this->inspeccionModel->find($id)) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        $rules = [
            'orden_produccion_id' => 'required',
            'punto_inspeccion_id' => 'required',
            'fecha' => 'required|valid_date',
            'inspector_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'ordenProduccionId' => $this->request->getPost('orden_produccion_id'),
            'puntoInspeccionId' => $this->request->getPost('punto_inspeccion_id'),
            'inspectorId' => $this->request->getPost('inspector_id'),
            'fecha' => $this->request->getPost('fecha'),
            'resultado' => $this->request->getPost('resultado') ?? 'Pendiente',
            'observaciones' => $this->request->getPost('observaciones'),
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        $this->inspeccionModel->update($id, $data);

        return redirect()->to(base_url('inspeccion'))
            ->with('success', 'Inspección actualizada correctamente.');
    }

    public function eliminar($id)
    {
        if (!$this->inspeccionModel->find($id)) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to(base_url('inspeccion'))
            ->with('success', 'Inspección eliminada correctamente.');
    }

    /**
     * Muestra el detalle y formulario de evaluación de una inspección.
     */
    public function evaluar(int $id)
    {
        $model = new InspeccionModel();
        $inspeccion = $model->getDetalle($id);

        if (!$inspeccion) {
            throw new PageNotFoundException('Inspección no encontrada');
        }

        return view('modulos/inspeccion_evaluar', [
            'title' => 'Evaluación de inspección',
            'i'     => $inspeccion,
        ]);
    }

    /**
     * Guarda la evaluación de una inspección, incluyendo sus defectos
     */
    public function guardarEvaluacion(int $id)
    {
        // Solo permitir peticiones AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Método no permitido'
            ])->setStatusCode(405);
        }

        $model = new InspeccionModel();
        $inspeccion = $model->find($id);

        if (!$inspeccion) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Inspección no encontrada'
            ])->setStatusCode(404);
        }

        // Obtener los datos del formulario
        $data = $this->request->getPost();
        $defectos = json_decode($this->request->getPost('defectos') ?? '[]', true);

        // Validar los datos
        $rules = [
            'resultado' => 'required|in_list[aprobado,rechazado,pendiente]',
            'fecha'     => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        // Validar que si es rechazado, tenga al menos un defecto
        if (($data['resultado'] === 'rechazado') && empty($defectos)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe registrar al menos un defecto para una inspección rechazada'
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Actualizar la inspección
            $model->update($id, [
                'resultado'     => $data['resultado'],
                'observaciones' => $data['observaciones'] ?? null,
                'fecha'         => $data['fecha'],
            ]);

            // Si hay defectos, guardarlos
            if (!empty($defectos) && $data['resultado'] === 'rechazado') {
                // Eliminar defectos existentes
                $db->table('inspeccion_defecto')->where('inspeccion_id', $id)->delete();

                // Insertar nuevos defectos
                foreach ($defectos as $defecto) {
                    $db->table('inspeccion_defecto')->insert([
                        'inspeccion_id'    => $id,
                        'defecto_id'       => $this->getDefectoIdByTipo($defecto['tipo']),
                        'descripcion'      => $defecto['descripcion'],
                        'cantidad'         => $defecto['cantidad'],
                        'accion_correctiva' => $defecto['accion_correctiva'],
                        'fecha_registro'   => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al guardar los datos');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Evaluación guardada correctamente'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar la evaluación: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Obtiene el ID de un defecto por su tipo
     */
    private function getDefectoIdByTipo(string $tipo): ?int
    {
        $db = \Config\Database::connect();
        $defecto = $db->table('defecto')
            ->select('id')
            ->where('nombre', $tipo)
            ->get()
            ->getRowArray();

        return $defecto ? (int)$defecto['id'] : null;
    }

    /**
     * Endpoint para obtener los datos de inspecciones en formato JSON
     * Si se proporciona un ID, devuelve los detalles de esa inspección con sus defectos
     */
    public function json($id = null)
    {
        $model = new InspeccionModel();

        if ($id === null) {
            // Obtener todas las inspecciones con información relacionada
            $inspecciones = $model->getListadoCompleto();

            return $this->response->setJSON([
                'success' => true,
                'data' => $inspecciones
            ]);
        } else {
            // Obtener los detalles de la inspección específica con información relacionada
            $inspeccion = $model->getDetalle($id);

            if (!$inspeccion) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Inspección no encontrada'
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $inspeccion
            ]);
        }
    }
}
