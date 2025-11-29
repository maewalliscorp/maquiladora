<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Control de caché -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Login Maquiladoras - Sistema de Maquiladora</title>

    <!-- Bootstrap 5 con integridad y CORS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #2c5364 0%, #203a43 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2c5364 0%, #0f2027 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #2c5364;
            box-shadow: 0 0 0 0.2rem rgba(44, 83, 100, 0.15);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #2c5364 0%, #0f2027 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(44, 83, 100, 0.3);
            color: white;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            max-width: 120px;
            filter: brightness(0) invert(1);
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #666;
            font-size: 14px;
        }
        
        .link-standard {
            color: #2c5364;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .link-standard:hover {
            color: #0f2027;
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>

<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-container">
                <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo">
            </div>
            <h1>Portal Maquiladoras</h1>
            <p>Acceso exclusivo para maquiladoras asociadas</p>
        </div>

        <div class="login-body">
            <!-- Mensaje de error -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Modal de éxito -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login_maquiladoras') ?>" method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="correo" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Correo electrónico
                    </label>
                    <input type="email" id="correo" name="correo" class="form-control"
                           placeholder="ejemplo@maquiladora.com" required>
                    <div class="invalid-feedback">
                        Por favor ingresa un correo válido.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Contraseña
                    </label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="••••••••" required>
                    <div class="invalid-feedback">
                        La contraseña es obligatoria.
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al Portal
                </button>

                <div class="divider">
                    <span>O</span>
                </div>

                <div class="text-center">
                    <a href="<?= base_url('login') ?>" class="link-standard">
                        <i class="bi bi-arrow-left me-2"></i>Volver al login estándar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <p class="text-white" style="font-size: 14px;">
            <i class="bi bi-shield-check me-2"></i>
            Conexión segura y encriptada
        </p>
    </div>
</div>

<!-- Validación Bootstrap -->
<script>
    (() => {
        'use strict';
        const form = document.querySelector('.needs-validation');
        if (form) {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        }
    })();
</script>

<!-- Control de caché al volver atrás -->
<script>
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            location.reload();
        }
    });
    window.addEventListener('unload', function () {
    });
</script>

</body>
</html>
