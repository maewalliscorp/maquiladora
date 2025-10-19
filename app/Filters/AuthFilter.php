<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Rutas que no requieren autenticación
        $uri = service('uri');
        $currentPath = ltrim($uri->getPath(), '/');
        // Normaliza prefijo index.php/ si existe
        if (strpos($currentPath, 'index.php/') === 0) {
            $currentPath = substr($currentPath, strlen('index.php/'));
        }
        $publicRoutes = ['login', 'register', 'auth', 'api/maquiladoras'];
        
        foreach ($publicRoutes as $route) {
            if (strpos($currentPath, $route) === 0) {
                // Si es la página de login y existe sesión, destruirla para forzar re-autenticación
                if ($route === 'login' && session()->get('logged_in')) {
                    session()->destroy();
                }
                return;
            }
        }

        // Verificar si el usuario está autenticado
        if (!session()->get('logged_in')) {
            // Guardar la URL actual para redirigir después del login
            session()->set('redirect_url', current_url());
            
            // Redirigir al login con mensaje
            return redirect()->to('/login')
                ->with('error', 'Por favor inicia sesión para continuar.');
        }

        // Prevenir almacenamiento en caché
        $response = service('response');
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Refuerza no-caché en todas las respuestas protegidas
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
    }
}
