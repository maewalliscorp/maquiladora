<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Información de la Maquiladora</h4>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editarMaquiladoraModal">
                        <i class="bi bi-pencil-square me-1"></i> Editar
                    </button>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>

                    <div class="row mb-4 align-items-center">
                        <div class="col-md-4 text-center">
                            <?php if (!empty($logo_base64)): ?>
                                <img src="data:image/jpeg;base64,<?= $logo_base64 ?>" alt="Logo Maquiladora" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 150px; width: 150px; margin: 0 auto;">
                                    <i class="bi bi-building text-secondary" style="font-size: 4rem;"></i>
                                </div>
                                <p class="text-muted mt-2 small">Sin logo</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light w-25">ID Maquiladora</th>
                                            <td><?= esc($maquiladora['idmaquiladora']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nombre</th>
                                            <td><?= esc($maquiladora['Nombre_Maquila']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Dueño / Rep.</th>
                                            <td><?= esc($maquiladora['Dueno'] ?? 'No especificado') ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Teléfono</th>
                                            <td><?= esc($maquiladora['Telefono'] ?? 'No especificado') ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Correo</th>
                                            <td><?= esc($maquiladora['Correo'] ?? 'No especificado') ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Domicilio</th>
                                            <td><?= esc($maquiladora['Domicilio'] ?? 'No especificado') ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Tipo</th>
                                            <td>
                                                <?php
                                                $tipo = $maquiladora['tipo'] ?? '';
                                                $badgeClass = match($tipo) {
                                                    'empresa' => 'bg-primary',
                                                    'sucursal' => 'bg-info',
                                                    'empresa externa' => 'bg-warning',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst($tipo)) ?: 'No especificado' ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Estatus</th>
                                            <td>
                                                <?php 
                                                    $status = $maquiladora['status'] ?? 0;
                                                    $badgeClass = $status == 1 ? 'bg-success' : 'bg-secondary';
                                                    $statusText = $status == 1 ? 'Activo' : 'Inactivo';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <small class="text-muted">Última consulta: <?= date('d/m/Y H:i') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar información -->
<div class="modal fade" id="editarMaquiladoraModal" tabindex="-1" aria-labelledby="editarMaquiladoraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editarMaquiladoraModalLabel">Editar Información de la Maquiladora</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarMaquiladora" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="idmaquiladora" value="<?= esc($maquiladora['idmaquiladora']) ?>">
                    
                    <div class="row g-3">
                        <div class="col-12 text-center mb-3">
                            <label for="logo_file" class="form-label d-block fw-bold">Logo de la Empresa</label>
                            <?php if (!empty($logo_base64)): ?>
                                <img src="data:image/jpeg;base64,<?= $logo_base64 ?>" class="img-thumbnail mb-2" style="max-height: 100px;">
                            <?php endif; ?>
                            <input type="file" class="form-control" id="logo_file" name="logo_file" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG. Máx 2MB.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="Nombre_Maquila" class="form-label">Nombre de la Maquiladora *</label>
                            <input type="text" class="form-control" id="Nombre_Maquila" name="Nombre_Maquila" 
                                   value="<?= esc($maquiladora['Nombre_Maquila']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="Dueno" class="form-label">Dueño / Representante</label>
                            <input type="text" class="form-control" id="Dueno" name="Dueno" 
                                   value="<?= esc($maquiladora['Dueno'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="Telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="Telefono" name="Telefono" 
                                   value="<?= esc($maquiladora['Telefono'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="Correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="Correo" name="Correo" 
                                   value="<?= esc($maquiladora['Correo'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label for="Domicilio" class="form-label">Domicilio</label>
                            <textarea class="form-control" id="Domicilio" name="Domicilio" rows="3"><?= esc($maquiladora['Domicilio'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo *</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="empresa" <?= ($maquiladora['tipo'] ?? '') == 'empresa' ? 'selected' : '' ?>>Empresa</option>
                                <option value="sucursal" <?= ($maquiladora['tipo'] ?? '') == 'sucursal' ? 'selected' : '' ?>>Sucursal</option>
                                <option value="empresa externa" <?= ($maquiladora['tipo'] ?? '') == 'empresa externa' ? 'selected' : '' ?>>Empresa Externa</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Estatus</label>
                            <select class="form-select" id="status" name="status" disabled>
                                <option value="1" <?= ($maquiladora['status'] ?? 0) == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($maquiladora['status'] ?? 0) == 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                            <small class="text-muted">El estatus no puede ser modificado</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarCambios">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditarMaquiladora');
    const btnGuardar = document.getElementById('btnGuardarCambios');
    const modalEl = document.getElementById('editarMaquiladoraModal');
    // Usar bootstrap.Modal.getOrCreateInstance es más seguro en BS5
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Manejo del envío del formulario con confirmación
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar alerta de confirmación
        Swal.fire({
            title: "¿Estás seguro?",
            text: "Se actualizará la información de la maquiladora.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, guardar cambios",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, procedemos a guardar
                guardarCambios();
            }
        });
    });

    function guardarCambios() {
        if (btnGuardar.disabled) return;
        
        // Bloquear botón
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

        const formData = new FormData(form);

        fetch('<?= base_url('maquiladora/update') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: "¡Actualizado!",
                    text: "La información ha sido guardada correctamente.",
                    icon: "success",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Aceptar"
                }).then(() => {
                    window.location.reload();
                });
                modal.hide();
            } else {
                throw new Error(data.message || 'Error al actualizar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: "Error",
                text: error.message || "Ocurrió un error al procesar la solicitud.",
                icon: "error",
                confirmButtonColor: "#d33",
                confirmButtonText: "Aceptar"
            });
        })
        .finally(() => {
            // Desbloquear botón (aunque si recarga la página no es estrictamente necesario, es buena práctica)
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar Cambios';
        });
    }

    // Previsualización de imagen
    const logoInput = document.getElementById('logo_file');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        title: "Archivo muy grande",
                        text: "El archivo no debe superar los 2MB.",
                        icon: "warning",
                        confirmButtonColor: "#3085d6"
                    });
                    logoInput.value = '';
                    return;
                }

                // Validar tipo
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    Swal.fire({
                        title: "Formato no válido",
                        text: "Solo se permiten archivos JPG, JPEG o PNG.",
                        icon: "warning",
                        confirmButtonColor: "#3085d6"
                    });
                    logoInput.value = '';
                    return;
                }

                // Mostrar previsualización
                const reader = new FileReader();
                reader.onload = function(e) {
                    let previousEl = logoInput.previousElementSibling;
                    let previewImg;

                    if (previousEl && previousEl.tagName === 'IMG') {
                        previewImg = previousEl;
                    } else {
                        previewImg = document.createElement('img');
                        previewImg.className = 'img-thumbnail mb-2';
                        previewImg.style.maxHeight = '100px';
                        logoInput.parentNode.insertBefore(previewImg, logoInput);
                    }
                    previewImg.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Limpiar formulario al cerrar
    modalEl.addEventListener('hidden.bs.modal', function () {
        form.reset();
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar Cambios';
    });
});
</script>
<?= $this->endSection() ?>