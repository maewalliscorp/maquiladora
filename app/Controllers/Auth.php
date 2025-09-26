<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function authenticate()
    {
        // Autenticación con roles específicos
        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');
        
        // Validación de credenciales específicas
        if ($usuario === 'Admin' && $password === 'ADMIN') {
            // Usuario Admin - Redirigir al dashboard
            session()->set([
                'user_id' => 1,
                'user_email' => 'admin@fabrica.com',
                'user_name' => 'Admin',
                'user_role' => 'admin',
                'logged_in' => true
            ]);
            
            return redirect()->to('/dashboard');
            
        } elseif ($usuario === 'almacenista' && $password === 'alma') {
            // Usuario Almacenista - Redirigir al módulo 3 (dashboard)
            session()->set([
                'user_id' => 2,
                'user_email' => 'almacenista@fabrica.com',
                'user_name' => 'Almacenista',
                'user_role' => 'almacenista',
                'logged_in' => true
            ]);
            
            return redirect()->to('/dashboard');
            
        }
        elseif ($usuario === 'diseñador' && $password === 'dise') {
            // Usuario Diseñador - Redirigir al dashboard
            session()->set([
                'user_id' => 3,
                'user_email' => 'diseñador@fabrica.com',
                'user_name' => 'diseñador',
                'user_role' => 'diseñador',
                'logged_in' => true
            ]);

            return redirect()->to('/dashboard');

        } else {
            // Credenciales inválidas
            return redirect()->to('/login')->with('error', 'Credenciales inválidas.');
        }
    }

    public function logout()
    {
        // Destruir la sesión
        session()->destroy();
        return redirect()->to('/login');
    }

    public function register()
    {
        return view('register');
    }

}
