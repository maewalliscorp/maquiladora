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
            <div class="card" style="width: 100%; max-width: 400px; background-color:rgb(255, 251, 251); border: 1px solid rgb(124, 144, 195);">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Crear cuenta</h3>

                    <!-- Mostrar errores -->
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Mostrar mensaje de éxito -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= esc(session('success')) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('/register/store') ?>" method="post" id="registerForm">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control" id="correo" name="correo" value="<?= old('correo') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label for="password_verify" class="form-label">Confirmar contraseña *</label>
                            <input type="password" class="form-control" id="password_verify" name="password_verify" required>
                        </div>

                        <div class="mb-3">
                            <label for="maquiladoraIdFK" class="form-label">Maquiladora *</label>
                            <select class="form-select" id="maquiladoraIdFK" name="maquiladoraIdFK" required>
                                <option value="">Seleccione una maquiladora</option>
                                <?php foreach ($maquiladoras as $m): ?>
                                    <option value="<?= $m['idmaquiladora'] ?>" <?= old('maquiladoraIdFK') == $m['idmaquiladora'] ? 'selected' : '' ?>>
                                        <?= esc($m['Nombre_Maquila']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                            <a href="<?= base_url('login') ?>" class="btn btn-link text-center">¿Ya tienes cuenta? Inicia sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>