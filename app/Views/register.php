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
    <img src="<?= base_url('img/maquiladora.png') ?>" alt="Logo" width="60">
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
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>

                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>

                    <label class="form-label">Edad</label>
                    <input type="number" name="edad" class="form-control" required>

                    <label class="form-label">Domicilio</label>
                    <input type="text" name="domicilio" class="form-control" required>

                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" required>

                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                    <label class="form-label">Empresa a trabajar</label>
                    <select name="empresa" class="form-control">
                        <option>Nombre de empresa</option>
                        <option>Maewallis Corp</option>
                        <option>Otra empresa</option>
                    </select>

                    <label class="form-label">Puesto a solicitar</label>
                    <select name="puesto" class="form-control">
                        <option>Puestos</option>
                        <option>Operario</option>
                        <option>Supervisor</option>
                        <option>Diseñador</option>
                    </select>

                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required>

                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>

                    <button type="submit" class="btn btn-custom text-white w-100 mt-3">Enviar</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
