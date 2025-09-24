<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            color: #000000;
            background-color: #63677c;
        }
        .navbar-custom {
            background-color: #5ca0d3;
        }
        .card-profile {
            background: #e6e3d3;
            padding: 1.5rem;
            border-radius: 10px;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }
        .btn-upload {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<!-- Barra superior -->
<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <img src="<?= base_url('img/maquiladora.png') ?>" alt="Logo" width="60">
    <a class="navbar-brand text-dark fw-bold" href="#">Sistema de Maquiladora</a>
    <div class="ms-auto">
        <a href="<?= base_url('perfilempleado') ?>" class="btn btn-link text-dark">Mi perfil</a>
        <a href="<?= base_url('pedidos') ?>" class="btn btn-link text-dark">Pedidos</a>
        <a href="#" class="btn btn-link text-dark">Órdenes de pedidos</a>
        <a href="<?= base_url('logout') ?>" class="btn btn-dark">Cerrar sesión</a>
    </div>
</nav>

<!-- Contenido -->
<div class="container my-5">
    <h3 class="text-center mb-4 text-black">INFORMACIÓN DEL EMPLEADO</h3>
    <div class="row justify-content-center">
        <!-- Foto -->
        <div class="col-md-3 text-center">
            <img src="<?= base_url('assets/img/avatar.png') ?>" alt="Foto" class="profile-img">
            <button class="btn btn-dark btn-upload">Subir Foto</button>
        </div>

        <!-- Datos -->
        <div class="col-md-6">
            <div class="card-profile">
                <p><strong>Nombre completo:</strong> <?= $empleado['nombre'] ?? 'Ana Guadalupe Martínez Rodríguez' ?></p>
                <p><strong>Edad:</strong> <?= $empleado['edad'] ?? '28 años' ?></p>
                <p><strong>Fecha de Nacimiento:</strong> <?= $empleado['fecha_nac'] ?? '12 de Mayo de 1996' ?></p>
                <p><strong>CURP:</strong> <?= $empleado['curp'] ?? 'MARO960512MBCRDN01' ?></p>
                <p><strong>Domicilio:</strong> <?= $empleado['domicilio'] ?? 'Calle Industria #123, Col. Parque Industrial, Tijuana' ?></p>
                <p><strong>Teléfono:</strong> <?= $empleado['telefono'] ?? '(664) 123-4567' ?></p>
                <p><strong>Email:</strong> <?= $empleado['email'] ?? 'ana.martinez@email.com' ?></p>

                <hr>
                <h6><strong>Información Laboral:</strong></h6>
                <p><strong>Puesto:</strong> <?= $empleado['puesto'] ?? 'Inspector de Control de Pedidos y Calidad' ?></p>
                <p><strong>Matrícula/Número de Empleado:</strong> <?= $empleado['matricula'] ?? '84752' ?></p>
                <p><strong>Fecha de Ingreso:</strong> <?= $empleado['fecha_ingreso'] ?? '15 de Agosto de 2025' ?></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
