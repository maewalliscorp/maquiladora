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
        $usuario  = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        $usuarioModel = new UsuarioModel();
        $user = $usuarioModel->authenticate($usuario, $password);

        if ($user) {
            // Crear sesión mínima
            session()->set([
                'user_id'   => $user['id'],
                'user_name' => $user['usuario'],
                'logged_in' => true
            ]);

            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login')->with('error', 'Credenciales inválidas.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
