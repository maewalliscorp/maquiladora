<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Maquiladora</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/f2923602be.js" crossorigin="anonymous"></script>
    <!-- FontAwesome -->
</head>
<body>

<section class=" d-flex align-items-center justify-content-center"">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card p-md-5">
                    <div class="row align-items-center">
                        <!-- Imagen lado derecho -->
                        <div class="col-md-6 d-none d-md-flex justify-content-center">
                            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="350" class="my-3">
                        </div>

                        <!-- Formulario -->
                        <div class="col-md-6">
                            <div class="text-center logo">
                                <h4>Sistema de Maquiladora</h4>
                            </div>

                            <h3 class="text-center fw-bold mb-4">Registro</h3>

                            <form action="<?= base_url('auth/register') ?>" method="post" id="registroForm">

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-user me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-user-tag me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="text" name="apellidoPaterno" class="form-control" placeholder="Apellido paterno" required>
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-user-tag me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="text" name="apellidoMaterno" class="form-control" placeholder="Apellido materno">
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-envelope me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="email" name="email" class="form-control" required placeholder="Correo electronico">
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-phone me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="number" name="telefono" class="form-control" required placeholder="Numero de telefono">
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-2" style="min-width:25px;text-align:center;"></i>
                                    <textarea name="domicilio" class="form-control" rows="2" required placeholder="Domicilio"></textarea>
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="text" name="usuario" class="form-control" required placeholder="Usuario">
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-lock me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="password" name="password" id="password" class="form-control" required placeholder="Contraseña">
                                </div>

                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-key me-2" style="min-width:25px;text-align:center;"></i>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Confirmar contraseña">
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required>
                                    <label class="form-check-label" for="terminos">
                                        Acepto los <a href="#">términos y condiciones</a>
                                    </label>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary px-5 py-2">
                                        <i class="fas fa-user-plus me-2"></i>Registrarse
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
<script>
    // Validación de contraseñas
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    confirmPassword.addEventListener('input', () => {
        confirmPassword.setCustomValidity(
            password.value !== confirmPassword.value ? 'Las contraseñas no coinciden' : ''
        );
    });

    document.getElementById('registroForm').addEventListener('submit', e => {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
        }
    });
</script>

</body>
</html>
