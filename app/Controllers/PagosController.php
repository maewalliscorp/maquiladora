<?php

namespace App\Controllers;

use App\Models\PagosModel;

class PagosController extends BaseController
{
    protected $pagosModel;

    public function __construct()
    {
        $this->pagosModel = new PagosModel();
    }

    /**
     * Vista principal de pagos - muestra todos los empleados con su forma de pago
     */
    public function index()
    {
        try {
            // Verificar que el usuario esté autenticado
            if (!session()->get('logged_in') && !session()->get('user_id')) {
                return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
            }

            // Obtener empleados de la maquiladora
            $empleados = $this->pagosModel->getEmpleadosPorMaquiladora();

            // Contadores por forma de pago (para evitar usar array_filter en la vista)
            $totalEmpleados = 0;
            $countDestajo   = 0;
            $countPorDia    = 0;
            $countPorHora   = 0;

            foreach ($empleados as $emp) {
                $totalEmpleados++;
                $fp = $emp['Forma_pago'] ?? '';
                if ($fp === 'Destajo') {
                    $countDestajo++;
                } elseif ($fp === 'Por dia') {
                    $countPorDia++;
                } elseif ($fp === 'Por hora') {
                    $countPorHora++;
                }
            }

            $data = [
                'title'          => 'Módulo 1 · Pagos de Empleados',
                'empleados'      => $empleados,
                'totalEmpleados' => $totalEmpleados,
                'countDestajo'   => $countDestajo,
                'countPorDia'    => $countPorDia,
                'countPorHora'   => $countPorHora,
                'notifCount'     => 0,
            ];

            return view('modulos/pagos', $data);
        } catch (\Throwable $e) {
            // Mostrar el mensaje de error directamente para depuración
            return $this->response->setStatusCode(500)
                ->setBody('Error en PagosController::index -> ' . $e->getMessage());
        }
    }

    /**
     * Obtener detalles de un empleado específico (JSON)
     */
    public function getEmpleado($id)
    {
        // Verificar que el usuario esté autenticado
        if (!session()->get('logged_in') && !session()->get('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $empleado = $this->pagosModel->getEmpleadoPorId($id);
        
        if (!$empleado) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Empleado no encontrado']);
        }

        return $this->response->setJSON(['success' => true, 'empleado' => $empleado]);
    }

    /**
     * Actualizar forma de pago de un empleado
     */
    public function actualizarFormaPago()
    {
        // Verificar que el usuario esté autenticado
        if (!session()->get('logged_in') && !session()->get('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }

        $empleadoId = $this->request->getPost('empleado_id');
        $formaPago = $this->request->getPost('forma_pago');

        if (!$empleadoId || !$formaPago) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        // Validar que la forma de pago sea válida
        $formasValidas = ['Destajo', 'Por dia', 'Por hora'];
        if (!in_array($formaPago, $formasValidas)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Forma de pago no válida']);
        }

        $result = $this->pagosModel->actualizarFormaPago($empleadoId, $formaPago);
        
        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Forma de pago actualizada correctamente']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error al actualizar la forma de pago']);
        }
    }

    /**
     * Exportar lista de empleados a Excel/CSV
     */
    public function exportar()
    {
        // Verificar que el usuario esté autenticado
        if (!session()->get('logged_in') && !session()->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $empleados = $this->pagosModel->getEmpleadosPorMaquiladora();

        // Preparar datos para exportación
        $exportData = [];
        foreach ($empleados as $empleado) {
            $exportData[] = [
                'No. Empleado' => $empleado['noEmpleado'],
                'Nombre Completo' => $empleado['nombre_completo'],
                'Forma de Pago' => $empleado['Forma_pago'],
                'Puesto' => $empleado['puesto'],
                'Email' => $empleado['email'],
                'Teléfono' => $empleado['telefono'],
                'Estatus' => $empleado['estatus_texto']
            ];
        }

        // Aquí podrías integrar una librería como PhpSpreadsheet para generar Excel
        // Por ahora, devolvemos JSON simple
        return $this->response->setJSON([
            'success' => true,
            'data' => $exportData,
            'message' => 'Datos preparados para exportación'
        ]);
    }
}
