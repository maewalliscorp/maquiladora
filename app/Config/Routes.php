<?php

use Config\Services;

$routes = Services::routes();

// --------------------------------------------------------------------
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

// --------------------------------------------------------------------
// Defaults
// --------------------------------------------------------------------
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Modulos');
$routes->setDefaultMethod('dashboard');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// --------------------------------------------------------------------
// Auth
// --------------------------------------------------------------------
$routes->get('/login',     'UsuarioController::login');
$routes->post('/login',    'UsuarioController::authenticate');
$routes->get('/logout',    'UsuarioController::logout');
$routes->get('/register',  'UsuarioController::register');
$routes->post('/register', 'UsuarioController::register');

// --------------------------------------------------------------------
// Home
// --------------------------------------------------------------------
$routes->get('/',          'Modulos::dashboard');
$routes->get('/dashboard', 'Modulos::dashboard');

// --------------------------------------------------------------------
// Muestras (prototipos)
// --------------------------------------------------------------------
$routes->get('muestras',                    'Modulos::muestras');
$routes->get('muestras/evaluar/(:num)',     'Modulos::muestras_evaluar/$1');
$routes->post('muestras/guardar',           'Modulos::muestras_guardarEvaluacion');

// --------------------------------------------------------------------
// Legacy
// --------------------------------------------------------------------
$routes->get('/perfilempleado',        'Modulos::m1_perfilempleado', ['filter' => 'auth']);
$routes->get('/pedidos',               'Modulos::m1_pedidos',        ['filter' => 'auth']);
$routes->get('/agregar_pedido',        'Modulos::m1_agregar',        ['filter' => 'auth']);
$routes->get('/editarpedido/(:num)',   'Modulos::m1_editar/$1',      ['filter' => 'auth']);
$routes->get('/detalle_pedido/(:num)', 'Modulos::m1_detalles/$1',    ['filter' => 'auth']);
$routes->get('/perfildisenador',       'Modulos::m2_perfildisenador', ['filter' => 'auth']);
$routes->get('/catalogodisenos',       'Modulos::m2_catalogodisenos', ['filter' => 'auth']);
$routes->get('/agregardiseno',         'Modulos::m2_agregardiseno',   ['filter' => 'auth']);
$routes->get('/editardiseno',          'Modulos::m2_editardiseno',    ['filter' => 'auth']);

// --------------------------------------------------------------------
// Módulo 1
// --------------------------------------------------------------------
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

    $routes->get('pedido/(:num)/json', 'Modulos::m1_pedido_json/$1');
    $routes->get('clientes/json',      'Clientes::json_catalogo');

    $routes->get('ordenes-produccion', 'Produccion::ordenes');
    $routes->post('ordenes/estatus',   'Produccion::actualizarEstatus');
    $routes->get('ordenes/(:num)/json','Produccion::orden_json/$1');
    $routes->get('evaluar/(:num)',       'Modulos::m1_evaluar/$1');
    $routes->post('guardar-evaluacion',  'Modulos::m1_guardarEvaluacion');
});

// --------------------------------------------------------------------
// Diag
// --------------------------------------------------------------------
$routes->get('diag/ping', function () { return 'OK DIAG PING'; });
$routes->get('diag/agregar', function () {
    return view('modulos/agregar_pedido', ['title'=>'Diag · Agregar Pedido', 'notifCount'=>0]);
});

// --------------------------------------------------------------------
// Módulo 2
// --------------------------------------------------------------------
$routes->group('modulo2', [], function ($routes) {
    $routes->get('/',                   'Modulos::m2_index');
    $routes->get('perfildisenador',     'Modulos::m2_perfildisenador');
    $routes->get('catalogodisenos',     'Modulos::m2_catalogodisenos');
    $routes->get('agregardiseno',       'Modulos::m2_agregardiseno');
    $routes->post('agregardiseno',      'Modulos::m2_agregardiseno');
    $routes->get('editardiseno/(:num)', 'Modulos::m2_editardiseno/$1');
    $routes->post('actualizar/(:num)',  'Modulos::m2_actualizar/$1');

    $routes->get('diseno/(:num)/json',  'Modulos::m2_diseno_json/$1');
    $routes->get('disenos/json',        'Disenos::json_catalogo');
});

// --------------------------------------------------------------------
// Módulo 3
// --------------------------------------------------------------------
$routes->group('modulo3', [], function ($routes) {

    $routes->get('/',         'Modulos::dashboard');
    $routes->get('dashboard', 'Modulos::dashboard');
    $routes->get('ordenes',   'Modulos::ordenes');

    $routes->get ('wip',                    'Wip::index');
    $routes->get ('wip/json',               'Wip::json');
    $routes->get ('wip/debug',              'Wip::debug');
    $routes->post('wip/actualizar/(:num)',  'Wip::actualizar/$1');

    $routes->get('incidencias',                 'Incidencias::index');
    $routes->post('incidencias/crear',          'Incidencias::store');
    $routes->get('incidencias/eliminar/(:num)', 'Incidencias::delete/$1');

    $routes->get('reportes',        'Modulos::reportes');
    $routes->get('notificaciones',  'Modulos::notificaciones');
    $routes->get('inspeccion',                  'Inspeccion::index');
    $routes->get('inspeccion/evaluar/(:num)',   'Inspeccion::evaluar/$1');
    $routes->post('inspeccion/evaluar/(:num)',  'Inspeccion::guardarEvaluacion/$1');
    $routes->get('mrp',             'Modulos::mrp');

    // Alias → Calidad/Desperdicios
    $routes->get('desperdicios', function () {
        return redirect()->to(site_url('calidad/desperdicios'));
    });

    $routes->get('mantenimiento_inventario', 'Maquinaria::index');
    $routes->get('mantenimiento_preventivo', 'Modulos::mantenimientoPreventivo');

    $routes->get('mantenimiento_correctivo', function () {
        return redirect()->to(site_url('modulo3/mantenimiento/correctivo'));
    });

    $routes->get ('maquinaria',               'Maquinaria::index');
    $routes->post('maquinaria/guardar',       'Maquinaria::guardar');
    $routes->get ('maquinaria/editar/(:num)', 'Maquinaria::editar/$1');

    $routes->group('mantenimiento', function($r){
        $r->get ('correctivo',       'MantenimientoCorrectivo::index');
        $r->get ('correctivo/diag',  'MantenimientoCorrectivo::diag');
        $r->get ('correctivo/probe', 'MantenimientoCorrectivo::probe');
        $r->post('correctivo/crear', 'MantenimientoCorrectivo::crear');
    });

    // Logística
    $routes->get('logistica_preparacion', 'Modulos::logisticaPreparacion');
    $routes->get('logistica_gestion',     'Modulos::logisticaGestion');
    $routes->get('logistica_documentos',  'Modulos::logisticaDocumentos');

    // Órdenes de clientes (enlace del menú)
    $routes->get('ordenesclientes',       'Modulos::m1_ordenesclientes');
});

$routes->group('modulo3', ['namespace'=>'App\Controllers'], static function($routes){
    $routes->group('mantenimiento', static function($r){
        $r->get ('correctivo',         'MantenimientoCorrectivo::index');
        $r->get ('correctivo/diag',    'MantenimientoCorrectivo::diag');
        $r->get ('correctivo/probe',   'MantenimientoCorrectivo::probe');
        $r->post('correctivo/crear',   'MantenimientoCorrectivo::crear');
        $r->post('correctivo/actualizar/(:num)', 'MantenimientoCorrectivo::actualizar/$1');
    });
});

// --------------------------------------------------------------------
// Calidad (Desperdicios & Reprocesos)
// --------------------------------------------------------------------
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

// --------------------------------------------------------------------
// Módulo 11 · Usuarios
// --------------------------------------------------------------------
$routes->group('modulo11', [], function ($routes) {
    $routes->get('usuarios',                 'Modulos::m11_usuarios');
    $routes->get('usuarios/agregar',         'Modulos::m11_agregar_usuario');
    $routes->post('usuarios/agregar',        'Modulos::m11_agregar_usuario');
    $routes->get('usuarios/editar/(:num)',   'Modulos::m11_editar_usuario/$1');
    $routes->post('usuarios/editar/(:num)',  'Modulos::m11_editar_usuario/$1');
});

// --------------------------------------------------------------------
// Utils DB
// --------------------------------------------------------------------
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
