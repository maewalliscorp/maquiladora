<?php

use Config\Services;

$routes = Services::routes();

// Rutas del sistema
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Modulos');
$routes->setDefaultMethod('dashboard');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // declaramos todas las rutas explícitas

// ---------------------------
// Rutas de autenticación
// ---------------------------
$routes->get('/login',    'UsuarioController::login');
$routes->post('/login',   'UsuarioController::authenticate');
$routes->get('/logout',   'UsuarioController::logout');
$routes->get('/register', 'UsuarioController::register');
$routes->post('/register','UsuarioController::register');

// ---------------------------
// Ruta principal / dashboard
// ---------------------------
$routes->get('/',          'Modulos::dashboard');
// $routes->get('/dashboard', 'Modulos::dashboard', ['filter' => 'auth']);
$routes->get('/dashboard', 'Modulos::dashboard');

// ---------------------------
// Enlaces “legacy” (compatibilidad)
// ---------------------------
$routes->get('/perfilempleado',         'Modulos::m1_perfilempleado', ['filter' => 'auth']);
$routes->get('/pedidos',                'Modulos::m1_pedidos',        ['filter' => 'auth']);
$routes->get('/agregar_pedido',         'Modulos::m1_agregar',        ['filter' => 'auth']);
$routes->get('/editarpedido/(:num)',    'Modulos::m1_editar/$1',      ['filter' => 'auth']);
$routes->get('/detalle_pedido/(:num)',  'Modulos::m1_detalles/$1',    ['filter' => 'auth']);
$routes->get('/perfildisenador',        'Modulos::m2_perfildisenador', ['filter' => 'auth']);
$routes->get('/catalogodisenos',        'Modulos::m2_catalogodisenos', ['filter' => 'auth']);
$routes->get('/agregardiseno',          'Modulos::m2_agregardiseno',   ['filter' => 'auth']);
$routes->get('/editardiseno',           'Modulos::m2_editardiseno',    ['filter' => 'auth']);

// ---------------------------
// Grupo: módulo 1
// ---------------------------
$routes->group('modulo1', [], function ($routes) {
    $routes->get('/',               'Modulos::m1_index');
    $routes->get('pedidos',         'Modulos::m1_pedidos');
    $routes->get('produccion',      'Modulos::m1_produccion');
    $routes->get('agregar',         'Modulos::m1_agregar');
    $routes->post('agregar',        'Modulos::m1_agregar');
    $routes->get('editar/(:num)',   'Modulos::m1_editar/$1');
    $routes->post('editar',         'Modulos::m1_editar');
    $routes->get('detalles/(:num)', 'Modulos::m1_detalles/$1');
    $routes->get('perfilempleado',  'Modulos::m1_perfilempleado');
    $routes->get('ordenes',         'Modulos::m1_ordenes');

    // URL pública correcta dentro del grupo (evita /modulo1/modulo1/..)
    $routes->get('ordenes-produccion', 'Produccion::ordenes');

    // Evaluación
    $routes->get('evaluar/(:num)',       'Modulos::m1_evaluar/$1');
    $routes->post('guardar-evaluacion',  'Modulos::m1_guardarEvaluacion');
});

// ---------------------------
// Grupo: módulo 2
// ---------------------------
$routes->group('modulo2', [], function ($routes) {
    $routes->get('/',                   'Modulos::m2_index');
    $routes->get('perfildisenador',     'Modulos::m2_perfildisenador');
    $routes->get('catalogodisenos',     'Modulos::m2_catalogodisenos');
    $routes->get('agregardiseno',       'Modulos::m2_agregardiseno');
    $routes->post('agregardiseno',      'Modulos::m2_agregardiseno');
    $routes->get('editardiseno/(:num)', 'Modulos::m2_editardiseno/$1');
    $routes->post('actualizar/(:num)',  'Modulos::m2_actualizar/$1');
});

// ---------------------------
// Grupo: módulo 3 (Dashboard y Gestión)
// ---------------------------
$routes->group('modulo3', [], function ($routes) {
    $routes->get('/',                 'Modulos::dashboard');
    $routes->get('dashboard',         'Modulos::dashboard');
    $routes->get('ordenes',           'Modulos::ordenes');

    // WIP (dinámico)
    $routes->get('wip',                     'Wip::index');
    $routes->get('wip/json',                'Wip::json');     // ver datos que llegan a la vista
    $routes->get('wip/debug',               'Wip::debug');    // 🔍 escaneo de tablas/campos/filas
    $routes->post('wip/actualizar/(:num)',  'Wip::actualizar/$1');
    // actualizar avance

    $routes->get('incidencias',       'Modulos::incidencias');
    $routes->get('reportes',          'Modulos::reportes');
    $routes->get('notificaciones',    'Modulos::notificaciones');

    // Inspección (dinámica desde BD)
    $routes->get('inspeccion',                  'Inspeccion::index');               // listado
    $routes->get('inspeccion/evaluar/(:num)',   'Inspeccion::evaluar/$1');          // ver form
    $routes->post('inspeccion/evaluar/(:num)',  'Inspeccion::guardarEvaluacion/$1');// guardar

    // Vistas del módulo 3
    $routes->get('mrp',                   'Modulos::mrp');
    $routes->get('desperdicios',          'Modulos::desperdicios');

    // Inventario/Mantenimiento
    $routes->get('mantenimiento_inventario', 'Maquinaria::index');
    $routes->get('mantenimiento_preventivo', 'Modulos::mantenimientoPreventivo');
    $routes->get('mantenimiento_correctivo', 'Modulos::mantenimientoCorrectivo');
    $routes->get('logistica_preparacion',    'Modulos::logisticaPreparacion');
    $routes->get('logistica_gestion',        'Modulos::logisticaGestion');
    $routes->get('logistica_documentos',     'Modulos::logisticaDocumentos');

    // Maquinaria (CRUD)
    $routes->get('maquinaria',               'Maquinaria::index');
    $routes->post('maquinaria/guardar',      'Maquinaria::guardar');
    $routes->get('maquinaria/editar/(:num)', 'Maquinaria::editar/$1');
});

// ---------------------------
// Grupo: muestras
// ---------------------------
$routes->group('muestras', [], function ($routes) {
    $routes->get('/',                    'Modulos::muestras');
    $routes->get('evaluar/(:num)',       'Modulos::muestras_evaluar/$1');
    $routes->post('guardar-evaluacion',  'Modulos::muestras_guardarEvaluacion');
});

// ---------------------------
// Grupo: módulo 11 (Usuarios)
// ---------------------------
$routes->group('modulo11', [], function ($routes) {
    $routes->get('/',                 'Modulos::m11_usuarios');
    $routes->get('usuarios',          'Modulos::m11_usuarios');
    $routes->get('agregar',           'Modulos::m11_agregar_usuario');
    $routes->post('agregar',          'Modulos::m11_agregar_usuario');
    $routes->get('editar/(:num)',     'Modulos::m11_editar_usuario/$1');
    $routes->post('editar/(:num)',    'Modulos::m11_editar_usuario/$1');
});

// ---------------------------
// Ruta de prueba de BD (/dbcheck)
// ---------------------------
$routes->get('dbcheck', function () {
    $db  = \Config\Database::connect();
    try {
        $row = $db->query('SELECT COUNT(*) AS c FROM maquina')->getRowArray();
        return 'OK DB. Registros en maquina: ' . ($row['c'] ?? 0);
    } catch (\Throwable $e) {
        return 'ERROR DB: ' . $e->getMessage();
    }
});
