<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->helpers = array_merge($this->helpers, ['form', 'url', 'html', 'auth']);
        parent::initController($request, $response, $logger);

        // Escribir en el log
        log_message('info', 'Inicializando controlador: ' . get_class($this));

        // Prevenir almacenamiento en caché
        $this->response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', '0');

        // Verificar autenticación para todas las rutas excepto las de autenticación
        $this->checkAuth();
    }

    /**
     * Verifica si el usuario está autenticado
     */
    protected function checkAuth()
    {
        // Obtener la instancia del router
        $router = service('router');
        $currentRoute = $router->controllerName() . '/' . $router->methodName();
        
        // Rutas que no requieren autenticación
        $publicRoutes = [
            'Login::index', 
            'Login::authenticate',
            'Register::index',
            'Register::register',
            'Api::maquiladoras'
        ];
        
        // Si es una ruta pública, no verificar autenticación
        if (in_array($currentRoute, $publicRoutes)) {
            return;
        }

        // Verificar si el usuario está autenticado
        if (!session()->get('logged_in')) {
            // Guardar la URL actual para redirigir después del login
            session()->set('redirect_url', current_url());
            
            // Redirigir al login
            return redirect()->to('/login')
                ->with('error', 'Por favor inicia sesión para continuar.')
                ->send();
        }
    }
}
