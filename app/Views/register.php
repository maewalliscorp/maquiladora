<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Maquiladora</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color:rgb(98, 132, 183);">
    <div class="d-flex min-vh-100">
        <!-- Panel izquierdo -->
        <div class="w-50 bg-primary d-none d-lg-flex flex-column justify-content-center align-items-center text-center text-light">
            <h1 class="fw-bold">Sistema de Maquiladora</h1>
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="350" class="my-3">
            <h2 class="text-secondary-emphasis fw-bold">Maewallis Corp</h2>
        </div>

        <!-- Panel derecho -->
        <div class="w-100 w-lg-50 d-flex justify-content-center align-items-center">
            <div class="card" style="width: 100%; max-width: 400px; background-color:rgb(255, 251, 251); border: 1px solidrgb(124, 144, 195);">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Crear cuenta</h3>
                    
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            $errors = session()->getFlashdata('errors');
                            if (is_array($errors)) {
                                echo '<ul>';
                                foreach ($errors as $error) {
                                    echo '<li>'.$error.'</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo $errors;
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('register') ?>" method="post" id="registerForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div id="loadingSpinner" class="input-group-text d-none">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                            </div>
                            <div id="passwordError" class="form-text text-danger d-none">Las contraseñas no coinciden</div>
                        </div>

                        <div class="mb-3">
                            <label for="maquiladora" class="form-label">Maquiladora *</label>
                            <div class="input-group">
                                <select class="form-select" id="maquiladora" name="maquiladoraIdFK" required>
                                    <option value="">Cargando maquiladoras...</option>
                                </select>
                                <span class="input-group-text p-0" id="maquiladoraSpinner">
                                    <div class="spinner-border spinner-border-sm m-1" role="status" style="width: 1rem; height: 1rem;">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </span>
                            </div>
                            <div id="maquiladoraError" class="form-text text-danger d-none">Error al cargar las maquiladoras. Por favor, recargue la página.</div>
                            <small class="text-muted">Seleccione la maquiladora a la que pertenece</small>
                        </div>

                        <input type="hidden" name="active" value="0">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                            <a href="<?= base_url('login') ?>" class="btn btn-link text-center">¿Ya tienes cuenta? Inicia sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y validación -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form validation
            const form = document.getElementById('registerForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordError = document.getElementById('passwordError');
            const maquiladoraSelect = document.getElementById('maquiladora');
            const maquiladoraSpinner = document.getElementById('maquiladoraSpinner');
            const maquiladoraError = document.getElementById('maquiladoraError');
            
            // Load maquiladoras on page load
            loadMaquiladoras();
            
            // Password validation
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                    passwordError.classList.remove('d-none');
                    return false;
                } else {
                    confirmPassword.classList.remove('is-invalid');
                    passwordError.classList.add('d-none');
                    return true;
                }
            }

            // Cargar maquiladoras desde la API con manejo mejorado de errores
            async function loadMaquiladoras() {
                console.log('Iniciando carga de maquiladoras...');
                
                // Obtener referencias a los elementos del DOM
                const maquiladoraSelect = document.getElementById('maquiladoraIdFK');
                const maquiladoraSpinner = document.getElementById('maquiladoraSpinner');
                const maquiladoraError = document.getElementById('maquiladoraError');
                
                if (!maquiladoraSelect || !maquiladoraSpinner || !maquiladoraError) {
                    console.error('Elementos del DOM no encontrados');
                    return;
                }
                
                try {
                    // Mostrar spinner y limpiar errores
                    maquiladoraSpinner.style.display = 'flex';
                    maquiladoraError.textContent = '';
                    maquiladoraError.classList.add('d-none');
                    maquiladoraSelect.disabled = true;
                    
                    // Hacer la petición a la API con cabeceras para evitar caché
                    const apiUrl = '<?= base_url('usuario/maquiladoras') ?>';
                    console.log('Solicitando maquiladoras a:', apiUrl);
                    
                    const response = await fetch(apiUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Cache-Control': 'no-cache, no-store, must-revalidate',
                            'Pragma': 'no-cache',
                            'Expires': '0'
                        }
                    });
                    
                    console.log('Respuesta recibida:', response.status, response.statusText);
                    
                    if (!response.ok) {
                        let errorMessage = `Error HTTP ${response.status}`;
                        try {
                            const errorData = await response.json();
                            errorMessage += `: ${errorData.message || response.statusText}`;
                        } catch (e) {
                            const errorText = await response.text();
                            errorMessage += `: ${errorText || response.statusText}`;
                        }
                        throw new Error(errorMessage);
                    }
                    
                    const result = await response.json();
                    console.log('Datos recibidos:', result);
                    
                    // Limpiar opciones existentes
                    maquiladoraSelect.innerHTML = '';
                    
                    // Agregar opción por defecto
                    const defaultOption = new Option('Seleccione una maquiladora', '');
                    defaultOption.selected = true;
                    defaultOption.disabled = true;
                    maquiladoraSelect.add(defaultOption);
                    
                    if (result.status === 'success' && Array.isArray(result.data)) {
                        if (result.data.length === 0) {
                            // Si no hay maquiladoras, mostrar mensaje
                            const noDataOption = new Option('No hay maquiladoras disponibles', '');
                            noDataOption.disabled = true;
                            maquiladoraSelect.add(noDataOption);
                            
                            maquiladoraError.textContent = 'No hay maquiladoras disponibles para registrar.';
                            maquiladoraError.classList.remove('d-none');
                        } else {
                            // Agregar cada maquiladora al select
                            result.data.forEach(maq => {
                                const option = new Option(maq.nombre, maq.id);
                                maquiladoraSelect.add(option);
                            });
                            console.log(`Se cargaron ${result.data.length} maquiladoras`);
                        }
                    } else {
                        throw new Error(result.message || 'Formato de respuesta inesperado');
                    }
                    
                } catch (error) {
                    console.error('Error al cargar maquiladoras:', error);
                    
                    // Mostrar mensaje de error
                    maquiladoraError.textContent = `Error al cargar las maquiladoras. ${error.message || 'Por favor, intente recargar la página.'}`;
                    maquiladoraError.classList.remove('d-none');
                    
                    // Restablecer el select con un mensaje de error
                    maquiladoraSelect.innerHTML = '';
                    const errorOption = new Option('Error al cargar las maquiladoras', '');
                    errorOption.disabled = true;
                    maquiladoraSelect.add(errorOption);
                    
                } finally {
                    // Restaurar estado
                    maquiladoraSpinner.style.display = 'none';
                    maquiladoraSelect.disabled = false;
                    
                    // Forzar validación del formulario
                    const event = new Event('change');
                    maquiladoraSelect.dispatchEvent(event);
                    
                    console.log('Finalizada carga de maquiladoras');
                }
            }

            // Event listeners
            confirmPassword.addEventListener('input', validatePassword);
            
            form.addEventListener('submit', async function(e) {
                // Validar contraseñas
                if (!validatePassword()) {
                    e.preventDefault();
                    return false;
                }
                
                try {
                    // Mostrar spinner de carga
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrando...';
                    
                    // Validar que se haya seleccionado una maquiladora
                    if (!maquiladoraSelect.value) {
                        e.preventDefault();
                        alert('Por favor seleccione una maquiladora');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                        return false;
                    }
                    
                    // Si todo está bien, el formulario se enviará normalmente
                    return true;
                    
                } catch (error) {
                    console.error('Error al enviar el formulario:', error);
                    e.preventDefault();
                    alert('Ocurrió un error al procesar el formulario');
                    return false;
                } finally {
                    // Restaurar el botón después de 3 segundos si aún no se ha redirigido
                    setTimeout(() => {
                        const submitButton = form.querySelector('button[type="submit"]');
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = 'Registrarse';
                        }
                    }, 3000);
                }
            });
            
            // Cargar maquiladoras al iniciar
            loadMaquiladoras();
            
            // Add validation for maquiladora select
            maquiladoraSelect.addEventListener('change', function() {
                if (this.value) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
    
    <!-- Código de depuración temporal -->
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            console.log('=== INICIO DE DEPURACIÓN ===');
            
            // 1. Verificar si el formulario existe
            const form = document.getElementById('registerForm');
            if (!form) {
                console.error('ERROR: No se encontró el formulario con ID "registerForm"');
                return;
            }
            
            // 2. Probar la conexión con la API
            console.log('Probando conexión con la API...');
            try {
                const response = await fetch('<?= base_url('api/maquiladoras') ?>');
                console.log('Respuesta de la API:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    url: response.url
                });
                
                const data = await response.json().catch(e => {
                    console.error('Error al parsear la respuesta JSON:', e);
                    return { error: 'No se pudo parsear la respuesta como JSON' };
                });
                
                console.log('Datos de la API:', data);
                
                if (data.status === 'success' && Array.isArray(data.data)) {
                    console.log(`Se encontraron ${data.data.length} maquiladoras`);
                    // Actualizar el select manualmente para pruebas
                    const select = document.getElementById('maquiladora');
                    if (select) {
                        select.innerHTML = '';
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Seleccione una maquiladora';
                        select.appendChild(defaultOption);
                        
                        data.data.forEach(maq => {
                            const option = document.createElement('option');
                            option.value = maq.id;
                            option.textContent = maq.nombre;
                            select.appendChild(option);
                        });
                        console.log('Select actualizado con maquiladoras');
                    }
                }
            } catch (error) {
                console.error('Error al conectar con la API:', error);
                console.error('Detalles del error:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
            }
            
            // 3. Configurar el evento de envío del formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                console.log('=== DATOS DEL FORMULARIO ===');
                const formData = new FormData(form);
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
                
                // Continuar con el envío normal
                console.log('Enviando formulario...');
                this.submit();
            });
        });
    </script>
</body>
</html>