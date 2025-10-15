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
                'username' => 'required|min_length[3]|max_length[50]|is_unique[usuarios.username]',
                'email' => 'required|valid_email|is_unique[usuarios.correo]',
                'password' => 'required|min_length[6]',
                'confirm_password' => 'matches[password]',
                'maquiladoraIdFK' => 'permit_empty|integer'
            ];

            if (!$this->validate($rules)) {
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
                return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el usuario. Por favor, inténtalo de nuevo.');
            }
        }
        
        // Mostrar el formulario de registro
        return view('register');
    }

    public function getMaquiladoras()
    {
        $db = \Config\Database::connect();
        $query = $db->table('maquiladora')
                    ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->where('activo', 1)
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->get();
        
        return $this->response->setJSON($query->getResultArray());
    }
}
