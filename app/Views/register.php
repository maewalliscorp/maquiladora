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
                    
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
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

            // Load maquiladoras from API
            async function loadMaquiladoras() {
                console.log('Iniciando carga de maquiladoras...');
                
                try {
                    // Mostrar spinner y limpiar errores
                    maquiladoraSpinner.style.display = 'flex';
                    maquiladoraError.classList.add('d-none');
                    
                    // Hacer la petición a la API
                    console.log('Realizando petición a:', '<?= base_url('api/maquiladoras') ?>');
                    const response = await fetch('<?= base_url('api/maquiladoras') ?>');
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`Error HTTP ${response.status}: ${errorText}`);
                    }
                    
                    const result = await response.json();
                    console.log('Respuesta de la API:', result);
                    
                    if (result.status === 'success' && Array.isArray(result.data)) {
                        // Limpiar opciones existentes
                        maquiladoraSelect.innerHTML = '<option value="">Seleccione una maquiladora</option>';
                        
                        // Agregar cada maquiladora al select
                        result.data.forEach(maq => {
                            const option = new Option(maq.nombre, maq.idmaquiladora);
                            maquiladoraSelect.add(option);
                        });
                        
                        if (result.data.length === 0) {
                            throw new Error('No hay maquiladoras disponibles');
                        }
                        
                        console.log(`Se cargaron ${result.data.length} maquiladoras`);
                    } else {
                        throw new Error(result.message || 'Formato de respuesta inválido');
                    }
                } catch (error) {
                    console.error('Error al cargar maquiladoras:', error);
                    maquiladoraError.textContent = 'Error al cargar las maquiladoras: ' + error.message;
                    maquiladoraError.classList.remove('d-none');
                    maquiladoraSelect.innerHTML = '<option value="">Error al cargar las maquiladoras</option>';
                } finally {
                    // Ocultar spinner
                    maquiladoraSpinner.style.display = 'none';
                    console.log('Finalizada carga de maquiladoras');
                }
            }

            // Event listeners
            confirmPassword.addEventListener('input', validatePassword);
            
            form.addEventListener('submit', function(e) {
                if (!validatePassword()) {
                    e.preventDefault();
                }
                
                // Ensure a maquiladora is selected
                if (!maquiladoraSelect.value) {
                    e.preventDefault();
                    maquiladoraSelect.classList.add('is-invalid');
                } else {
                    maquiladoraSelect.classList.remove('is-invalid');
                }
                
                form.classList.add('was-validated');
            });
            
            // Add validation for maquiladora select
            maquiladoraSelect.addEventListener('change', function() {
                if (this.value) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
</body>
</html>