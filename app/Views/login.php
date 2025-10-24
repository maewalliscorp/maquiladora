<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Control de caché -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>Login - Sistema de Maquiladora</title>

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
</head>

<body class="bg-light">

<div class="container-fluid vh-100">
    <div class="row h-100">

        <!-- Panel izquierdo -->
        <div class="col-12 col-md-6 d-flex flex-column justify-content-center align-items-center text-white text-center p-5"
             style="background-color: #2c5364;">
            <h1 class="fw-bold">Sistema de Maquiladora</h1>
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" class="img-fluid my-4"
                 style="max-width: 300px;">
            <h2 class="fw-semibold">Maewallis Corp</h2>
        </div>

        <!-- Panel derecho -->
        <div class="col-12 col-md-6 d-flex justify-content-center align-items-center bg-white">
            <div class="card shadow p-4 w-100" style="max-width: 400px;">
                <h3 class="text-center fw-bold mb-4">Inicio de Sesión</h3>

                <!-- Mensaje de error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= session()->getFlashdata('error') ?>
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

                <form action="<?= base_url('login') ?>" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control"
                               placeholder="ejemplo@correo.com" required>
                        <div class="invalid-feedback">
                            Por favor ingresa un correo válido.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Contraseña" required>
                        <div class="invalid-feedback">
                            La contraseña es obligatoria.
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 text-white fw-bold" style="background-color: #2c5364;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                    </button>

                    <div class="mt-3 text-center">
                        <a href="<?= base_url('register') ?>" class="text-decoration-none text-primary fw-semibold">
                            ¿No tienes cuenta? Regístrate
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Validación Bootstrap sdasdasd-->
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