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
        // Si es una petición POST, procesar el formulario
        if ($this->request->getMethod() === 'post') {
            log_message('debug', 'Inicio del proceso de registro');
            
            // Validación de datos
            $rules = [
                'username' => 'required|min_length[3]|is_unique[users.username]',
                'email' => 'required|valid_email|is_unique[users.correo]',
                'password' => 'required|min_length[6]',
                'confirm_password' => 'matches[password]',
                'maquiladoraIdFK' => 'required|integer'
            ];
            
            $messages = [
                'username' => [
                    'required' => 'El nombre de usuario es obligatorio',
                    'min_length' => 'El nombre de usuario debe tener al menos 3 caracteres',
                    'is_unique' => 'Este nombre de usuario ya está en uso'
                ],
                'email' => [
                    'required' => 'El correo electrónico es obligatorio',
                    'valid_email' => 'Por favor ingresa un correo electrónico válido',
                    'is_unique' => 'Este correo electrónico ya está registrado'
                ],
                'password' => [
                    'required' => 'La contraseña es obligatoria',
                    'min_length' => 'La contraseña debe tener al menos 6 caracteres'
                ],
                'confirm_password' => [
                    'matches' => 'Las contraseñas no coinciden'
                ],
                'maquiladoraIdFK' => [
                    'required' => 'Debes seleccionar una maquiladora',
                    'integer' => 'La maquiladora seleccionada no es válida'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                $errors = $this->validator->getErrors();
                log_message('debug', 'Errores de validación: ' . print_r($errors, true));
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $errors);
            }

            // Verificar duplicados manualmente
            $usuarioModel = new UsuarioModel();
            $existingUser = $usuarioModel->where('correo', $this->request->getPost('email'))
                                       ->orWhere('username', $this->request->getPost('username'))
                                       ->first();
            
            if ($existingUser) {
                $errorMsg = $existingUser['correo'] === $this->request->getPost('email') 
                    ? 'Este correo electrónico ya está registrado' 
                    : 'Este nombre de usuario ya está en uso';
                
                log_message('warning', 'Intento de registro con datos duplicados: ' . $errorMsg);
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMsg);
            }

            // Obtener los datos del formulario
            $userData = [
                'username' => trim($this->request->getPost('username')),
                'correo' => strtolower(trim($this->request->getPost('email'))),
                'password' => $this->request->getPost('password'), // Será hasheado por el modelo
                'maquiladoraIdFK' => (int)$this->request->getPost('maquiladoraIdFK'),
                'active' => 1,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('debug', 'Datos del usuario a registrar: ' . print_r($userData, true));
            
            try {
                // Insertar el usuario (el modelo se encarga del hash de la contraseña)
                $usuarioModel->protect(false);
                $saved = $usuarioModel->save($userData);
                $userId = $usuarioModel->getInsertID();
                $usuarioModel->protect(true);
                
                if (!$saved) {
                    throw new \Exception('No se pudo guardar el usuario en la base de datos');
                }
                
                log_message('info', 'Usuario registrado exitosamente con ID: ' . $userId);
                
                // Limpiar datos sensibles antes de redirigir
                unset($userData['password']);
                
                return redirect()->to('/login')
                    ->with('message', '¡Registro exitoso! Ahora puedes iniciar sesión.');
                
            } catch (\Exception $e) {
                $errorMsg = 'Error al registrar usuario: ' . $e->getMessage();
                log_message('error', $errorMsg);
                log_message('error', 'Trace: ' . $e->getTraceAsString());
                
                // Verificar si es un error de duplicado
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errorMsg = 'El correo electrónico o nombre de usuario ya está registrado';
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ocurrió un error al registrar el usuario. ' . $errorMsg);
            }
        }
        
        // Mostrar el formulario de registro
        return view('register');
    }

    public function getMaquiladoras()
{
    try {
        log_message('debug', 'Iniciando getMaquiladoras');
        
        $db = \Config\Database::connect();
        log_message('debug', 'Conexión a la base de datos establecida');
        
        $query = $db->table('maquiladora')
                   ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                   ->orderBy('Nombre_Maquila', 'ASC');
        
        log_message('debug', 'Consulta SQL: ' . $db->getLastQuery());
        
        $maquiladoras = $query->get()->getResultArray();
        
        log_message('debug', 'Maquiladoras encontradas: ' . count($maquiladoras));
        
        if (empty($maquiladoras)) {
            log_message('warning', 'No se encontraron maquiladoras en la base de datos');
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $maquiladoras
        ]);
        
    } catch (\Exception $e) {
        $errorMessage = 'Error al obtener maquiladoras: ' . $e->getMessage();
        log_message('error', $errorMessage);
        log_message('error', $e->getTraceAsString());
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al cargar las maquiladoras',
            'debug' => (ENVIRONMENT === 'development') ? $e->getMessage() : null
        ])->setStatusCode(500);
    }
}
}
