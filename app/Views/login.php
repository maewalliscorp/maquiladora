<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login - Maquiladora</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <h3 class="mb-4 fw-bold text-center text-light">Inicio de Sesión</h3>

            <?php $successMsg = session()->getFlashdata('success'); ?>
            <?php if ($successMsg): ?>
                <div class="alert alert-success d-none" role="alert" id="successInline">
                    <?= esc($successMsg) ?>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Registro exitoso',
                            text: document.getElementById('successInline')?.textContent?.trim() || '',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            heightAuto: false
                        });
                    });
                </script>
            <?php endif; ?>

            <?php $attempted = session()->getFlashdata('login_attempted'); ?>
            <?php if (session()->getFlashdata('error') && $attempted): ?>
                <div class="alert alert-danger d-none" id="errorInline" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Error',
                            text: document.getElementById('errorInline')?.textContent?.trim() || '',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            heightAuto: false
                        });
                    });
                </script>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post" id="loginForm">
                <!-- Cambiado a correo -->
                <label for="correo" class="form-label text-light">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control mb-3"
                       placeholder="ejemplo@correo.com" required>

                <label for="password" class="form-label text-light">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control mb-3"
                       placeholder="Contraseña" required>

                <button type="submit" class="mt-4 btn btn-primary border border-black w-100">Ingresar</button>

                <div class="mt-3 text-center">
                    <a href="<?= base_url('register') ?>" class="text-light link-light link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">
                        ¿No tienes cuenta? Regístrate
                    </a>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
  // Si volvemos a esta página desde el historial (bfcache), forzar recarga
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) {
      location.reload();
    }
  });
  // Ayuda a que algunos navegadores no cacheen esta página
  window.addEventListener('unload', function () {});
  // Como refuerzo adicional, si detectamos que hay sesión (por error de caché), redirigimos a /logout
  // Nota: no tenemos acceso a PHP aquí, pero si tuvieras una variable global renderizada, podríamos usarla.
  // En login ya no se usa confirmación; el Swal solo se muestra si hay error en flashdata
</script>

</body>
</html>
