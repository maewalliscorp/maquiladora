<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Maquiladora</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #63677c;  color: #ffffff; }
        .navbar-custom { background-color: #5ca0d3; }
        .card { background-color: #847c84; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
        .form-section-title { font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #5ca0d3; padding-bottom: 5px; }
        .btn-primary-custom { background-color: #5ca0d3; border: none; font-weight: bold; }
        .btn-primary-custom:hover { background-color: #4a8ab3; }
        .btn-secondary-custom { background-color: #444; border: none; }
        .btn-secondary-custom:hover { background-color: #222; }
        .preview-box { background:#5ca0d3; border-radius:8px; padding:10px; margin-top:10px; text-align:center; }
        .preview-box img { max-width:100%; height:auto; border-radius:6px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <img src="<?= base_url('img/maquiladora.png') ?>" alt="Logo" width="60">
    <a class="navbar-brand text-dark fw-bold ms-2" href="#">Sistema de Maquiladora</a>
    <div class="ms-auto">
        <a href="<?= base_url('perfilempleado') ?>" class="btn btn-link text-dark">Mi perfil</a>
        <a href="<?= base_url('pedidos') ?>" class="btn btn-link text-dark">Pedidos</a>
        <a href="#" class="btn btn-link text-dark">Órdenes</a>
        <a href="<?= base_url('logout') ?>" class="btn btn-dark">Cerrar sesión</a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4 text-center fw-bold">Agregar Pedido</h2>

    <form action="<?= base_url('guardar_pedido') ?>" method="POST" enctype="multipart/form-data">

        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-6">
                <div class="card">
                    <h5 class="form-section-title">Cliente</h5>
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

                <div class="card">
                    <h5 class="form-section-title">Dirección</h5>
                    <div class="row">
                        <div class="col-8 mb-2">
                            <input type="text" class="form-control" placeholder="Calle" name="calle" required>
                        </div>
                        <div class="col-4 mb-2">
                            <input type="text" class="form-control" placeholder="No. Ext" name="numExt" required>
                        </div>
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
                    <input type="text" class="form-control mb-2" placeholder="C.P." name="cp" required>
                    <input type="text" class="form-control mb-2" value="México" name="pais" required>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-6">
                <div class="card">
                    <h5 class="form-section-title">Pedido</h5>
                    <textarea class="form-control mb-2" placeholder="Descripción" name="descripcion" rows="2" required></textarea>
                    <input type="number" class="form-control mb-2" placeholder="Cantidad" name="cantidad" min="1" required>

                    <textarea class="form-control mb-2" placeholder="Especificaciones técnicas" name="especificaciones" rows="2" required></textarea>
                    <textarea class="form-control mb-2" placeholder="Materiales" name="materiales" rows="2" required></textarea>

                    <!-- Modelo + Documento -->
                    <label class="form-label">Modelo</label>
                    <select class="form-select mb-2" id="modelo" name="modelo" required>
                        <option value="">Seleccionar...</option>
                        <option value="MODELO 1">MODELO 1</option>
                        <option value="MODELO 2">MODELO 2</option>
                        <option value="MODELO 3">MODELO 3</option>
                        <option value="OTRO">OTRO</option>
                    </select>

                    <label class="form-label">Subir archivo (opcional)</label>
                    <input type="file" class="form-control mb-2" id="archivo" name="archivo">

                    <!-- Vista previa -->
                    <div id="preview" class="preview-box d-none">
                        <p class="fw-bold">Vista previa:</p>
                        <div id="preview-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between mt-3">
            <a href="<?= base_url('pedidos') ?>" class="btn btn-secondary-custom px-4">Cancelar</a>
            <button type="submit" class="btn btn-primary-custom px-4">Agregar Pedido</button>
        </div>
    </form>
</div>

<!-- Script vista previa -->
<script>
    const modelo = document.getElementById('modelo');
    const archivo = document.getElementById('archivo');
    const preview = document.getElementById('preview');
    const content = document.getElementById('preview-content');

    modelo.addEventListener('change', () => {
        if (modelo.value) {
            preview.classList.remove('d-none');
            content.innerHTML = `<p class="text-dark">Has seleccionado: <strong>${modelo.value}</strong></p>`;
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
                content.innerHTML = `<p class="text-dark">Archivo cargado: <strong>${file.name}</strong></p>`;
            }
        }
    });
</script>
</body>
</html>
