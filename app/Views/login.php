<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Maquiladora</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
</head>
<body class="vh-100">

<div class="d-flex h-100">

    <!-- Panel izquierdo -->
    <div class="w-50 bg-primary d-flex flex-column justify-content-center align-items-center text-center text-light">
        <h1 class="fw-bold">Sistema de Maquiladora</h1>
        <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="350" class="my-3">
        <h2 class="text-secondary-emphasis fw-bold">Maewallis Corp</h2>
    </div>

    <!-- Panel derecho -->
    <div class="w-50 bg-secondary d-flex justify-content-center align-items-center">
        <div class="login-card" style="max-width: 380px; width: 100%;">
            <h3 class="mb-4 fw-bold text-center">Inicio de Sesión</h3>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post">
                <label for="usuario" class="form-label text-light">Usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control mb-3" placeholder="Usuario" required>

                <label for="password" class="form-label text-light">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control mb-3" placeholder="Contraseña"
                       required>

                <button type="submit" class="mt-4 btn btn-primary border border-black w-100">Ingresar</button>
                <div class="mt-3 text-center ">
                    <a href="<?= base_url('register') ?>" class="text-light link-light link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">¿No tienes cuenta? Regístrate</a>
                </div>
            </form>

        </div>
    </div>

</div>

</body>
</html>
