<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="me-3">CATÁLOGO DE DISEÑOS</h1>
        <a href="<?= base_url('modulo2/agregardiseno') ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> NUEVO DISEÑO
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <table id="tablaDisenos" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>MATERIALES</th>
                    <th>DETALLES</th>
                    <th>ACCIONES</th>
                    <th>DISEÑO</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td><strong>Blusa "Luna Casual"</strong></td>
                    <td>Blusa femenina de manga corta, cuello redondo, corte recto con pinzas en el busto. Ideal para uso casual.</td>
                    <td class="text-start">
                        <ul class="material-list">
                            <li>Tela principal: Rayon stretch 95% + spandex 5%, ancho 1.50 m.</li>
                            <li>Hilo poliéster color a juego.</li>
                            <li>1 etiqueta interior.</li>
                        </ul>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-accion" title="Editar" data-id="1">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar" data-id="1">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    <td>
                        <div style="width: 80px; height: 80px; background: #f0f0f0; border: 1px solid #ddd; margin: 0 auto;">
                            <span style="line-height: 80px; color: #666;">Imagen</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td><strong>Pantalón "Urban Fit"</strong></td>
                    <td>Pantalón unisex estilo jogger con pretina elástica y bolsas laterales. Diseño cómodo y moderno para uso diario.</td>
                    <td class="text-start">
                        <ul class="material-list">
                            <li>Tela principal: Gabardina ligera 100% algodón.</li>
                            <li>Elástico de 4 cm (1.5 m por prenda).</li>
                            <li>Hilo poliéster resistente.</li>
                            <li>Cordón decorativo (opcional).</li>
                        </ul>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-accion" title="Editar" data-id="2">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar" data-id="2">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    <td>
                        <div style="width: 80px; height: 80px; background: #f0f0f0; border: 1px solid #ddd; margin: 0 auto;">
                            <span style="line-height: 80px; color: #666;">Imagen</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td><strong>Camisa "Oxford Classic"</strong></td>
                    <td>Camisa de manga larga con cuello clásico, botones frontales y puños ajustables. Formal y elegante.</td>
                    <td class="text-start">
                        <ul class="material-list">
                            <li>Tela principal: Oxford 60% algodón / 40% poliéster, ancho 1.50 m.</li>
                            <li>Botones plásticos de 4 orificios (8-10 piezas por camisa).</li>
                        </ul>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-accion" title="Editar" data-id="3">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-accion" title="Eliminar" data-id="3">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    <td>
                        <div style="width: 80px; height: 80px; background: #f0f0f0; border: 1px solid #ddd; margin: 0 auto;">
                            <span style="line-height: 80px; color: #666;">Imagen</span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- JS Bootstrap + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaDisenos').DataTable({
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad"
                    }
                }
            });

            // Eventos para los botones de acción
            $('.btn-accion').on('click', function() {
                const action = $(this).attr('title');
                const id = $(this).data('id');

                if (action === 'Editar') {
                    // Redirigir a la página de edición con el ID del diseño
                    window.location.href = '<?= base_url('modulo2/editardiseno/') ?>' + id;
                } else if (action === 'Eliminar') {
                    if (confirm('¿Estás seguro de que deseas eliminar este diseño?')) {
                        // Lógica para eliminar
                        alert('Diseño con ID ' + id + ' eliminado (simulación)');
                    }
                }
            });
        });
    </script>
<?= $this->endSection() ?>