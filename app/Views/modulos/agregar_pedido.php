<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h1 class="me-3">Agregar Pedido</h1>
            <span class="badge bg-primary">Módulo 1</span>
        </div>

        <form action="<?= base_url('modulo1/agregar') ?>" method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- Columna izquierda -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light">
                            <strong>Cliente</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Empresa</label>
                                <input type="text" class="form-control" name="empresa" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contacto</label>
                                <input type="text" class="form-control" name="contacto" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono" required>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <strong>Dirección</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-8">
                                    <label class="form-label">Calle</label>
                                    <input type="text" class="form-control" placeholder="Calle" name="calle" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">No. Ext</label>
                                    <input type="text" class="form-control" placeholder="No. Ext" name="numExt" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-4">
                                    <label class="form-label">No. Int</label>
                                    <input type="text" class="form-control" placeholder="No. Int" name="numInt">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" placeholder="Ciudad" name="ciudad" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Estado</label>
                                    <input type="text" class="form-control" placeholder="Estado" name="estado" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Código Postal</label>
                                <input type="text" class="form-control" placeholder="C.P." name="cp" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">País</label>
                                <input type="text" class="form-control" value="México" name="pais" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <strong>Pedido</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" placeholder="Descripción del pedido" name="descripcion" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control" placeholder="Cantidad" name="cantidad" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Especificaciones técnicas</label>
                                <textarea class="form-control" placeholder="Especificaciones técnicas" name="especificaciones" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Materiales</label>
                                <textarea class="form-control" placeholder="Materiales requeridos" name="materiales" rows="2" required></textarea>
                            </div>

                            <!-- Modelo + Documento -->
                            <div class="mb-3">
                                <label class="form-label">Modelo</label>
                                <select class="form-select" id="modelo" name="modelo" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="MODELO 1">MODELO 1</option>
                                    <option value="MODELO 2">MODELO 2</option>
                                    <option value="MODELO 3">MODELO 3</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subir archivo (opcional)</label>
                                <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.dwg,.dxf">
                                <div class="form-text">Formatos aceptados: PDF, DOC, JPG, PNG, DWG, DXF</div>
                            </div>

                            <!-- Vista previa -->
                            <div id="preview" class="card bg-light mt-3 d-none">
                                <div class="card-body">
                                    <h6 class="card-title">Vista previa</h6>
                                    <div id="preview-content" class="text-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-between mt-4">
                <a href="<?= base_url('modulo1') ?>" class="btn btn-secondary px-4">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    Agregar Pedido
                </button>
            </div>
        </form>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script vista previa -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modelo = document.getElementById('modelo');
            const archivo = document.getElementById('archivo');
            const preview = document.getElementById('preview');
            const content = document.getElementById('preview-content');

            modelo.addEventListener('change', () => {
                if (modelo.value) {
                    preview.classList.remove('d-none');
                    content.innerHTML = `
                    <div class="alert alert-info mb-0">
                        Has seleccionado: <strong>${modelo.value}</strong>
                    </div>
                `;
                } else {
                    preview.classList.add('d-none');
                }
            });

            archivo.addEventListener('change', () => {
                const file = archivo.files[0];
                if (file) {
                    preview.classList.remove('d-none');
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = e => content.innerHTML = `
                        <div class="alert alert-success mb-2">
                            Imagen cargada: <strong>${file.name}</strong>
                        </div>
                        <img src="${e.target.result}" alt="Vista previa" class="img-fluid rounded" style="max-height: 200px;">
                    `;
                        reader.readAsDataURL(file);
                    } else {
                        content.innerHTML = `
                        <div class="alert alert-warning mb-0">
                            Archivo cargado: <strong>${file.name}</strong>
                            <br><small class="text-muted">Tipo: ${file.type || 'No especificado'}</small>
                        </div>
                    `;
                    }
                } else {
                    if (!modelo.value) {
                        preview.classList.add('d-none');
                    }
                }
            });

            // Validación del formulario
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos requeridos.');
                }
            });
        });
    </script>
<?= $this->endSection() ?>