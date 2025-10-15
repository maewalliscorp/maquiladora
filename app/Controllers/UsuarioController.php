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
                'user_name' => $user['username'] ?? ($user['nombre'] ?? $user['correo']),
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
        // Destruir la sesión
        session()->destroy();
        
        // Limpiar la caché para prevenir el retroceso después del cierre de sesión
        $response = service('response');
        $response->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
        
        // Redirigir al login
        return redirect()->to('/login')
            ->with('message', 'Has cerrado sesión correctamente.')
            ->withCookies();
    }

    public function register()
{
    // Si es una solicitud POST, procesar el registro
    if ($this->request->getMethod() === 'post') {
        // Validar los datos del formulario
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.correo]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'matches[password]',
            'maquiladoraIdFK' => 'permit_empty|integer'
        ];

        // Mensajes de validación personalizados
        $messages = [
            'username' => [
                'is_unique' => 'Este nombre de usuario ya está registrado.'
            ],
            'email' => [
                'is_unique' => 'Este correo electrónico ya está registrado.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        // Obtener los datos del formulario
        $userData = [
            'username' => $this->request->getPost('username'),
            'correo' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'maquiladoraIdFK' => $this->request->getPost('maquiladoraIdFK') ?: null,
            'active' => 0, // Usuario inactivo hasta que sea aprobado
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Guardar el usuario en la base de datos
        $usuarioModel = new UsuarioModel();
        try {
            $usuarioModel->insert($userData);
            return redirect()->to('/login')->with('message', 'Registro exitoso. Tu cuenta está pendiente de aprobación.');
        } catch (\Exception $e) {
            log_message('error', 'Error al registrar usuario: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el usuario. Por favor, inténtalo de nuevo. Error: ' . $e->getMessage());
        }
    }
    
    // Mostrar el formulario de registro
    return view('register');
}

    public function getMaquiladoras()
{
    try {
        $db = \Config\Database::connect();
        $query = $db->table('maquiladora')
                    ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->get();
        
        $maquiladoras = $query->getResultArray();
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $maquiladoras
        ]);
    } catch (\Exception $e) {
        log_message('error', 'Error al obtener maquiladoras: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al cargar las maquiladoras'
        ])->setStatusCode(500);
    }
}
}
