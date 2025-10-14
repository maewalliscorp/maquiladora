<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Controller;

class UsuarioController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function authenticate()
    {
        // Recibir datos del formulario
        $correo   = $this->request->getPost('correo');
        $password = $this->request->getPost('password');

        // Cargar modelo
        $usuarioModel = new UsuarioModel();
        $user = $usuarioModel->where('correo', $correo)->first();

        // Verificar usuario y contraseña
        if ($user && password_verify($password, $user['password'])) {
            // Crear sesión
            session()->set([
                'user_id'   => $user['id'],
                'user_name' => $user['nombre'] ?? $user['correo'],
                'logged_in' => true
            ]);

            return redirect()->to('/dashboard');
        }

        // Error si las credenciales no coinciden
        return redirect()
            ->to('/login')
            ->with('error', 'Correo o contraseña incorrectos.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
