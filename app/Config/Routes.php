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
$routes->get('/login',           'UsuarioController::login',   ['as' => 'login']);
$routes->post('/login',          'UsuarioController::authenticate');
$routes->get('/logout',          'UsuarioController::logout');

// API sueltos
$routes->get('api/maquiladoras', 'UsuarioController::getMaquiladoras');

// Módulo 11 - Usuarios (acciones AJAX)
$routes->post('modulo11/eliminar_usuario', 'Modulos::m11_eliminar_usuario');
$routes->get('modulo11/obtener_usuario/(:num)', 'Modulos::m11_obtener_usuario/$1');
$routes->post('modulo11/actualizar_usuario', 'Modulos::m11_actualizar_usuario');

/* =========================
 * Inventario de Almacenes
 * ========================= */
$routes->get   ('almacen/inventario',                'AlmacenController::inventario');
$routes->get   ('api/almacenes',                     'AlmacenController::apiAlmacenes');
$routes->get   ('api/ubicaciones',                   'AlmacenController::apiUbicaciones');
$routes->get   ('api/inventario',                    'AlmacenController::apiInventario');
$routes->get   ('api/inventario/lotes',              'AlmacenController::apiLotes');
$routes->get   ('api/inventario/movimientos/(:num)', 'AlmacenController::apiMovimientos/$1');
$routes->post  ('api/inventario/agregar',            'AlmacenController::apiAgregar');
$routes->post  ('api/inventario/editar',             'AlmacenController::apiEditar');
$routes->delete('api/inventario/eliminar/(:num)',    'AlmacenController::apiEliminar/$1');

// ===== NUEVAS RUTAS para spinner/búsqueda y validaciones =====
$routes->get   ('api/inventario/existe',                 'AlmacenController::apiExiste');
$routes->get   ('api/articulos/buscar',                  'AlmacenController::apiBuscarArticulos');
$routes->get   ('api/articulos/detalle',                 'AlmacenController::apiArticuloDetalle');
$routes->get   ('api/inventario/resumen-articulo/(:num)','AlmacenController::apiResumenArticulo/$1');

/* --------------------------------------------------------------------
 * Home / Auth landing
 * ------------------------------------------------------------------*/
$routes->get('/',          'UsuarioController::login');
$routes->get('/dashboard', 'Modulos::dashboard', ['filter' => 'auth']);

/* --------------------------------------------------------------------
 * API (Dashboard, etc.)
 * ------------------------------------------------------------------*/
$routes->group('api', static function ($routes) {
    $routes->get('dashboard', 'Api::dashboard'); // ?range=30
});

/* --------------------------------------------------------------------
 * Muestras (prototipos)
 * ------------------------------------------------------------------*/
$routes->get ('muestras',                'Muestras::index');
$routes->post('muestras/data',           'Muestras::data');
$routes->get ('muestras/evaluar/(:num)', 'Muestras::evaluar/$1');

/* --------------------------------------------------------------------
 * Legacy (enlaces antiguos, con filtro auth opcional)
 * ------------------------------------------------------------------*/
$routes->get('/perfilempleado',        'Modulos::m1_perfilempleado', ['filter' => 'auth']);
$routes->get('/pedidos',               'Modulos::m1_pedidos',        ['filter' => 'auth']);
$routes->get('/agregar_pedido',        'Modulos::m1_agregar',        ['filter' => 'auth']);
$routes->get('/editarpedido/(:num)',   'Modulos::m1_editar/$1',      ['filter' => 'auth']);
$routes->get('/detalle_pedido/(:num)', 'Modulos::m1_detalles/$1',    ['filter' => 'auth']);
$routes->get('/perfildisenador',       'Modulos::m2_perfildisenador', ['filter' => 'auth']);
$routes->get('/catalogodisenos',       'Modulos::m2_catalogodisenos', ['filter' => 'auth']);
$routes->get('/agregardiseno',         'Modulos::m2_agregardiseno',   ['filter' => 'auth']);
$routes->get('/editardiseno',          'Modulos::m2_editardiseno',    ['filter' => 'auth']);

/* --------------------------------------------------------------------
 * Módulo 1
 * ------------------------------------------------------------------*/
$routes->group('modulo1', [], function ($routes) {
    $routes->get('/',               'Modulos::m1_index');
    $routes->get('pedidos',         'Modulos::m1_pedidos');
    $routes->get('produccion',      'Modulos::m1_produccion');
    $routes->get('agregar',         'Modulos::m1_agregar');
    $routes->get('agregar_pedido',  'Modulos::m1_agregar');
    $routes->get('editar/(:num)',   'Modulos::m1_editar/$1');
    $routes->post('editar',         'Modulos::m1_editar');
    $routes->get('detalles/(:num)', 'Modulos::m1_detalles/$1');
    $routes->get('perfilempleado',  'Modulos::m1_perfilempleado');
    $routes->get('ordenes',         'Modulos::m1_ordenes');

    // APIs / Producción
    $routes->get('pedido/(:num)/json',   'Modulos::m1_pedido_json/$1');
    $routes->get('clientes/json',        'Clientes::json_catalogo');
    $routes->get('ordenes-produccion',   'Produccion::ordenes');
    $routes->post('ordenes/estatus',     'Produccion::actualizarEstatus');
    $routes->get('ordenes/(:num)/json',  'Produccion::orden_json/$1');

    // Endpoints para modales
    $routes->get('ordenes/folio/(:segment)/json', 'Produccion::orden_json_folio/$1');
    $routes->get('ordenes/(:num)/asignaciones',   'Produccion::asignaciones/$1');
    $routes->post('ordenes/asignaciones/agregar', 'Produccion::asignaciones_agregar');
    $routes->post('ordenes/asignaciones/agregar-multiple', 'Produccion::asignaciones_agregar_multiple');
    $routes->post('ordenes/asignaciones/eliminar',         'Produccion::asignaciones_eliminar');

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

/* --------------------------------------------------------------------
 * Módulo 2
 * ------------------------------------------------------------------*/
$routes->group('modulo2', [], function ($routes) {
    $routes->get('/',                   'Modulos::m2_index');
    $routes->get('perfildisenador',     'Modulos::m2_perfildisenador');
    $routes->get('catalogodisenos',     'Modulos::m2_catalogodisenos');
    $routes->get('agregardiseno',       'Modulos::m2_agregardiseno');
    $routes->post('agregardiseno',      'Modulos::m2_agregardiseno');
    $routes->get('editardiseno/(:num)', 'Modulos::m2_editardiseno/$1');
    $routes->post('actualizar/(:num)',  'Modulos::m2_actualizar/$1');
    $routes->post('disenos/crear',      'Modulos::m2_crear_diseno');

    // APIs
    $routes->get('diseno/(:num)/json',  'Modulos::m2_diseno_json/$1');
    $routes->get('disenos/json',        'Disenos::json_catalogo');
    $routes->get('articulos/json',      'Modulos::m2_articulos_json');
});

/* --------------------------------------------------------------------
 * Rutas raíz · Mantenimiento Correctivo (alias directo)
 * ------------------------------------------------------------------*/
$routes->group('mantenimiento', static function($r){
    $r->get ('correctivo',                   'MantenimientoCorrectivo::index');
    $r->post('correctivo/crear',             'MantenimientoCorrectivo::crear');
    $r->post('correctivo/actualizar/(:num)', 'MantenimientoCorrectivo::actualizar/$1');
    $r->post('correctivo/eliminar/(:num)',   'MantenimientoCorrectivo::eliminar/$1');
});

/* --------------------------------------------------------------------
 * Módulo 3 (Dashboard, WIP, Inspección, Mantenimiento, Logística)
 * ------------------------------------------------------------------*/
$routes->group('modulo3', ['filter' => 'auth'], function ($routes) {

    $routes->get('/',         'Modulos::dashboard');
    $routes->get('dashboard', 'Modulos::dashboard');
    $routes->get('ordenes',   'Modulos::ordenes');

    // WIP
    $routes->get ('wip',                    'Wip::index');
    $routes->get ('wip/json',               'Wip::json');
    $routes->get ('wip/debug',              'Wip::debug');
    $routes->post('wip/actualizar/(:num)',  'Wip::actualizar/$1');

    // Inspección / Incidencias
    $routes->get ('incidencias',                 'Incidencias::index');
    $routes->post('incidencias/crear',           'Incidencias::store');
    $routes->get ('incidencias/eliminar/(:num)', 'Incidencias::delete/$1');

    // Grupo Inspección
    $routes->group('inspeccion', function($routes) {
        $routes->get('/', 'Inspeccion::index');
        $routes->get('nueva', 'Inspeccion::nueva');
        $routes->post('guardar', 'Inspeccion::guardar');
        $routes->get('ver/(:num)', 'Inspeccion::ver/$1');
        $routes->get('editar/(:num)', 'Inspeccion::editar/$1');
        $routes->post('actualizar/(:num)', 'Inspeccion::actualizar/$1');
        $routes->get('eliminar/(:num)', 'Inspeccion::eliminar/$1');
        $routes->get('evaluar/(:num)', 'Inspeccion::evaluar/$1');
        $routes->post('evaluar/guardar/(:num)', 'Inspeccion::guardarEvaluacion/$1');
    });

    // Aliases
    $routes->get('mrp',          fn () => redirect()->to(site_url('mrp')));
    $routes->get('desperdicios', fn () => redirect()->to(site_url('calidad/desperdicios')));

    // Inventario / Mantenimiento
    $routes->get('mantenimiento_inventario', 'Maquinaria::index');
    $routes->get('mantenimiento_preventivo', 'Modulos::mantenimientoPreventivo');

    // Compatibilidad enlace viejo
    $routes->get('mantenimiento_correctivo', fn () => redirect()->to(site_url('mantenimiento/correctivo')));

    // CRUD Maquinaria
    $routes->get ('maquinaria',                 'Maquinaria::index');
    $routes->post('maquinaria/guardar',         'Maquinaria::guardar');
    $routes->get ('maquinaria/editar/(:num)',   'Maquinaria::editar/$1');
    $routes->post('maquinaria/eliminar/(:num)', 'Maquinaria::eliminar/$1');

    // Mantenimiento Correctivo (prefijo /modulo3)
    $routes->group('mantenimiento', function($r){
        $r->get ('correctivo',                   'MantenimientoCorrectivo::index');
        $r->get ('correctivo/diag',              'MantenimientoCorrectivo::diag');
        $r->get ('correctivo/probe',             'MantenimientoCorrectivo::probe');
        $r->post('correctivo/crear',             'MantenimientoCorrectivo::crear');
        $r->post('correctivo/actualizar/(:num)', 'MantenimientoCorrectivo::actualizar/$1');
    });

    /* =========================
     * LOGÍSTICA · PREPARACIÓN / PACKING
     * ========================= */
    $routes->get('logistica_preparacion', 'LogisticaController::preparacion');
    $routes->get('preparacion',           'LogisticaController::preparacion');

    // Embarques
    $routes->post('embarques/crear',                'LogisticaController::crearEmbarque');
    $routes->post('embarques/(:num)/agregar-orden', 'LogisticaController::agregarOrden/$1');
    $routes->get ('embarques/(:num)/packing-list',  'LogisticaController::packingList/$1');
    $routes->get ('embarques/(:num)/etiquetas',     'LogisticaController::etiquetas/$1');

    // Acciones por pedido (OC)
    $routes->get ('ordenes/(:num)/json',   'LogisticaController::ordenJson/$1');
    $routes->post('ordenes/(:num)/editar', 'LogisticaController::ordenEditar/$1');

    /* =========================
     * LOGÍSTICA · GESTIÓN (Tracking)
     * ========================= */
    $routes->get('logistica_gestion', 'LogisticaController::gestion');
    $routes->get('gestion',           'LogisticaController::gestion');

    // CRUD envíos
    $routes->post('envios/crear',           'LogisticaController::crearEnvio');
    $routes->get ('envios/(:num)/json',     'LogisticaController::envioJson/$1');
    $routes->post('envios/(:num)/editar',   'LogisticaController::editarEnvio/$1');
    $routes->post('envios/(:num)/eliminar', 'LogisticaController::eliminarEnvio/$1');

    /* =========================
     * LOGÍSTICA · DOCUMENTOS
     * ========================= */
    $routes->get ('documentos',                 'LogisticaController::documentos');   // alias principal
    $routes->get ('logistica_documentos',       'LogisticaController::documentos');   // compat

    // CRUD documentos (doc_embarque)
    $routes->post('documentos/crear',           'LogisticaController::crearDocumento');
    $routes->get ('documentos/(:num)/json',     'LogisticaController::docJson/$1');
    $routes->post('documentos/(:num)/editar',   'LogisticaController::editarDocumento/$1');
    $routes->post('documentos/(:num)/eliminar', 'LogisticaController::eliminarDocumento/$1');
    $routes->get ('documentos/(:num)/pdf',      'LogisticaController::descargarPdf/$1');

    /* =========================
     * LOGÍSTICA · DOCUMENTO MANUAL (sin BD)
     * ========================= */
    $routes->get ('embarque/manual',        'LogisticaController::documentoManual');        // captura + vista previa
    $routes->post('embarque/manual',        'LogisticaController::documentoManual');        // procesa POST y vuelve a la misma
    $routes->get ('embarque/manual/print',  'LogisticaController::documentoManualPrint');   // vista SOLO documento (para imprimir)
    $routes->post('embarque/manual/print',  'LogisticaController::documentoManualPrint');   // idem por POST

    // Órdenes de clientes (enlace del menú)
    $routes->get('ordenesclientes', 'Modulos::m1_ordenesclientes');
});

/* --------------------------------------------------------------------
 * Calidad (Desperdicios & Reprocesos)
 * ------------------------------------------------------------------*/
$routes->group('calidad', [], function ($routes) {
    $routes->get('desperdicios', 'Calidad::desperdicios');

    // Desechos
    $routes->post('desperdicios/guardar',        'Calidad::guardarDesecho');
    $routes->get ('desperdicios/(:num)',         'Calidad::verDesecho/$1');
    $routes->post('desperdicios/(:num)/editar',  'Calidad::editarDesecho/$1');

    // Reprocesos
    $routes->post('reprocesos/guardar',          'Calidad::guardarReproceso');
    $routes->get ('reprocesos/(:num)',           'Calidad::verReproceso/$1');
    $routes->post('reprocesos/(:num)/editar',    'Calidad::editarReproceso/$1');

    // Diagnóstico
    $routes->get ('desperdicios/diag',           'Calidad::diag');
});

/* --------------------------------------------------------------------
 * MRP
 * ------------------------------------------------------------------*/
$routes->group('mrp', [], function ($r) {
    $r->get('/',   'Mrp::index');
    $r->get('diag','Mrp::diag');

    // Endpoints opcionales
    $r->post('requerimientos/guardar',       'Mrp::guardarRequerimiento');
    $r->post('requerimientos/(:num)/editar', 'Mrp::editarRequerimiento/$1');
    $r->get ('requerimientos/(:num)',        'Mrp::verReq/$1');

    $r->post('ocs/guardar',                  'Mrp::guardarOc');
    $r->post('ocs/(:num)/editar',            'Mrp::editarOc/$1');
    $r->get ('ocs/(:num)',                   'Mrp::verOc/$1');
});

/* --------------------------------------------------------------------
 * Módulo 11 · Usuarios
 * ------------------------------------------------------------------*/
$routes->group('modulo11', [], function ($routes) {
    $routes->get('roles', 'Modulos::m11_roles');
    $routes->post('roles/actualizar', 'Modulos::m11_roles_actualizar');
    $routes->post('roles/agregar', 'Modulos::m11_roles_agregar');
    $routes->get('usuarios',                 'Modulos::m11_usuarios');
    $routes->get('usuarios/agregar',         'Modulos::m11_agregar_usuario');
    $routes->post('usuarios/agregar',        'Modulos::m11_agregar_usuario');
    $routes->get('usuarios/editar/(:num)',   'Modulos::m11_editar_usuario/$1');
    $routes->post('usuarios/editar/(:num)',  'Modulos::m11_editar_usuario/$1');
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
