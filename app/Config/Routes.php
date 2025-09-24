<?php

use Config\Services;

$routes = Services::routes();

// Rutas del sistema
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Modulo3');
$routes->setDefaultMethod('dashboard');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false); // declaramos todas las rutas explícitas
$routes->get('/login', 'Auth::login');
$routes->get('/register', 'Auth::register');
$routes->get('/perfilempleado', 'PerfilEmpleado::index');
$routes->get('pedidos', 'Pedidos::index');
$routes->get('agregar_pedido', 'agregar_pedido::index');
$routes->get('editarpedido/(:num)', 'Pedidos::editar/$1');
$routes->post('actualizar_pedido/(:num)', 'Pedidos::actualizar/$1');
$routes->get('/detalle_pedido/(:num)', 'Pedidos::detalles/$1');


// Raíz del sitio -> Dashboard del módulo 3
$routes->get('/', 'Modulo3::dashboard');

// Grupo del módulo 3
$routes->group('modulo3', function ($routes) {
    $routes->get('/',            'Modulo3::dashboard');
    $routes->get('dashboard',    'Modulo3::dashboard');
    $routes->get('ordenes',      'Modulo3::ordenes');
    $routes->get('wip',          'Modulo3::wip');
    $routes->get('incidencias',  'Modulo3::incidencias');
    $routes->get('reportes',     'Modulo3::reportes');
    $routes->get('notificaciones','Modulo3::notificaciones');
});
