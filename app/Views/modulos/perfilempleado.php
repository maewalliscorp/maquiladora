<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Perfil del Empleado</h1>
    <span class="badge bg-primary">Módulo 1</span>
</div>
<div class="row justify-content-center">
    <!-- Foto -->
    <div class="col-md-3 text-center">
        <div class="card shadow-sm">
            <div class="card-body">
                <img src="<?= base_url('assets/img/avatar.png') ?>" alt="Foto" class="profile-img">
                <button class="btn btn-primary btn-upload">Subir Foto</button>
            </div>
        </div>
    </div>

    <!-- Datos -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Información Personal</strong>
            </div>
            <div class="card-body">
                <div class="info-section">
                    <h6>Datos Personales</h6>
                    <p><strong>Nombre completo:</strong> <?= esc($empleado['nombre'] ?? '') ?></p>
                    <p><strong>Edad:</strong> <?= esc($empleado['edad'] ?? '') ?></p>
                    <p><strong>Fecha de Nacimiento:</strong> <?= esc($empleado['fecha_nac'] ?? '') ?></p>
                    <p><strong>CURP:</strong> <?= esc($empleado['curp'] ?? '') ?></p>
                </div>

                <div class="info-section">
                    <h6>Contacto</h6>
                    <p><strong>Domicilio:</strong> <?= esc($empleado['domicilio'] ?? '') ?></p>
                    <p><strong>Teléfono:</strong> <?= esc($empleado['telefono'] ?? '') ?></p>
                    <p><strong>Email:</strong> <?= esc($empleado['email'] ?? '') ?></p>
                </div>

                <div class="info-section">
                    <h6>Información Laboral</h6>
                    <p><strong>Puesto:</strong> <?= esc($empleado['puesto'] ?? '') ?></p>
                    <p><strong>Matrícula/Número de Empleado:</strong> <?= esc($empleado['matricula'] ?? '') ?></p>
                    <p><strong>Fecha de Ingreso:</strong> <?= esc($empleado['fecha_ingreso'] ?? '') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
