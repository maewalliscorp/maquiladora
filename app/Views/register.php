<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Control de caché -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Registro - Maquiladora</title>

    <!-- Bootstrap 5 con integridad y CORS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
</head>

<body class="bg-light">

<div class="container-fluid vh-100">
    <div class="row h-100">

        <!-- Panel izquierdo -->
        <div class="col-12 col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-white text-center p-5"
             style="background-color: #2c5364;">
            <h1 class="fw-bold">Sistema de Maquiladora</h1>
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" class="img-fluid my-4"
                 style="max-width: 300px;">
            <h2 class="fw-semibold">Maewallis Corp</h2>
        </div>

        <!-- Panel derecho -->
        <div class="col-12 col-lg-6 d-flex justify-content-center align-items-center bg-white">
            <div class="card shadow p-4 w-100" style="max-width: 400px;">
                <h3 class="text-center fw-bold mb-4">Crear cuenta</h3>

                <!-- Errores -->
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Modal de éxito -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="modal fade" id="registerSuccessModal" tabindex="-1"
                         aria-labelledby="registerSuccessLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="registerSuccessLabel">Registro exitoso</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <?= esc(session()->getFlashdata('success')) ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var modalEl = document.getElementById('registerSuccessModal');
                            if (modalEl && window.bootstrap) {
                                var modal = new bootstrap.Modal(modalEl);
                                modal.show();
                            }
                        });
                    </script>
                <?php endif; ?>

                <form action="<?= base_url('/register/store') ?>" method="post" id="registerForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de usuario *</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= old('username') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico *</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?= old('correo') ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password" required
                               minlength="6">
                    </div>

                    <div class="mb-3">
                        <label for="password_verify" class="form-label">Confirmar contraseña *</label>
                        <input type="password" class="form-control" id="password_verify" name="password_verify"
                               required>
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
                        <button type="submit" class="btn text-white fw-bold" style="background-color: #2c5364;">
                            <i class="bi bi-person-plus-fill me-2"></i>Registrarse
                        </button>
                        <a href="<?= base_url('login') ?>" class="btn btn-link text-center">¿Ya tienes cuenta? Inicia
                            sesión</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Control de cache al volver atras sdasad-->
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