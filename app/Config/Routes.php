<?php

use Config\Services;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes = Services::routes();

/* --------------------------------------------------------------------
 * Rutas del sistema (NO tocar)
 * ------------------------------------------------------------------*/
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/* --------------------------------------------------------------------
 * Defaults
 * ------------------------------------------------------------------*/
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('UsuarioController');
$routes->setDefaultMethod('login');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // todo debe declararse explícitamente

/* --------------------------------------------------------------------
 * Auth
 * ------------------------------------------------------------------*/
$routes->get('/register',        'UsuarioController::index', ['as' => 'register']);
$routes->post('/register/store', 'UsuarioController::store');
$routes->get('/login',           'UsuarioController::login', ['as' => 'login']);
$routes->post('/login',          'UsuarioController::authenticate');
$routes->get('/logout',          'UsuarioController::logout');

// API sueltos
$routes->get('api/maquiladoras', 'UsuarioController::getMaquiladoras');

// Módulo 11 - Usuarios (acciones AJAX)
$routes->post('modulo11/eliminar_usuario',       'Modulos::m11_eliminar_usuario');
$routes->get ('modulo11/obtener_usuario/(:num)', 'Modulos::m11_obtener_usuario/$1');
$routes->post('modulo11/actualizar_usuario',     'Modulos::m11_actualizar_usuario');

/* =========================
 * Inventario de Almacenes
 * ========================= */
$routes->get   ('almacen/inventario',                'AlmacenController::inventario', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
$routes->get   ('api/almacenes',                     'AlmacenController::apiAlmacenes');
$routes->get   ('api/ubicaciones',                   'AlmacenController::apiUbicaciones');
$routes->get   ('api/inventario',                    'AlmacenController::apiInventario');
$routes->get   ('api/inventario/lotes',              'AlmacenController::apiLotes');
$routes->get   ('api/inventario/movimientos/(:num)', 'AlmacenController::apiMovimientos/$1');
$routes->post  ('api/inventario/agregar',            'AlmacenController::apiAgregar');
$routes->post  ('api/inventario/editar',             'AlmacenController::apiEditar');
$routes->delete('api/inventario/eliminar/(:num)',    'AlmacenController::apiEliminar/$1');
// extras
$routes->get   ('api/inventario/existe',                  'AlmacenController::apiExiste');
$routes->get   ('api/articulos/buscar',                   'AlmacenController::apiBuscarArticulos');
$routes->get   ('api/articulos/detalle',                  'AlmacenController::apiArticuloDetalle');
$routes->get   ('api/inventario/resumen-articulo/(:num)', 'AlmacenController::apiResumenArticulo/$1');

/* --------------------------------------------------------------------
 * Home / Auth landing
 * ------------------------------------------------------------------*/
$routes->get('/',          'UsuarioController::login');
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

/* --------------------------------------------------------------------
 * API (Dashboard y Clientes sin duplicados)
 * ------------------------------------------------------------------*/
$routes->group('api', static function ($routes) {
    $routes->get('dashboard',                 'Api::dashboard'); // ?range=30

    // Clientes (con CORS para POST si lo necesitas)
    $routes->match(['post','options'],'clientes/crear',           'Clientes::crear');
    $routes->match(['post','options'],'clientes/(:num)/editar',   'Clientes::actualizar/$1');
    $routes->match(['post','options'],'clientes/(:num)/eliminar', 'Clientes::eliminar/$1');

    // Lectura/detalle
    $routes->get('clientes/(:num)', 'Clientes::json_detalle/$1');
});

/* --------------------------------------------------------------------
 * Muestras (prototipos)
 * ------------------------------------------------------------------*/
$routes->get ('muestras',                'Muestras::index',      ['filter' => 'auth:Administrador,Jefe,Diseñador,Calidad,Inspector']);
$routes->post('muestras/data',           'Muestras::data',       ['filter' => 'auth:Administrador,Jefe,Diseñador,Calidad,Inspector']);
$routes->get ('muestras/evaluar/(:num)', 'Muestras::evaluar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador,Calidad,Inspector']);
$routes->post('muestras/guardar',        'Muestras::guardar',    ['filter' => 'auth:Administrador,Jefe,Diseñador,Calidad,Inspector']);
$routes->get ('muestras/archivo/(:num)', 'Muestras::archivo/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador,Calidad,Inspector']);

/* --------------------------------------------------------------------
 * Legacy
 * ------------------------------------------------------------------*/
$routes->get('/perfilempleado',        'Modulos::m1_perfilempleado', ['filter' => 'auth']);
$routes->get('/pedidos',               'Modulos::m1_pedidos',        ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
$routes->get('/agregar_pedido',        'Modulos::m1_agregar',        ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
$routes->get('/editarpedido/(:num)',   'Modulos::m1_editar/$1',      ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
$routes->get('/detalle_pedido/(:num)', 'Modulos::m1_detalles/$1',    ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
$routes->get('/perfildisenador',       'Modulos::m2_perfildisenador', ['filter' => 'auth']);
$routes->get('/catalogodisenos',       'Modulos::m2_catalogodisenos', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
$routes->get('/agregardiseno',         'Modulos::m2_agregardiseno',   ['filter' => 'auth:Administrador,Jefe,Diseñador']);

/* --------------------------------------------------------------------
 * Módulo 1
 * ------------------------------------------------------------------*/
$routes->group('modulo1', [], function ($routes) {
    $routes->get('/',               'Modulos::m1_index');
    $routes->get('pedidos',         'Modulos::m1_pedidos',    ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->get('produccion',      'Modulos::m1_produccion', ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte']);
    $routes->get('agregar',         'Modulos::m1_agregar',    ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->get('agregar_pedido',  'Modulos::m1_agregar',    ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->get('editar/(:num)',   'Modulos::m1_editar/$1',  ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->post('editar',         'Modulos::m1_editar',     ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->get('detalles/(:num)', 'Modulos::m1_detalles/$1',['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->get('perfilempleado',  'Modulos::m1_perfilempleado');
    $routes->get('ordenes',         'Modulos::m1_ordenes',    ['filter' => 'auth:Administrador,Jefe,Inspector,Empleado,Corte,RH']);
    $routes->post('empleado/guardar', 'Modulos::m1_empleado_guardar', ['filter' => 'auth']);
    // APIs / Producción
    $routes->get('pedido/(:num)/json',   'Modulos::m1_pedido_json/$1');
    $routes->get('pedido/(:num)/pdf',    'Modulos::m1_pedido_pdf/$1');
    $routes->get('pedido/(:num)/excel',  'Modulos::m1_pedido_excel/$1');
    $routes->get('clientes/json',        'Clientes::json_catalogo');
    $routes->get('ordenes-produccion',   'Produccion::ordenes');
    $routes->post('ordenes/estatus',     'Produccion::actualizarEstatus');
    $routes->post('ordenes/eliminar',    'Produccion::orden_eliminar');
    $routes->get('ordenes/(:num)/json',  'Produccion::orden_json/$1');
    // Tareas del empleado (producción)
    $routes->get('produccion/tareas',    'Produccion::tareas_empleado_json');
    // Tiempo de trabajo
    $routes->match(['post','options'],'produccion/tiempo/iniciar',   'Produccion::tiempo_trabajo_iniciar');
    $routes->match(['post','options'],'produccion/tiempo/finalizar', 'Produccion::tiempo_trabajo_finalizar');
    // Crear/Eliminar pedido (OC + OP)
    $routes->post('pedidos/crear',       'Modulos::m1_pedidos_crear',     ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);
    $routes->post('pedidos/eliminar',    'Modulos::m1_pedido_eliminar',   ['filter' => 'auth:Administrador,Jefe,Inspector,Diseñador,Empleado,Corte,Calidad,Envios']);

    // Endpoints para modales
    $routes->get('ordenes/folio/(:segment)/json', 'Produccion::orden_json_folio/$1');
    $routes->get('ordenes/(:num)/asignaciones',   'Produccion::asignaciones/$1');
    $routes->match(['post','options'],'ordenes/asignaciones/agregar',           'Produccion::asignaciones_agregar');
    $routes->match(['post','options'],'ordenes/asignaciones/agregar-multiple',  'Produccion::asignaciones_agregar_multiple');
    $routes->match(['post','options'],'ordenes/asignaciones/eliminar',          'Produccion::asignaciones_eliminar');

    // Evaluación
    $routes->get('evaluar/(:num)',      'Modulos::m1_evaluar/$1');
    $routes->post('guardar-evaluacion', 'Modulos::m1_guardarEvaluacion');
});

/* --------------------------------------------------------------------
 * Diag rápidos
 * ------------------------------------------------------------------*/
$routes->get('diag/ping', fn () => 'OK DIAG PING');
$routes->get('diag/agregar', function () {
    return view('modulos/agregar_pedido', ['title'=>'Diag · Agregar Pedido', 'notifCount'=>0]);
});
$routes->get('clientes', 'ClientesPage::index', ['filter' => 'auth:Administrador,Jefe,Empleado,Corte,Envios,Calidad,Almacenista,RH,Inspector,Diseñador']);

/* --------------------------------------------------------------------
 * Módulo 2
 * ------------------------------------------------------------------*/
$routes->group('modulo2', [], function ($routes) {
    $routes->get('/',                   'Modulos::m2_index', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->get('perfildisenador',     'Modulos::m2_perfildisenador', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->get('catalogodisenos',     'Modulos::m2_catalogodisenos', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->get('agregardiseno',       'Modulos::m2_agregardiseno',   ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('agregardiseno',      'Modulos::m2_agregardiseno',   ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->get('editardiseno/(:num)', 'Modulos::m2_editardiseno/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('actualizar/(:num)',  'Modulos::m2_actualizar/$1',   ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->match(['post','options'],'disenos/crear',                  'Modulos::m2_crear_diseno', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->match(['post','options'],'disenos/eliminar/(:num)',        'Modulos::m2_eliminar_diseno/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);

    // APIs
    $routes->get('diseno/(:num)/json',  'Modulos::m2_diseno_json/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->get('disenos/json',        'Disenos::json_catalogo',     ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->get('articulos/json',      'Modulos::m2_articulos_json', ['filter' => 'auth:Administrador,Jefe,Diseñador']);

    $routes->get ('catalogos/sexo',      'Modulos::m2_catalogo_sexo',        ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/sexo/crear', 'Modulos::m2_catalogo_sexo_crear',  ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/sexo/actualizar/(:num)', 'Modulos::m2_catalogo_sexo_actualizar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/sexo/eliminar/(:num)', 'Modulos::m2_catalogo_sexo_eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    
    $routes->get ('catalogos/tallas',    'Modulos::m2_catalogo_tallas',      ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tallas/crear', 'Modulos::m2_catalogo_tallas_crear',  ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tallas/actualizar/(:num)', 'Modulos::m2_catalogo_tallas_actualizar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tallas/eliminar/(:num)', 'Modulos::m2_catalogo_tallas_eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    
    $routes->get ('catalogos/tipo-corte','Modulos::m2_catalogo_tipo_corte',  ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tipo-corte/crear', 'Modulos::m2_catalogo_tipo_corte_crear',  ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tipo-corte/actualizar/(:num)', 'Modulos::m2_catalogo_tipo_corte_actualizar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tipo-corte/eliminar/(:num)', 'Modulos::m2_catalogo_tipo_corte_eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    
    $routes->get ('catalogos/tipo-ropa', 'Modulos::m2_catalogo_tipo_ropa',   ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tipo-ropa/crear', 'Modulos::m2_catalogo_tipo_ropa_crear',  ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tipo-ropa/actualizar/(:num)', 'Modulos::m2_catalogo_tipo_ropa_actualizar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
    $routes->post('catalogos/tipo-ropa/eliminar/(:num)', 'Modulos::m2_catalogo_tipo_ropa_eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Diseñador']);
});

/* --------------------------------------------------------------------
 * Rutas raíz · Mantenimiento Correctivo (alias directo)
 * ------------------------------------------------------------------*/
$routes->group('mantenimiento', static function($r){
    $r->get ('correctivo',                   'MantenimientoCorrectivo::index', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->post('correctivo/crear',             'MantenimientoCorrectivo::crear', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->post('correctivo/actualizar/(:num)', 'MantenimientoCorrectivo::actualizar/$1', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->post('correctivo/eliminar/(:num)',   'MantenimientoCorrectivo::eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Almacenista']);

    // JSON: Historial por máquina
    $r->get('correctivo/historial/maquina/(:num)', 'MantenimientoCorrectivo::historialPorMaquina/$1', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
});

/* --------------------------------------------------------------------
 * *** NUEVO ***  Notificaciones + Mantenimiento Programado
 * ------------------------------------------------------------------*/
// Notificaciones (vista nueva)
$routes->get ('notificaciones1',               'Notificaciones1::index', ['filter' => 'auth']);
$routes->post('notificaciones1/marcar/(:num)', 'Notificaciones1::marcar/$1', ['filter' => 'auth']);
$routes->post('notificaciones1/borrar/(:num)', 'Notificaciones1::borrar/$1', ['filter' => 'auth']);
// Compatibilidad con enlaces antiguos del menú
$routes->get('modulo3/notificaciones', 'Notificaciones1::index', ['filter' => 'auth']);

// Programación/Calendario/Alertas (mantenimiento preventivo)
$routes->get ('mtto/calendario',                  'MttoProgramacion::calendario',   ['filter' => 'auth']);
$routes->get ('mtto/programacion',                'MttoProgramacion::index',        ['filter' => 'auth']); // compat
$routes->get ('mtto/api/revisiones',              'MttoProgramacion::apiRevisiones',['filter' => 'auth']);

// “Cron” HTTP para generar instancias y notificaciones (proteger con token si lo deseas)
$routes->get ('mtto/alertas/run',                 'MttoAlertas::run');

/* --------------------------------------------------------------------
 * Módulo 3 (Dashboard, WIP, Inspección, Mantenimiento, Logística, MRP alias)
 * ------------------------------------------------------------------*/
$routes->group('modulo3', ['filter' => 'auth'], function ($routes) {

    $routes->get('/',         'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('ordenes',   'Modulos::ordenes');
    $routes->get('reportes',  'Modulos::reportes');

    // ===== MRP (ALIAS bajo /modulo3/mrp) =====
    $routes->group('mrp', function($r){
        $r->get('/',   'Mrp::index', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
        $r->get('diag','Mrp::diag');
        $r->post('requerimientos/guardar',       'Mrp::guardarRequerimiento',   ['filter' => 'auth:Administrador,Jefe,Almacenista']);
        $r->post('requerimientos/(:num)/editar', 'Mrp::editarRequerimiento/$1', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
        $r->get ('requerimientos/(:num)',        'Mrp::verReq/$1',              ['filter' => 'auth:Administrador,Jefe,Almacenista']);
        $r->post('ocs/guardar',                  'Mrp::guardarOc',              ['filter' => 'auth:Administrador,Jefe,Almacenista']);
        $r->post('ocs/(:num)/editar',            'Mrp::editarOc/$1',            ['filter' => 'auth:Administrador,Jefe,Almacenista']);
        $r->get ('ocs/(:num)',                   'Mrp::verOc/$1',               ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    });

    // Incidencias - modal ligero
    $routes->get ('incidencias/modal', 'Incidencias::modal', ['filter' => 'auth:Administrador,Jefe,Empleado,Almacenista,Calidad,Inspector,Diseñador']);

    // ===== ALIAS Calidad (Desperdicios & Reprocesos) bajo /modulo3 =====
    $routes->get ('desperdicios',                 'Calidad::desperdicios', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('desperdicios/guardar',         'Calidad::guardarDesecho', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->get ('desperdicios/(:num)',          'Calidad::verDesecho/$1',  ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('desperdicios/(:num)/editar',   'Calidad::editarDesecho/$1', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('reprocesos/guardar',           'Calidad::guardarReproceso', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->get ('reprocesos/(:num)',            'Calidad::verReproceso/$1',  ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('reprocesos/(:num)/editar',     'Calidad::editarReproceso/$1', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);

    // WIP
    $routes->get ('wip',                   'Wip::index',          ['filter' => 'auth:Administrador,Jefe,Inspector,Empleado,Corte']);
    $routes->get ('wip/json',              'Wip::json',           ['filter' => 'auth:Administrador,Jefe,Inspector,Empleado,Corte']);
    $routes->get ('wip/debug',             'Wip::debug',          ['filter' => 'auth:Administrador,Jefe,Inspector,Empleado,Corte']);
    $routes->post('wip/actualizar/(:num)', 'Wip::actualizar/$1',  ['filter' => 'auth:Administrador,Jefe,Inspector,Empleado,Corte']);

    // Inspección / Incidencias
    $routes->get ('incidencias',                 'Incidencias::index',     ['filter' => 'auth:Administrador,Jefe,Empleado,Corte,Almacenista,Calidad']);
    $routes->post('incidencias/crear',           'Incidencias::store',     ['filter' => 'auth:Administrador,Jefe,Empleado,Corte,Almacenista,Calidad']);
    $routes->get ('incidencias/eliminar/(:num)', 'Incidencias::delete/$1', ['filter' => 'auth:Administrador,Jefe,Empleado,Corte,Almacenista,Calidad']);

    // Grupo Inspección
    $routes->group('inspeccion', function($routes) {
        $routes->get ('/',                      'Inspeccion::index',   ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->get ('nueva',                  'Inspeccion::nueva',   ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('guardar',                'Inspeccion::guardar', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->get ('ver/(:num)',             'Inspeccion::ver/$1',  ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->get ('editar/(:num)',          'Inspeccion::editar/$1', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('actualizar/(:num)',      'Inspeccion::actualizar/$1', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->get ('eliminar/(:num)',        'Inspeccion::eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->get ('evaluar/(:num)',         'Inspeccion::evaluar/$1', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('evaluar/guardar/(:num)', 'Inspeccion::guardarEvaluacion/$1', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('actualizar-punto',       'Inspeccion::actualizarPunto', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        // CRUD puntos de inspección
        $routes->get ('puntos/json',            'Inspeccion::puntosJson', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('puntos/crear',           'Inspeccion::puntoCrear',  ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('puntos/editar',          'Inspeccion::puntoEditar', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
        $routes->post('puntos/eliminar',        'Inspeccion::puntoEliminar', ['filter' => 'auth:Administrador,Jefe,Inspector,Calidad']);
    });

    // Inventario / Mantenimiento (modulo3)
    $routes->get('mantenimiento_inventario', 'Maquinaria::index', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $routes->get('mantenimiento_preventivo', 'Modulos::mantenimientoPreventivo');
    $routes->get('mantenimiento_correctivo', fn () => redirect()->to(site_url('mantenimiento/correctivo')));

    // CRUD Maquinaria
    $routes->get ('maquinaria',                      'Maquinaria::index',       ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $routes->post('maquinaria/guardar',              'Maquinaria::guardar',     ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $routes->get ('maquinaria/editar/(:num)',        'Maquinaria::editar/$1',   ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $routes->post('maquinaria/actualizar/(:num)',    'Maquinaria::actualizar/$1',['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $routes->post('maquinaria/eliminar/(:num)',      'Maquinaria::eliminar/$1', ['filter' => 'auth:Administrador,Jefe,Almacenista']);

    /* =========================
     * LOGÍSTICA · PREPARACIÓN / PACKING
     * ========================= */
    $routes->get('logistica_preparacion', 'LogisticaController::preparacion', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->get('preparacion',           'LogisticaController::preparacion');

    // Embarques
    $routes->post('embarques/crear',                'LogisticaController::crearEmbarque', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->post('embarques/(:num)/agregar-orden', 'LogisticaController::agregarOrden/$1');
    $routes->get ('embarques/(:num)/packing-list',  'LogisticaController::packingList/$1');
    $routes->get ('embarques/(:num)/etiquetas',     'LogisticaController::etiquetas/$1');

    // Acciones por pedido (OC)
    $routes->get ('ordenes/(:num)/json',   'LogisticaController::ordenJson/$1');
    $routes->post('ordenes/(:num)/editar', 'LogisticaController::ordenEditar/$1');

    /* =========================
     * LOGÍSTICA · GESTIÓN (Tracking)
     * ========================= */
    $routes->get('logistica_gestion', 'LogisticaController::gestion', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->get('gestion',           'LogisticaController::gestion');

    // CRUD envíos
    $routes->post('envios/crear',           'LogisticaController::crearEnvio',   ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->get ('envios/(:num)/json',     'LogisticaController::envioJson/$1', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->post('envios/(:num)/editar',   'LogisticaController::editarEnvio/$1',['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->post('envios/(:num)/eliminar', 'LogisticaController::eliminarEnvio/$1',['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);

    /* =========================
     * LOGÍSTICA · DOCUMENTOS
     * ========================= */
    $routes->get ('documentos',                 'LogisticaController::documentos', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    $routes->get ('logistica_documentos',       'LogisticaController::documentos', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
    // CRUD documentos (doc_embarque)
    $routes->post('documentos/crear',           'LogisticaController::crearDocumento');
    $routes->get ('documentos/(:num)/json',     'LogisticaController::docJson/$1');
    $routes->post('documentos/(:num)/editar',   'LogisticaController::editarDocumento/$1');
    $routes->post('documentos/(:num)/eliminar', 'LogisticaController::eliminarDocumento/$1');
    $routes->get ('documentos/(:num)/pdf',      'LogisticaController::descargarPdf/$1');

    /* =========================
     * LOGÍSTICA · DOCUMENTO MANUAL (sin BD)
     * ========================= */
    $routes->get ('embarque/manual',        'LogisticaController::documentoManual');
    $routes->post('embarque/manual',        'LogisticaController::documentoManual');
    $routes->get ('embarque/manual/print',  'LogisticaController::documentoManualPrint');
    $routes->post('embarque/manual/print',  'LogisticaController::documentoManualPrint');

    // ===== Proxy Storage (fallback de listado desde el front) =====
    $routes->post('storage/list', 'StorageProxy::list');

    // Órdenes de clientes
    $routes->get('ordenesclientes', 'Modulos::m1_ordenesclientes', ['filter' => 'auth:Administrador,Jefe,Empleado,Corte,Envios,Calidad,Almacenista,RH,Inspector,Diseñador']);
});

/* --------------------------------------------------------------------
 * Calidad (Desperdicios & Reprocesos)
 * ------------------------------------------------------------------*/
$routes->group('calidad', [], function ($routes) {
    $routes->get('desperdicios', 'Calidad::desperdicios', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('desperdicios/guardar',        'Calidad::guardarDesecho',   ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->get ('desperdicios/(:num)',         'Calidad::verDesecho/$1',    ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('desperdicios/(:num)/editar',  'Calidad::editarDesecho/$1', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('reprocesos/guardar',          'Calidad::guardarReproceso', ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->get ('reprocesos/(:num)',           'Calidad::verReproceso/$1',  ['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
    $routes->post('reprocesos/(:num)/editar',    'Calidad::editarReproceso/$1',['filter' => 'auth:Administrador,Jefe,Calidad,Almacenista,Diseñador']);
});

/* --------------------------------------------------------------------
 * MRP (grupo raíz existente)
 * ------------------------------------------------------------------*/
$routes->group('mrp', [], function ($r) {
    $r->get('/',   'Mrp::index', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->get('diag','Mrp::diag');
    $r->post('requerimientos/guardar',       'Mrp::guardarRequerimiento',   ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->post('requerimientos/(:num)/editar', 'Mrp::editarRequerimiento/$1', ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->get ('requerimientos/(:num)',        'Mrp::verReq/$1',              ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->post('ocs/guardar',                  'Mrp::guardarOc',              ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->post('ocs/(:num)/editar',            'Mrp::editarOc/$1',            ['filter' => 'auth:Administrador,Jefe,Almacenista']);
    $r->get ('ocs/(:num)',                   'Mrp::verOc/$1',               ['filter' => 'auth:Administrador,Jefe,Almacenista']);
});

/* --------------------------------------------------------------------
 * Módulo 11 · Usuarios
 * ------------------------------------------------------------------*/
$routes->group('modulo11', ['filter' => 'auth:Administrador,Jefe,RH'], function ($routes) {
    $routes->get('roles',                   'Modulos::m11_roles');
    $routes->post('roles/actualizar',       'Modulos::m11_roles_actualizar');
    $routes->post('roles/agregar',          'Modulos::m11_roles_agregar');
    $routes->post('roles/eliminar',         'Modulos::m11_roles_eliminar');
    $routes->post('roles/permisos',         'Modulos::m11_roles_permisos');
    $routes->post('roles/guardar_permisos', 'Modulos::m11_roles_guardar_permisos');
    $routes->get('roles/inicializar_permisos', 'Modulos::m11_roles_inicializar_permisos');
    $routes->get('usuarios',                'Modulos::m11_usuarios');
    $routes->get('usuarios/agregar',        'Modulos::m11_agregar_usuario');
    $routes->post('usuarios/agregar',       'Modulos::m11_agregar_usuario');
    $routes->get('usuarios/editar/(:num)',  'Modulos::m11_editar_usuario/$1');
    $routes->post('usuarios/editar/(:num)', 'Modulos::m11_editar_usuario/$1');
});

/* --------------------------------------------------------------------
 * Utils DB (diagnóstico rápido)
 * ------------------------------------------------------------------*/
$routes->get('dbcheck', function () {
    $db = \Config\Database::connect();
    try {
        $row = $db->query('SELECT COUNT(*) AS c FROM maquina')->getRowArray();
        return 'OK DB. Registros en maquina: ' . ($row['c'] ?? 0);
    } catch (\Throwable $e) {
        return 'ERROR DB: ' . $e->getMessage();
    }
});
$routes->get('dbschema', function () {
    $db  = \Config\Database::connect();
    $out = [];
    try {
        $tables = $db->query('SHOW TABLES')->getResultArray();
        $out[] = '<h3>SHOW TABLES</h3><pre>' . print_r($tables, true) . '</pre>';
        $candidates = ['orden_compra','OrdenCompra','cliente','Cliente','orden_produccion','OrdenProduccion'];
        foreach ($candidates as $t) {
            try {
                $desc = $db->query('DESCRIBE ' . $t)->getResultArray();
                $out[] = '<h4>DESCRIBE ' . $t . '</h4><pre> ' . print_r($desc, true) . '</pre>';
            } catch (\Throwable $e) {
                $out[] = '<h4>DESCRIBE ' . $t . '</h4><pre>ERROR: ' . $e->getMessage() . '</pre>';
            }
        }
        return implode("\n", $out);
    } catch (\Throwable $e) {
        return 'ERROR DBSchema: ' . $e->getMessage();
    }
});
$routes->get('pedido-debug/(:num)', function ($id) {
    $db  = \Config\Database::connect();
    $out = [];
    try {
        $rowOc = null; $tablaOC = null;
        foreach (['orden_compra','OrdenCompra'] as $t) {
            try {
                $rowOc = $db->query('SELECT * FROM ' . $t . ' WHERE id = ?', [$id])->getRowArray();
                if ($rowOc) { $tablaOC = $t; break; }
            } catch (\Throwable $e) {}
        }
        $out[] = '<h3>Orden (' . ($tablaOC ?: 'no encontrada') . ')</h3><pre>' . print_r($rowOc, true) . '</pre>';
        $rowCli = null; $tablaCli = null;
        if ($rowOc && isset($rowOc['clienteId'])) {
            foreach (['cliente','Cliente'] as $t) {
                try {
                    $rowCli = $db->query('SELECT * FROM ' . $t . ' WHERE id = ?', [$rowOc['clienteId']])->getRowArray();
                    if ($rowCli) { $tablaCli = $t; break; }
                } catch (\Throwable $e) {}
            }
        }
        $out[] = '<h3>Cliente (' . ($tablaCli ?: 'no encontrada') . ')</h3><pre>' . print_r($rowCli, true) . '</pre>';
        return implode("\n", $out);
    } catch (\Throwable $e) {
        return 'ERROR PedidoDebug: ' . $e->getMessage();
    }
});

/* --------------------------------------------------------------------
 * API · Supabase Storage (guardar PDFs)
 * ------------------------------------------------------------------*/
$routes->group('api/storage', static function($r){
    $r->post('pdf', 'StorageController::guardarPdf'); // subir PDF a Doc_Embarque (opcional)
});

/* --------------------------------------------------------------------
 * Logística · Facturación (UI + acción de timbrado/mock)
 * ------------------------------------------------------------------*/
$routes->get (
    'logistica/embarque/(:num)/facturar/ui',
    'LogisticaController::facturarUI/$1',
    ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']
);
$routes->post(
    'logistica/embarque/(:num)/facturar',
    'LogisticaController::facturar/$1',
    ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']
);

/* --------------------------------------------------------------------
 * FACTURA DEMO (Preview HTML + PDF por GET usando sesión)
 * ------------------------------------------------------------------*/
$routes->get('logistica/factura',               'FacturaDemoController::preview',    ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
$routes->get('logistica/factura/(:num)',        'FacturaDemoController::preview/$1', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);
$routes->get('logistica/factura/(:num)/pdf',    'FacturaDemoController::pdf/$1',     ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad']);

/* --------------------------------------------------------------------
 * (Opcional) Endpoints POST si quieres mandar payload directo (no sesión)
 * ------------------------------------------------------------------*/
$routes->group('facturacion', ['filter' => 'auth:Administrador,Jefe,Envios,Almacenista,Calidad'], static function($r){
    // Espacio para futuras rutas POST (payload directo)
    // $r->post('demo/html', 'FacturaDemoController::html');
    // $r->post('demo/pdf',  'FacturaDemoController::pdfPost');
});
