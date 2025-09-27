<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Maquiladora</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #63677c;
            color: white;
        }
        .header {
            background-color: #4b4f63;
            padding: 1rem;
            display: flex;
            align-items: center;
        }
        .header img {
            margin-right: 1rem;
        }
        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 900px;
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
