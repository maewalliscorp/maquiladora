<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MaquiladoraModel;
use CodeIgniter\Controller;

class UsuarioController extends Controller
{
    public function login()
    {
        // Forzar re-login: si ya hay sesión abierta, destruirla
        if (session()->get('logged_in')) {
            session()->destroy();
            // Regenerar ID y eliminar cookie previa para evitar sesiones pegajosas
            try { session()->regenerate(true); } catch (\Throwable $e) {}
        }

        // Evitar caché de la página de login
        $response = service('response');
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');

        return view('login');
    }

    public function authenticate()
    {
        // Recibir datos del formulario
        $correo   = $this->request->getPost('correo');
        $password = $this->request->getPost('password');

        // Cargar modelo
        $usuarioModel = new UserModel();
        $user = $usuarioModel->where('correo', $correo)->first();

        if ($user && (int)($user['active'] ?? 0) === 1 && password_verify($password, $user['password'])) {
            $roleIds = [];
            $roleNames = [];
            try {
                $db = \Config\Database::connect();
                $rows = $db->query(
                    'SELECT r.id, r.nombre FROM usuario_rol ur JOIN rol r ON r.id = ur.rolIdFK WHERE ur.usuarioIdFK = ?',
                    [$user['id']]
                )->getResultArray();
                foreach ($rows as $r) {
                    if (isset($r['id'])) { $roleIds[] = (int)$r['id']; }
                    if (isset($r['nombre'])) { $roleNames[] = (string)$r['nombre']; }
                }
            } catch (\Throwable $e) { /* sin roles => arrays vacíos */ }
            session()->set([
                'user_id'     => $user['id'],
                'user_name'   => $user['username'] ?? ($user['nombre'] ?? $user['correo']),
                'logged_in'   => true,
                'role_ids'    => $roleIds,
                'role_names'  => $roleNames,
                'primary_role'=> isset($roleNames[0]) ? (string)$roleNames[0] : null,
            ]);

            return redirect()->to('/dashboard');
        }

        // Error si las credenciales no coinciden o la cuenta está inactiva
        if ($user && (int)($user['active'] ?? 0) !== 1) {
            return redirect()
                ->to('/login')
                ->withInput()
                ->with('login_attempted', true)
                ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }
        return redirect()
            ->to('/login')
            ->withInput()
            ->with('login_attempted', true)
            ->with('error', 'Correo o contraseña incorrectos.');
    }

    public function logout()
    {
        // Destruir la sesión
        session()->destroy();
        try { session()->regenerate(true); } catch (\Throwable $e) {}
        
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
    public function index()
    {
        $maquiladoraModel = new MaquiladoraModel();
        $data['maquiladoras'] = $maquiladoraModel->findAll();

        return view('register', $data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => [
                'rules'  => 'required|min_length[3]|is_unique[users.username]',
                'errors' => [
                    'required'   => 'El nombre de usuario es obligatorio.',
                    'min_length' => 'El nombre de usuario debe tener al menos 3 caracteres.',
                    'is_unique'  => 'El nombre de usuario ya está registrado.'
                ],
            ],
            'correo' => [
                'rules'  => 'required|valid_email|is_unique[users.correo]',
                'errors' => [
                    'required'    => 'El correo electrónico es obligatorio.',
                    'valid_email' => 'Debes ingresar un correo electrónico válido.',
                    'is_unique'   => 'El correo electrónico ya está registrado.'
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => 'La contraseña es obligatoria.',
                    'min_length' => 'La contraseña debe tener al menos 6 caracteres.'
                ],
            ],
            'password_verify' => [
                'rules'  => 'matches[password]',
                'errors' => [
                    'matches' => 'La confirmación de contraseña no coincide.'
                ],
            ],
            'maquiladoraIdFK' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Debes seleccionar una maquiladora.'
                ],
            ],
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userModel = new UserModel();
        try {
            $userModel->save([
                'username' => $this->request->getPost('username'),
                'correo' => $this->request->getPost('correo'),
                'password' => $this->request->getPost('password'),
                'maquiladoraIdFK' => $this->request->getPost('maquiladoraIdFK'),
                'status' => 'inactivo',
                'active' => 0
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $msg = $e->getMessage();
            $errors = [];
            if (stripos($msg, 'users.username') !== false) {
                $errors['username'] = 'El nombre de usuario ya existe. Elige otro.';
            } elseif (stripos($msg, 'users.correo') !== false) {
                $errors['correo'] = 'El correo ya está registrado.';
            } else {
                $errors['general'] = 'No se pudo registrar. Intenta nuevamente.';
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        return redirect()->to('/login')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
    }
   
}
