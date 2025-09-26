<?php

namespace App\Controllers;

class Modulos extends BaseController
{
    /**
     * Datos base para todas las vistas (notificaciones y usuario).
     */
    private function payload(array $data = []): array
    {
        $base = [
            'notifCount' => $data['notifCount'] ?? 0,
            'userEmail'  => session()->get('email') ?: 'admin@fabrica.com',
        ];
        return array_merge($base, $data);
    }

    /**
     * Alias a dashboard por compatibilidad.
     */
    public function index()
    {
        return $this->dashboard();
    }

    /* =========================================================
     *                   MÓDULO 3 si tu
     * ========================================================= */

    public function dashboard()
    {
        $kpis = [
            ['label' => 'Órdenes Activas',     'value' => 8,  'color' => 'primary'],
            ['label' => 'WIP (%)',             'value' => 62, 'color' => 'info'],
            ['label' => 'Incidencias Hoy',     'value' => 3,  'color' => 'danger'],
            ['label' => 'Órdenes Completadas', 'value' => 21, 'color' => 'success'],
        ];


        return view('modulos/dashboard', $this->payload([
            'title' => 'Módulo 3 · Dashboard',
            'kpis'  => $kpis,
            'notifCount' => 3,
        ]));
    }

    public function ordenes()
    {
        $ordenes = [
            ['op'=>'OP-0001','cliente'=>'Textiles MX','responsable'=>'Juan Pérez','ini'=>'2025-09-20','fin'=>'2025-09-25','estatus'=>'En proceso'],
            ['op'=>'OP-0002','cliente'=>'Fábrica Sur','responsable'=>'María López','ini'=>'2025-09-21','fin'=>'2025-09-27','estatus'=>'Planificada'],
            ['op'=>'OP-0003','cliente'=>'Industrias PZ','responsable'=>'Carlos Ruiz','ini'=>'2025-09-19','fin'=>'2025-09-24','estatus'=>'En proceso'],
        ];

        return view('modulos/ordenes', $this->payload([
            'title'   => 'Órdenes',
            'ordenes' => $ordenes,
            'notifCount' => 0,
        ]));
    }

    public function wip()
    {
        $etapas = [
            ['etapa'=>'Corte','resp'=>'Juan Pérez','ini'=>'2025-09-20','fin'=>'2025-09-22','prog'=>80],
            ['etapa'=>'Confección','resp'=>'María López','ini'=>'2025-09-22','fin'=>'2025-09-25','prog'=>45],
            ['etapa'=>'Acabado','resp'=>'Carlos Ruiz','ini'=>'2025-09-25','fin'=>'2025-09-27','prog'=>10],
        ];

        return view('modulos/wip', $this->payload([
            'title'      => 'WIP',
            'etapas'     => $etapas,
            'notifCount' => 0,
        ]));
    }

    public function incidencias()
    {
        $lista = [
            ['fecha'=>'2025-09-21','op'=>'OP-0001','tipo'=>'Paro de máquina','desc'=>'Mantenimiento no programado'],
            ['fecha'=>'2025-09-22','op'=>'OP-0003','tipo'=>'Falta de material','desc'=>'Faltan rollos de tela'],
        ];

        return view('modulos/incidencias', $this->payload([
            'title'      => 'Incidencias',
            'lista'      => $lista,
            'notifCount' => count($lista),
        ]));
    }

    public function reportes()
    {
        return view('modulos/reportes', $this->payload([
            'title'      => 'Reportes',
            'notifCount' => 0,
        ]));
    }

    public function notificaciones()
    {
        $items = [
            ['nivel'=>'Crítica','color'=>'#e03131','titulo'=>'Actualizar avance WIP en OP-2025-014','sub'=>'Atrasado 1 día • Módulo: Confección (WIP)'],
            ['nivel'=>'Alta','color'=>'#ffd43b','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Vence hoy • Módulo: Prototipos'],
            ['nivel'=>'Media','color'=>'#4dabf7','titulo'=>'Revisar muestra M-0045 del cliente A','sub'=>'Módulo: Prototipos'],
        ];

        return view('modulos/notificaciones', $this->payload([
            'title'      => 'Notificaciones',
            'items'      => $items,
            'notifCount' => count($items),
        ]));
    }

    public function mrp()
    {
        $reqs = [
            ['mat'=>'Tela Algodón 180g','u'=>'m','necesidad'=>1200,'stock'=>450,'comprar'=>750],
            ['mat'=>'Hilo 40/2','u'=>'rollo','necesidad'=>35,'stock'=>10,'comprar'=>25],
            ['mat'=>'Etiqueta talla','u'=>'pz','necesidad'=>1000,'stock'=>1200,'comprar'=>0],
        ];
        $ocs = [
            ['prov'=>'Textiles MX','mat'=>'Tela Algodón 180g','cant'=>750,'u'=>'m','eta'=>'2025-10-02'],
            ['prov'=>'Hilos del Norte','mat'=>'Hilo 40/2','cant'=>25,'u'=>'rollo','eta'=>'2025-09-30'],
        ];

        return view('modulos/mrp', $this->payload([
            'title'      => 'MRP',
            'reqs'       => $reqs,
            'ocs'        => $ocs,
            'notifCount' => 0,
        ]));
    }

    public function desperdicios()
    {
        $desp = [
            ['fecha'=>'2025-09-20','op'=>'OP-0012','mat'=>'Tela','cant'=>'15 m','motivo'=>'Manchas'],
            ['fecha'=>'2025-09-21','op'=>'OP-0010','mat'=>'Piezas','cant'=>'8 pz','motivo'=>'Corte chueco'],
        ];
        $rep = [
            ['op'=>'OP-0014','tarea'=>'Costura lateral','pend'=>25,'resp'=>'María','eta'=>'2025-09-24'],
            ['op'=>'OP-0011','tarea'=>'Rebasteado','pend'=>10,'resp'=>'Luis','eta'=>'2025-09-23'],
        ];

        return view('modulos/desperdicios', $this->payload([
            'title'      => 'Desperdicios y Reprocesos',
            'desp'       => $desp,
            'rep'        => $rep,
            'notifCount' => 2,
        ]));
    }

    /* =========================================================
     *                   MÓDULO 3 · Mantenimiento
     * ========================================================= */

    public function mantenimientoInventario()
    {
        $maq = [
            ['cod'=>'MC-0001','modelo'=>'Juki DDL-8700','compra'=>'2022-01-10','ubic'=>'Línea 1','estado'=>'Operativa'],
            ['cod'=>'MC-0002','modelo'=>'Brother 8450','compra'=>'2021-07-05','ubic'=>'Línea 3','estado'=>'En reparación'],
        ];

        return view('modulos/mantenimiento_inventario', $this->payload([
            'title'      => 'Mantenimiento · Inventario',
            'maq'        => $maq,
            'notifCount' => 0,
        ]));
    }

    public function mantenimientoPreventivo()
    {
        $prox = [
            ['fecha'=>'2025-09-25','maq'=>'MC-0001','tarea'=>'Lubricación','resp'=>'Carlos','estado'=>'Próximo'],
            ['fecha'=>'2025-09-28','maq'=>'MC-0002','tarea'=>'Ajuste correa','resp'=>'Ana','estado'=>'Programado'],
        ];

        return view('modulos/dashboard', $this->payload([
            'title'      => 'Mantenimiento · Preventivo',
            'prox'       => $prox,
            'notifCount' => 0,
        ]));
    }

    public function mantenimientoCorrectivo()
    {
        $hist = [
            ['fecha'=>'2025-09-20','maq'=>'MC-0002','falla'=>'Correa rota','accion'=>'Reemplazo','estado'=>'Cerrada'],
            ['fecha'=>'2025-09-22','maq'=>'MC-0003','falla'=>'Vibración','accion'=>'Ajuste base','estado'=>'En reparación'],
        ];

        return view('modulos/mantenimiento_correctivo', $this->payload([
            'title'      => 'Mantenimiento · Correctivo',
            'hist'       => $hist,
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                    MÓDULO 3 · Logística
     * ========================================================= */

    public function logisticaPreparacion()
    {
        $cons = [
            ['pedido'=>'PED-0041','op'=>'OP-0011','cajas'=>3,'peso'=>25,'dest'=>'Cliente B'],
            ['pedido'=>'PED-0042','op'=>'OP-0012','cajas'=>6,'peso'=>54,'dest'=>'Cliente C'],
        ];

        return view('modulos/logistica_preparacion', $this->payload([
            'title'      => 'Logística · Preparación de Envíos',
            'cons'       => $cons,
            'notifCount' => 0,
        ]));
    }

    public function logisticaGestion()
    {
        $env = [
            ['fecha'=>'2025-09-21','empresa'=>'DHL','guia'=>'JD0148899001','estado'=>'En tránsito'],
            ['fecha'=>'2025-09-22','empresa'=>'FedEx','guia'=>'FE99223311','estado'=>'Entregado'],
        ];

        return view('modulos/logistica_gestion', $this->payload([
            'title'      => 'Logística · Gestión de Envíos',
            'env'        => $env,
            'notifCount' => 0,
        ]));
    }

    public function logisticaDocumentos()
    {
        $docs = [
            ['tipo'=>'Factura','num'=>'FAC-2025-001','fecha'=>'2025-09-21','estado'=>'Emitida'],
            ['tipo'=>'Lista de empaque','num'=>'PL-2025-009','fecha'=>'2025-09-21','estado'=>'Emitida'],
        ];

        return view('modulos/logistica_documentos', $this->payload([
            'title'      => 'Logística · Documentos de Embarque',
            'docs'       => $docs,
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                       MÓDULO 1
     * ========================================================= */

    public function m1_index()
    {
        return $this->m1_pedidos();
    }

    public function m1_pedidos()
    {
        $pedidos = [
            ['id' => 1, 'empresa' => 'Textiles MX', 'descripcion' => 'Camisetas básicas', 'estatus' => 'En proceso'],
            ['id' => 2, 'empresa' => 'Fábrica Sur', 'descripcion' => 'Pantalones vaqueros', 'estatus' => 'Pendiente'],
            ['id' => 3, 'empresa' => 'Industrias PZ', 'descripcion' => 'Chamarras de mezclilla', 'estatus' => 'Completado'],
        ];

        return view('modulos/pedidos', $this->payload([
            'title'      => 'Módulo 1 · Pedidos',
            'pedidos'    => $pedidos,
            'notifCount' => 0,
        ]));
    }

    public function m1_ordenes()
    {
        $ordenes = [
            ['op' => 'OP-0001', 'cliente' => 'Textiles MX', 'responsable' => 'Juan Pérez', 'ini' => '2025-09-20', 'fin' => '2025-09-25', 'estatus' => 'En proceso'],
            ['op' => 'OP-0002', 'cliente' => 'Fábrica Sur', 'responsable' => 'María López', 'ini' => '2025-09-21', 'fin' => '2025-09-27', 'estatus' => 'Planificada'],
            ['op' => 'OP-0003', 'cliente' => 'Industrias PZ', 'responsable' => 'Carlos Ruiz', 'ini' => '2025-09-19', 'fin' => '2025-09-24', 'estatus' => 'En proceso'],
        ];

        return view('modulos/m1_ordenes', $this->payload([
            'title'      => 'Módulo 1 · Órdenes',
            'ordenes'    => $ordenes,
            'notifCount' => 0,
        ]));
    }

    public function m1_agregar()
    {
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario
            return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido agregado correctamente');
        }

        return view('modulos/agregar_pedido', $this->payload([
            'title'      => 'Módulo 1 · Agregar Pedido',
            'notifCount' => 0,
        ]));
    }

    public function m1_editar($id = null)
    {
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario
            return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido actualizado correctamente');
        }

        $pedido = [
            'id' => $id,
            'empresa' => 'Textiles MX',
            'descripcion' => 'Camisetas básicas',
            'estatus' => 'En proceso',
            'fecha_creacion' => '2025-01-15',
            'fecha_entrega' => '2025-01-30',
        ];

        return view('modulos/editarpedido', $this->payload([
            'title'      => 'Módulo 1 · Editar Pedido',
            'pedido'     => $pedido,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }

    public function m1_detalles($id = null)
    {
        $pedido = [
            'id' => $id,
            'empresa' => 'Textiles MX',
            'descripcion' => 'Camisetas básicas',
            'estatus' => 'En proceso',
            'fecha_creacion' => '2025-01-15',
            'fecha_entrega' => '2025-01-30',
            'cantidad' => 1000,
            'precio_unitario' => 25.50,
            'total' => 25500.00,
        ];

        return view('modulos/detalle_pedido', $this->payload([
            'title'      => 'Módulo 1 · Detalle del Pedido',
            'pedido'     => $pedido,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }

    public function m1_perfilempleado()
    {
        $empleado = [
            'nombre' => session()->get('user_name') ?? 'Admin',
            'email' => session()->get('user_email') ?? 'admin@fabrica.com',
            'rol' => session()->get('user_role') ?? 'admin',
            'departamento' => 'Administración',
            'fecha_ingreso' => '2024-01-15',
            'telefono' => '+52 555 123 4567',
        ];

        return view('modulos/perfilempleado', $this->payload([
            'title'      => 'Módulo 1 · Perfil de Empleado',
            'empleado'   => $empleado,
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                       MÓDULO 2
     * ========================================================= */

    public function m2_index()
    {
        return $this->m2_perfildisenador();
    }

    public function m2_perfildisenador()
    {
        $disenador = [
            'nombre' => session()->get('user_name') ?? 'Diseñador',
            'email' => session()->get('user_email') ?? 'diseñador@fabrica.com',
            'especialidad' => 'Diseño Textil',
            'experiencia' => '5 años',
            'proyectos_completados' => 45,
            'proyectos_activos' => 3,
        ];

        return view('modulos/perfildisenador', $this->payload([
            'title'      => 'Módulo 2 · Perfil del Diseñador',
            'disenador'  => $disenador,
            'notifCount' => 0,
        ]));
    }

    public function m2_catalogodisenos()
    {
        $disenos = [
            ['id' => 1, 'nombre' => 'Camiseta Básica', 'categoria' => 'Ropa Casual', 'estatus' => 'Activo', 'fecha_creacion' => '2025-01-10'],
            ['id' => 2, 'nombre' => 'Pantalón Vaquero', 'categoria' => 'Denim', 'estatus' => 'Activo', 'fecha_creacion' => '2025-01-12'],
            ['id' => 3, 'nombre' => 'Chamarra Deportiva', 'categoria' => 'Deportiva', 'estatus' => 'En revisión', 'fecha_creacion' => '2025-01-15'],
        ];

        return view('modulos/catalogodisenos', $this->payload([
            'title'      => 'Módulo 2 · Catálogo de Diseños',
            'disenos'    => $disenos,
            'notifCount' => 0,
        ]));
    }

    public function m2_agregardiseno()
    {
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario
            return redirect()->to('/modulo2/catalogodisenos')->with('success', 'Diseño agregado correctamente');
        }

        return view('modulos/agregardiseno', $this->payload([
            'title'      => 'Módulo 2 · Agregar Diseño',
            'notifCount' => 0,
        ]));
    }

    public function m2_editardiseno($id = null)
    {
        // Validar que se proporcione un ID
        if (!$id) {
            return redirect()->to('/modulo2/catalogodisenos')->with('error', 'ID de diseño no válido');
        }

        // Datos de ejemplo del diseño (en una aplicación real, estos vendrían de la base de datos)
        $diseno = [
            'id' => $id,
            'nombre' => 'Camiseta Básica',
            'descripcion' => 'Diseño clásico de camiseta básica con corte moderno',
            'materiales' => 'Algodón 100%, hilo de poliéster',
            'cortes' => 'Corte recto, manga corta, cuello redondo',
            'archivo' => 'uploads/diseños/camiseta_basica.jpg', // Ruta de ejemplo
            'categoria' => 'Ropa Casual',
            'estatus' => 'Activo',
            'fecha_creacion' => '2025-01-10',
        ];

        return view('modulos/editardiseno', $this->payload([
            'title'      => 'Módulo 2 · Editar Diseño',
            'diseno'     => $diseno,
            'id'         => $id,
            'notifCount' => 0,
        ]));
    }

    public function m2_actualizar($id = null)
    {
        // Validar que se proporcione un ID
        if (!$id) {
            return redirect()->to('/modulo2/catalogodisenos')->with('error', 'ID de diseño no válido');
        }

        // Obtener datos del formulario
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'materiales' => $this->request->getPost('materiales'),
            'cortes' => $this->request->getPost('cortes'),
        ];

        // Procesar archivo si se subió uno nuevo
        $file = $this->request->getFile('archivo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Crear directorio si no existe
            $uploadPath = WRITEPATH . 'uploads/diseños/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Generar nombre único para el archivo
            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);
            
            // Guardar la ruta del archivo
            $data['archivo'] = 'uploads/diseños/' . $newName;
        }

        // Aquí iría la lógica para actualizar en la base de datos
        // Por ahora, solo simulamos la actualización
        
        return redirect()->to('/modulo2/catalogodisenos')->with('success', 'Diseño actualizado correctamente');
    }
}
