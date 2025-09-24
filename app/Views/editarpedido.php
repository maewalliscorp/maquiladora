<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido - Maquiladora</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {   background-color: #63677c;color: #ffffff; }
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
    <a class="navbar-brand text-black fw-bold ms-2" href="#">Sistema de Maquiladora</a>
    <div class="ms-auto">
        <a href="<?= base_url('perfilempleado') ?>" class="btn btn-link text-dark">Mi perfil</a>
        <a href="<?= base_url('pedidos') ?>" class="btn btn-link text-dark">Pedidos</a>
        <a href="#" class="btn btn-link text-dark">Órdenes</a>
        <a href="<?= base_url('logout') ?>" class="btn btn-dark">Cerrar sesión</a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4 text-center fw-bold">✏️ Editar Pedido</h2>

    <?php
    // Pedido de ejemplo (simulado desde la BD)
    $pedido = [
        'empresa' => 'Textiles del Norte S.A. de C.V.',
        'contacto' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'telefono' => '6641234567',
        'descripcion' => 'Camiseta de piqué algodón 100%, cuello redondo, corte regular.',
        'cantidad' => 100,
        'especificaciones' => 'Color azul marino',
        'materiales' => 'Algodón 100%',
        'modelo' => 'MODELO 1'
    ];
    ?>

    <form id="formEditarPedido" action="<?= base_url('actualizar_pedido/1') ?>" method="POST">
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-6">
                <div class="card">
                    <h5 class="form-section-title">Cliente</h5>
                    <input type="text" class="form-control mb-2" name="empresa" value="<?= $pedido['empresa'] ?>" required>
                    <input type="text" class="form-control mb-2" name="contacto" value="<?= $pedido['contacto'] ?>" required>
                    <input type="email" class="form-control mb-2" name="email" value="<?= $pedido['email'] ?>" required>
                    <input type="tel" class="form-control mb-2" name="telefono" value="<?= $pedido['telefono'] ?>" required>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-6">
                <div class="card">
                    <h5 class="form-section-title">Pedido</h5>
                    <textarea class="form-control mb-2" name="descripcion" rows="2"><?= $pedido['descripcion'] ?></textarea>
                    <input type="number" class="form-control mb-2" name="cantidad" value="<?= $pedido['cantidad'] ?>" required>
                    <textarea class="form-control mb-2" name="especificaciones"><?= $pedido['especificaciones'] ?></textarea>
                    <textarea class="form-control mb-2" name="materiales"><?= $pedido['materiales'] ?></textarea>

                    <!-- Selección de modelo -->
                    <label class="form-label">Modelo</label>
                    <select class="form-select mb-2" id="modelo" name="modelo" required>
                        <option value="MODELO 1" <?= $pedido['modelo']=='MODELO 1'?'selected':'' ?>>MODELO 1</option>
                        <option value="MODELO 2" <?= $pedido['modelo']=='MODELO 2'?'selected':'' ?>>MODELO 2</option>
                        <option value="MODELO 3" <?= $pedido['modelo']=='MODELO 3'?'selected':'' ?>>MODELO 3</option>
                        <option value="OTRO" <?= $pedido['modelo']=='OTRO'?'selected':'' ?>>OTRO</option>
                    </select>

                    <!-- Vista previa del modelo -->
                    <div id="preview" class="preview-box">
                        <p class="fw-bold text-dark">Vista previa:</p>
                        <div id="preview-content" class="text-dark"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-between mt-3">
            <a href="<?= base_url('pedidos') ?>" class="btn btn-secondary-custom px-4">Cancelar</a>
            <button type="submit" id="btnActualizar" class="btn btn-primary-custom px-4">Actualizar Pedido</button>
        </div>
    </form>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Diccionario de modelos con imagen/descripción
    const modelos = {
        "MODELO 1": {
            desc: "Modelo 1: Camiseta clásica de algodón",
            img: "<?= base_url('img/modelo1.png') ?>"
        },
        "MODELO 2": {
            desc: "Modelo 2: Pantalón de mezclilla slim fit",
            img: "<?= base_url('img/modelo2.png') ?>"
        },
        "MODELO 3": {
            desc: "Modelo 3: Chaqueta oversize de mezclilla",
            img: "<?= base_url('img/modelo3.png') ?>"
        },
        "OTRO": {
            desc: "Otro: Modelo personalizado",
            img: "<?= base_url('img/default.png') ?>"
        }
    };

    // Función para actualizar la vista previa
    function actualizarPreview(valor) {
        const content = document.getElementById('preview-content');
        if (modelos[valor]) {
            content.innerHTML = `
            <p>${modelos[valor].desc}</p>
            <img src="${modelos[valor].img}" alt="${valor}">
        `;
        } else {
            content.innerHTML = "<p>No hay vista previa</p>";
        }
    }

    // Inicializar preview al cargar
    document.addEventListener('DOMContentLoaded', () => {
        const modelo = document.getElementById('modelo');
        actualizarPreview(modelo.value);
        modelo.addEventListener('change', () => actualizarPreview(modelo.value));

        // Manejo del envío del formulario
        document.getElementById('formEditarPedido').addEventListener('submit', function(e) {
            e.preventDefault(); // Evita el envío inmediato

            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '✅ Guardado exitosamente',
                text: 'El pedido se actualizó correctamente',
                confirmButtonText: 'OK'
            }).then(() => {
                // Enviar el formulario después de que el usuario confirme
                this.submit();
            });
        });
    });
</script>

</body>
</html>