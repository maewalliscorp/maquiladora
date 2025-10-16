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

            // Verificar que se haya seleccionado una maquiladora
            $maquiladoraId = (int)$this->request->getPost('maquiladoraIdFK');
            if ($maquiladoraId <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debes seleccionar una maquiladora válida');
            }
            
            // Verificar que la maquiladora exista
            $db = db_connect();
            $maqExists = $db->table('maquiladora')
                          ->where('idmaquiladora', $maquiladoraId)
                          ->countAllResults() > 0;
                          
            if (!$maqExists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'La maquiladora seleccionada no existe');
            }

            // Obtener los datos del formulario
            $userData = [
                'username' => trim($this->request->getPost('username')),
                'correo' => strtolower(trim($this->request->getPost('email'))),
                'password' => $this->request->getPost('password'),
                'maquiladoraIdFK' => $maquiladoraId,
                'active' => 1,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('debug', '=== DATOS DEL USUARIO A REGISTRAR ===');
            log_message('debug', print_r($userData, true));
            
            try {
                log_message('debug', '=== INICIO DEL PROCESO DE REGISTRO ===');
                
                // Verificar la conexión a la base de datos
                $db = \Config\Database::connect();
                if (!$db) {
                    throw new \Exception('No se pudo conectar a la base de datos');
                }
                
                // Verificar si la tabla users existe
                $tables = $db->listTables();
                log_message('debug', 'Tablas en la base de datos: ' . print_r($tables, true));
                
                if (!in_array('users', $tables)) {
                    throw new \Exception('La tabla "users" no existe en la base de datos');
                }
                
                // Verificar las columnas de la tabla users
                $fields = $db->getFieldData('users');
                log_message('debug', 'Campos de la tabla users: ' . print_r($fields, true));
                log_message('debug', 'Datos a guardar: ' . print_r($userData, true));
                
                // Verificar conexión a la base de datos
                $db = db_connect();
                if (!$db) {
                    throw new \Exception('No se pudo conectar a la base de datos');
                }
                
                // Verificar si la tabla users existe
                $tables = $db->listTables();
                log_message('debug', 'Tablas en la base de datos: ' . print_r($tables, true));
                
                if (!in_array('users', $tables)) {
                    throw new \Exception('La tabla "users" no existe en la base de datos');
                }
                
                // Verificar columnas de la tabla users
                $fields = $db->getFieldData('users');
                log_message('debug', 'Campos de la tabla users: ' . print_r($fields, true));
                
                // Desactivar protección temporalmente
                $usuarioModel->protect(false);
                
                // Insertar el usuario
                $saved = $usuarioModel->save($userData);
                $dbError = $usuarioModel->errors();
                $lastQuery = $db->getLastQuery();
                $userId = $usuarioModel->getInsertID();
                
                // Reactivar protección
                $usuarioModel->protect(true);
                
                log_message('debug', 'Resultado del guardado: ' . ($saved ? 'ÉXITO' : 'FALLO'));
                log_message('debug', 'ID insertado: ' . $userId);
                log_message('debug', 'Última consulta: ' . $lastQuery);
                
                if (!$saved) {
                    log_message('error', 'Error al guardar usuario: ' . print_r($dbError, true));
                    
                    // Verificar errores específicos
                    $errorMsg = 'Error al guardar el usuario. ';
                    if ($dbError) {
                        $errorMsg .= implode(' ', $dbError);
                    }
                    
                    // Verificar si el error es por clave foránea
                    $maquiladoraId = (int)$this->request->getPost('maquiladoraIdFK');
                    $maqExists = $db->table('maquiladora')
                                  ->where('idmaquiladora', $maquiladoraId)
                                  ->countAllResults() > 0;
                    
                    if (!$maqExists) {
                        $errorMsg = 'La maquiladora seleccionada no existe.';
                    }
                    
                    throw new \Exception($errorMsg);
                }
                
                log_message('info', 'Usuario registrado exitosamente con ID: ' . $userId);
                
                // Limpiar datos sensibles antes de redirigir
                unset($userData['password']);
                
                // Redirigir al login con mensaje de éxito
                return redirect()->to('/login')
                    ->with('success', '¡Registro exitoso! Ahora puedes iniciar sesión.');
                    
            } catch (\Exception $e) {
                log_message('error', '=== ERROR EN REGISTRO ===');
                log_message('error', 'Mensaje: ' . $e->getMessage());
                log_message('error', 'Archivo: ' . $e->getFile() . ' en línea ' . $e->getLine());
                log_message('error', 'Datos del formulario: ' . print_r($this->request->getPost(), true));
                
                $errorMessage = $e->getMessage();
                
                // Si es un error de base de datos, mostrar un mensaje genérico en producción
                if (ENVIRONMENT === 'production' && strpos($e->getMessage(), 'SQL') !== false) {
                    $errorMessage = 'Ocurrió un error al procesar la solicitud. Por favor, inténtalo de nuevo más tarde.';
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            } finally {
                log_message('debug', '=== FIN DE REGISTRO ===');
            }
        }
        
        // Mostrar el formulario de registro
        $data = [
            'title' => 'Registro de Usuario'
        ];
        return view('register', $data);
    }

    public function getMaquiladoras()
{
    try {
        log_message('debug', 'Iniciando getMaquiladoras');
        
        $db = \Config\Database::connect();
        log_message('debug', 'Conexión a la base de datos establecida');
        
        $query = $db->table('maquiladora')
                   ->select('idmaquiladora, Nombre_Maquila')
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
