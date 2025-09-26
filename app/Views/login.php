<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Maquiladora</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .left-panel {
            background: linear-gradient(135deg, #5ca0d3, #6baed6);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .right-panel {
            background-color: #3f4257;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
        }
        .login-card {
            background: #ffffff10;
            backdrop-filter: blur(6px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .login-card h3 {
            font-weight: bold;
            color: #f1f1f1;
            text-align: center;
        }
        .form-control {
            margin-bottom: 1rem;
            border-radius: 8px;
            border: none;
            padding: 0.75rem;
        }
        .btn-custom {
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .register-link a {
            color: #9ec9ff;
            text-decoration: none;
            transition: color 0.2s;
        }
        .register-link a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
<div class="container-fluid h-100">
    <div class="row h-100">
        <div class="col-md-6 left-panel">
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="150">
            <h2 class="mt-3 fw-bold">Sistema de Maquiladora</h2>
            <h4>Maewallis Corp</h4>
        </div>
        <div class="col-md-6 right-panel d-flex align-items-center">
            <div class="login-card mx-auto" style="max-width: 380px; width: 100%;">
                <h3 class="mb-4">Inicio de Sesión</h3>
                
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?= base_url('login') ?>" method="post">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Usuario" required>

                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>

                    <button type="submit" class="btn btn-custom w-100 mt-2">Ingresar</button>
                </form>
                <div class="mt-3 text-center register-link">
                    <a href="<?= base_url('register') ?>">¿No tienes cuenta? Regístrate</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
