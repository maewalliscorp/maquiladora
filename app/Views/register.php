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
                            <label for="maquiladora" class="form-label">Maquiladora</label>
                            <select class="form-select" id="maquiladora" name="maquiladoraIdFK">
                                <option value="">Cargando maquiladoras...</option>
                            </select>
                            <small class="text-muted">Si no selecciona una, se asignará automáticamente</small>
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
            cargarMaquiladoras();
            configurarValidacion();
        });

        function cargarMaquiladoras() {
            const select = document.getElementById('maquiladora');
            const spinner = document.getElementById('loadingSpinner');
            
            spinner.classList.remove('d-none');
            
            fetch('<?= base_url('api/maquiladoras') ?>')
                .then(response => response.json())
                .then(data => {
                    select.innerHTML = '<option value="">Seleccionar maquiladora...</option>';
                    data.forEach(maq => {
                        const option = new Option(maq.nombre, maq.id);
                        select.add(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar maquiladoras:', error);
                    select.innerHTML = '<option value="">Error al cargar las maquiladoras</option>';
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
        }

        function configurarValidacion() {
            const form = document.getElementById('registerForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordError = document.getElementById('passwordError');
            
            function validarPassword() {
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

            confirmPassword.addEventListener('input', validarPassword);
            
            form.addEventListener('submit', function(e) {
                if (!validarPassword()) {
                    e.preventDefault();
                }
            });
        }
    </script>
</body>
</html>
        }
        .form-control {
            margin-bottom: 1rem;
        }
        .btn-custom {
            background-color: #007bff;
            border: none;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<!-- Header arriba -->
<div class="header">
    <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="60">
    <h4 class="m-0">Sistema de Maquiladora</h4>
</div>

<!-- Sección del formulario -->
<div class="container-fluid form-section">
    <div class="form-card">
        <h2 class="mb-4 text-center">Regístrate</h2>
        <form action="<?= base_url('auth/register') ?>" method="post">
            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-md-6">
                    <label class="form-label">Número de Empleado *</label>
                    <input type="text" name="noEmpleado" class="form-control" required>

                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required>

                    <label class="form-label">Apellido *</label>
                    <input type="text" name="apellido" class="form-control" required>

                    <label class="form-label">Correo Electrónico *</label>
                    <input type="email" name="email" class="form-control" required>

                    <label class="form-label">Teléfono *</label>
                    <input type="tel" name="telefono" class="form-control" required>
                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                    <label class="form-label">Puesto *</label>
                    <select name="puesto" class="form-control" required>
                        <option value="">Seleccionar puesto...</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Operador">Operador</option>
                        <option value="Diseñador">Diseñador</option>
                        <option value="Jefe de Producción">Jefe de Producción</option>
                        <option value="Coordinador">Coordinador</option>
                        <option value="Analista">Analista</option>
                    </select>

                    <label class="form-label">Domicilio *</label>
                    <textarea name="domicilio" class="form-control" rows="3" required></textarea>

                    <label class="form-label">Usuario *</label>
                    <input type="text" name="usuario" class="form-control" required>

                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" class="form-control" required>

                    <label class="form-label">Confirmar Contraseña *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required>
                        <label class="form-check-label" for="terminos">
                            Acepto los términos y condiciones de uso
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-custom text-white px-5">Registrarse</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Validación de contraseñas
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            this.setCustomValidity('');
        }
    });

    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return false;
        }
    });
</script>
</body>
</html>
