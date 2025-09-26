<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <style>
        .form-section-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid var(--color-primary-700);
            padding-bottom: 5px;
            color: var(--color-text);
        }
        .btn-primary-custom {
            background-color: var(--color-primary-700);
            border: none;
            font-weight: bold;
        }
        .btn-primary-custom:hover {
            background-color: var(--color-primary-600);
        }
        .btn-secondary-custom {
            background-color: #444;
            border: none;
        }
        .btn-secondary-custom:hover {
            background-color: #222;
        }
        .preview-box {
            background: var(--color-primary);
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
            color: var(--color-text);
        }
        .preview-box img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
        }

        /* Estilos para evitar superposición */
        .row {
            margin-right: -5px;
            margin-left: -5px;
        }
        .col-md-6 {
            padding-right: 5px;
            padding-left: 5px;
        }
        .card {
            margin-bottom: 10px;
        }
        .card-body {
            padding: 15px;
        }
        .mb-2 {
            margin-bottom: 0.5rem !important;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Agregar Pedido</h1>
        <span class="badge bg-primary">Módulo 1</span>
    </div>
    <form action="<?= base_url('modulo1/agregar') ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Cliente</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label">Empresa</label>
                            <input type="text" class="form-control" name="empresa" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Contacto</label>
                            <input type="text" class="form-control" name="contacto" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Dirección</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8 mb-2">
                                <input type="text" class="form-control" placeholder="Calle" name="calle" required>
                            </div>
                            <div class="col-4 mb-2">
                                <input type="text" class="form-control" placeholder="No. Ext" name="numExt" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 mb-2">
                                <input type="text" class="form-control" placeholder="No. Int" name="numInt">
                            </div>
                            <div class="col-4 mb-2">
                                <input type="text" class="form-control" placeholder="Ciudad" name="ciudad" required>
                            </div>
                            <div class="col-4 mb-2">
                                <input type="text" class="form-control" placeholder="Estado" name="estado" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" placeholder="C.P." name="cp" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" value="México" name="pais" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Pedido</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Descripción" name="descripcion" rows="2" required></textarea>
                        </div>
                        <div class="mb-2">
                            <input type="number" class="form-control" placeholder="Cantidad" name="cantidad" min="1" required>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Especificaciones técnicas" name="especificaciones" rows="2" required></textarea>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control" placeholder="Materiales" name="materiales" rows="2" required></textarea>
                        </div>

                        <!-- Modelo + Documento -->
                        <div class="mb-2">
                            <label class="form-label">Modelo</label>
                            <select class="form-select" id="modelo" name="modelo" required>
                                <option value="">Seleccionar...</option>
                                <option value="MODELO 1">MODELO 1</option>
                                <option value="MODELO 2">MODELO 2</option>
                                <option value="MODELO 3">MODELO 3</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Subir archivo (opcional)</label>
                            <input type="file" class="form-control" id="archivo" name="archivo">
                        </div>

                        <!-- Vista previa -->
                        <div id="preview" class="preview-box d-none">
                            <p class="fw-bold">Vista previa:</p>
                            <div id="preview-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between mt-3">
            <a href="<?= base_url('modulo1') ?>" class="btn btn-secondary-custom px-4">Cancelar</a>
            <button type="submit" class="btn btn-primary-custom px-4">Agregar Pedido</button>
        </div>
    </form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- Script vista previa -->
    <script>
        const modelo = document.getElementById('modelo');
        const archivo = document.getElementById('archivo');
        const preview = document.getElementById('preview');
        const content = document.getElementById('preview-content');

        modelo.addEventListener('change', () => {
            if (modelo.value) {
                preview.classList.remove('d-none');
                content.innerHTML = `<p>Has seleccionado: <strong>${modelo.value}</strong></p>`;
            }
        });

        archivo.addEventListener('change', () => {
            const file = archivo.files[0];
            if (file) {
                preview.classList.remove('d-none');
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => content.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
                    reader.readAsDataURL(file);
                } else {
                    content.innerHTML = `<p>Archivo cargado: <strong>${file.name}</strong></p>`;
                }
            }
        });
    </script>
<?= $this->endSection() ?>