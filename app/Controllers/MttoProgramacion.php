<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MttoProgramacionModel;
use App\Models\MaquinaModel;
use App\Models\EmpleadoModel;

class MttoProgramacion extends BaseController
{
    /**
     * Si alguien entra a /mtto/programacion sin método,
     * podríamos redirigir al calendario o a la lista.
     * (No es obligatorio usar esta ruta, el grupo en routes
     * apunta directo a lista() y calendario()).
     */
    public function index()
    {
        return redirect()->to(site_url('mtto/calendario'));
    }

    /**
     * Vista del calendario con el modal para programar mantenimiento.
     */
    public function calendario()
    {
        $maquinaModel  = new MaquinaModel();
        $empleadoModel = new EmpleadoModel();

        // Máquinas activas
        $maquinas = $maquinaModel
            ->where('activa', 1)
            ->orderBy('codigo', 'ASC')
            ->findAll();

        // Empleados activos (sin JOIN a tabla usuario)
        $empleados = $empleadoModel
            ->where('activo', 1)
            ->orderBy('apellido', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return view('modulos/mtto_calendario', [
            'title'     => 'Calendario de Mantenimiento',
            'maquinas'  => $maquinas,
            'empleados' => $empleados,
        ]);
    }

    /**
     * Vista tipo lista de todos los mantenimientos programados.
     */
    public function lista()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('mtto_programacion mp');
        $builder->select("
            mp.id,
            mp.titulo AS tarea,
            mp.fecha_inicio AS fecha,
            CONCAT(IFNULL(m.codigo, ''), ' ', IFNULL(m.modelo, '')) AS maquina,
            CONCAT(IFNULL(e.nombre, ''), ' ', IFNULL(e.apellido, '')) AS responsable
        ");
        $builder->join('maquina m', 'm.id = mp.maquina_id', 'left');
        $builder->join('empleado e', 'e.id = mp.responsable_id', 'left');
        $builder->orderBy('mp.fecha_inicio', 'ASC');

        $items = $builder->get()->getResultArray();

        return view('modulos/mtto_programacion', [
            'title' => 'Programación de Mantenimiento',
            'items' => $items,
        ]);
    }

    /**
     * Guarda la programación que viene del formulario del modal.
     */
    public function guardar()
    {
        $progModel     = new MttoProgramacionModel();
        $maquinaModel  = new MaquinaModel();
        $empleadoModel = new EmpleadoModel();

        // Datos del formulario
        $maquinaId     = $this->request->getPost('maquina_id');
        $responsableId = $this->request->getPost('responsable_id');
        $fecha         = $this->request->getPost('fecha_programada');
        $hora          = $this->request->getPost('hora_programada');
        $tipoMtto      = $this->request->getPost('tipo_mtto');   // Preventivo / Correctivo
        $prioridad     = $this->request->getPost('prioridad');   // Alta / Media / Baja
        $descripcion   = $this->request->getPost('descripcion');

        if (empty($fecha) || empty($maquinaId)) {
            session()->setFlashdata('msg_mtto', 'Debe seleccionar máquina y fecha para el mantenimiento.');
            return redirect()->back()->withInput();
        }

        // Construimos fecha_inicio / fecha_fin
        if (!empty($hora)) {
            $fechaInicio = "{$fecha} {$hora}:00"; // Y-m-d H:i:s
        } else {
            $fechaInicio = "{$fecha} 00:00:00";
        }
        $fechaFin = $fechaInicio; // luego puedes sumar duración si quieres

        // Color según prioridad (para el calendario)
        switch ($prioridad) {
            case 'Alta':
                $color = '#dc3545'; // rojo
                break;
            case 'Media':
                $color = '#ffc107'; // amarillo
                break;
            case 'Baja':
            default:
                $color = '#198754'; // verde
                break;
        }

        // Texto amigable de la máquina para mostrar en el título
        $maquinaTexto = 'Máquina #' . $maquinaId;
        $maq = $maquinaModel->find($maquinaId);
        if ($maq) {
            $codigo = $maq['codigo'] ?? null;
            $modelo = $maq['modelo'] ?? null;
            if ($codigo || $modelo) {
                $maquinaTexto = trim(($codigo ?: '') . ' ' . ($modelo ?: ''));
            }
        }

        // Opcional: nombre del responsable (por si luego lo quieres usar)
        $responsableTexto = '';
        if (!empty($responsableId)) {
            $emp = $empleadoModel->find($responsableId);
            if ($emp) {
                $responsableTexto = trim(($emp['nombre'] ?? '') . ' ' . ($emp['apellido'] ?? ''));
            }
        }

        // Título para el evento del calendario
        $titulo = trim(($tipoMtto ?: 'Mtto') . ' - ' . $maquinaTexto);

        $data = [
            'maquina_id'     => $maquinaId,
            'responsable_id' => $responsableId ?: null,
            'titulo'         => $titulo,
            'descripcion'    => $descripcion,
            'prioridad'      => $prioridad,
            'fecha_inicio'   => $fechaInicio,
            'fecha_fin'      => $fechaFin,
            'frecuencia'     => 'unico',
            'estado'         => 'Programado',
            'color'          => $color,
        ];

        try {
            $progModel->insert($data);
            session()->setFlashdata('msg_mtto', 'Mantenimiento programado correctamente.');
        } catch (\Throwable $e) {
            session()->setFlashdata('msg_mtto', 'Error al guardar mantenimiento: ' . $e->getMessage());
        }

        return redirect()->to(site_url('mtto/calendario'));
    }

    /**
     * Feed de eventos para FullCalendar.
     */
    public function apiEventos()
    {
        $model = new MttoProgramacionModel();

        $startParam = $this->request->getGet('start');
        $endParam   = $this->request->getGet('end');

        // Si no llegan, usamos mes actual
        if (!$startParam || !$endParam) {
            $inicioMes = date('Y-m-01 00:00:00');
            $finMes    = date('Y-m-t 23:59:59');
            $start     = $inicioMes;
            $end       = $finMes;
        } else {
            $start = date('Y-m-d H:i:s', strtotime($startParam));
            $end   = date('Y-m-d H:i:s', strtotime($endParam));
        }

        $rows = $model->eventsBetween($start, $end);

        $eventos = [];
        foreach ($rows as $row) {
            $eventos[] = [
                'id'    => $row['id'],
                'title' => $row['titulo'],
                'start' => $row['fecha_inicio'],
                'end'   => $row['fecha_fin'],
                'backgroundColor' => $row['color'] ?? '#0d6efd',
                'borderColor'     => $row['color'] ?? '#0d6efd',
                'extendedProps'   => [
                    'prioridad'      => $row['prioridad']      ?? null,
                    'estado'         => $row['estado']         ?? null,
                    'descripcion'    => $row['descripcion']    ?? null,
                    'maquina_id'     => $row['maquina_id']     ?? null,
                    'responsable_id' => $row['responsable_id'] ?? null,
                ],
            ];
        }

        return $this->response->setJSON($eventos);
    }
}
