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
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario de registro
            $data = [
                'noEmpleado' => $this->request->getPost('noEmpleado'),
                'nombre' => $this->request->getPost('nombre'),
                'apellido' => $this->request->getPost('apellido'),
                'email' => $this->request->getPost('email'),
                'telefono' => $this->request->getPost('telefono'),
                'puesto' => $this->request->getPost('puesto'),
                'domicilio' => $this->request->getPost('domicilio'),
                'usuario' => $this->request->getPost('usuario'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'activo' => 3, // Estado "En espera" para nuevos registros
                'fechaAlta' => date('Y-m-d H:i:s'),
                'ultimoAcceso' => date('Y-m-d H:i:s')
            ];
            
            // Validar que las contraseñas coincidan
            if ($this->request->getPost('password') !== $this->request->getPost('confirm_password')) {
                return redirect()->to('/register')->with('error', 'Las contraseñas no coinciden.');
            }
            
            // Aquí iría la lógica para guardar en la base de datos
            // Por ahora, solo simulamos el registro exitoso
            
            return redirect()->to('/login')->with('success', 'Registro exitoso. Tu cuenta está en espera de aprobación.');
        }

        return view('register');
    }

}
