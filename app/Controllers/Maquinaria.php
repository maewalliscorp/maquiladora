<?php
namespace App\Controllers;

use App\Models\MaquinaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Maquinaria extends BaseController
{
    /**
     * Lista el inventario (usado por /modulo3/mantenimiento_inventario).
     */
    public function index()
    {
        $model    = new MaquinaModel();
        $maquinas = $model->orderBy('codigo','ASC')->findAll();
        $maquinas = $model->withEstado($maquinas);

        // Adaptamos al formato que la vista espera
        $maq = array_map(function ($m) {
            return [
                'id'         => $m['id'] ?? null,
                'cod'        => $m['codigo'] ?? '',
                'modelo'     => $m['modelo'] ?? '',
                'fabricante' => $m['fabricante'] ?? '',
                'serie'      => $m['serie'] ?? '',
                'compra'     => $m['fechaCompra'] ?? '',
                'ubic'       => $m['ubicacion'] ?? '',
                'estado'     => $m['estado_txt'] ?? 'Operativa',
            ];
        }, $maquinas);

        return view('modulos/mantenimiento_inventario', [
            'title' => 'Inventario de Maquinaria',
            'maq'   => $maq
        ]);
    }

    /**
     * Guarda una nueva máquina desde el formulario superior.
     * (Ruta: POST /modulo3/maquinaria/guardar)
     */
    public function guardar()
    {
        // Recoger sólo los campos permitidos
        $data = $this->request->getPost([
            'codigo','modelo','fabricante','serie','fechaCompra','ubicacion','activa'
        ]);

        // Normalizar estado a 1/0
        $data['activa'] = $data['activa'] ?? 'Operativa';
        $data['activa'] = ($data['activa'] === 'Operativa' || $data['activa'] === '1') ? 1 : 0;

        // Normalizar fecha si vino como dd/mm/aaaa
        if (!empty($data['fechaCompra']) && strpos($data['fechaCompra'], '/') !== false) {
            [$d,$m,$y] = explode('/', $data['fechaCompra']);
            if (@checkdate((int)$m, (int)$d, (int)$y)) {
                $data['fechaCompra'] = sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }

        // Validación sencilla (opcional, puedes ampliar reglas)
        $rules = [
            'codigo'  => 'required|min_length[3]|max_length[50]',
            'modelo'  => 'required|min_length[2]|max_length[120]',
            'ubicacion' => 'permit_empty|max_length[100]',
            'fechaCompra' => 'permit_empty|valid_date[Y-m-d]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            $model = new MaquinaModel();
            $model->insert([
                'codigo'      => $data['codigo'] ?? '',
                'modelo'      => $data['modelo'] ?? '',
                'fabricante'  => $data['fabricante'] ?? null,
                'serie'       => $data['serie'] ?? null,
                'fechaCompra' => $data['fechaCompra'] ?? null,
                'ubicacion'   => $data['ubicacion'] ?? null,
                'activa'      => $data['activa'],
            ]);

            // Redirige a la URL "bonita" que mapea a este módulo
            return redirect()->to(base_url('modulo3/mantenimiento_inventario'))
                ->with('success', 'Máquina guardada correctamente.');
        } catch (\Throwable $e) {
            log_message('error', 'Guardar Máquina: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar. Revisa los datos.');
        }
    }

    /**
     * Carga un registro para editar (vista opcional).
     * (Ruta: GET /modulo3/maquinaria/editar/{id})
     */
    public function editar($id)
    {
        $model = new MaquinaModel();
        $row   = $model->find($id);
        if (!$row) {
            throw new PageNotFoundException('Máquina no encontrada');
        }

        $row['estado_txt'] = ((int)$row['activa'] === 1) ? 'Operativa' : 'En reparación';

        return view('modulos/maquinaria_editar', ['m' => $row]);
    }
}
