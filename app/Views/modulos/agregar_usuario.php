<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="<?= base_url('modulo11/usuarios') ?>" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <h1>Agregar Nuevo Usuario</h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Información del Usuario</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('modulo11/agregar') ?>">
                <!-- Información de Usuario -->
                <h5 class="mb-3 text-primary">Información de Usuario</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label">Nombre de Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idMaquiladora" class="form-label">ID Maquiladora</label>
                        <input type="number" class="form-control" id="idMaquiladora" name="idMaquiladora" 
                               placeholder="Ingrese el ID de la maquiladora">
                    </div>
                </div>

                <!-- Información de Empleado -->
                <h5 class="mb-3 text-primary mt-4">Información de Empleado</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="noEmpleado" class="form-label">Número de Empleado *</label>
                        <input type="text" class="form-control" id="noEmpleado" name="noEmpleado" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellido" class="form-label">Apellido *</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono *</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="puesto" class="form-label">Puesto *</label>
                        <select class="form-select" id="puesto" name="puesto" required>
                            <option value="">Seleccionar puesto...</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Operador">Operador</option>
                            <option value="Diseñador">Diseñador</option>
                            <option value="Jefe de Producción">Jefe de Producción</option>
                            <option value="Coordinador">Coordinador</option>
                            <option value="Analista">Analista</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="domicilio" class="form-label">Domicilio *</label>
                        <textarea class="form-control" id="domicilio" name="domicilio" rows="3" required></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enviar_email" name="enviar_email" checked>
                            <label class="form-check-label" for="enviar_email">
                                Enviar credenciales por correo electrónico
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('modulo11/usuarios') ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Agregar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Validación de contraseñas
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
        });
    </script>
<?= $this->endSection() ?>
