<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .estatus {
            font-weight: bold;
            color: #000;
            padding: 5px 12px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .estatus-verde { background-color: #4caf50; }
        .estatus-amarillo { background-color: #ffeb3b; }
        .estatus-rojo { background-color: #f44336; }
        .btn-accion {
            margin: 0 5px;
        }

        /* Estilos específicos para el buscador */
        .dataTables_wrapper .dataTables_filter {
            text-align: center;
            margin-bottom: 15px;
        }
        .dataTables_wrapper .dataTables_filter label {
            color: var(--color-text);
            font-size: 1.1rem;
            font-weight: bold;
        }
        .dataTables_wrapper .dataTables_filter input {
            background-color: #F3F8FE !important;
            color: #333 !important;
            border: 1px solid #d7e3ef !important;
            border-radius: 5px !important;
            padding: 8px 12px !important;
            font-size: 1rem !important;
            width: 300px !important;
            margin-left: 10px !important;
        }

        /* Estilos para los iconos de documentos */
        .doc-icon {
            font-size: 1.2rem;
            margin: 0 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .doc-icon:hover {
            transform: scale(1.2);
        }
        .ver-doc { color: #0d6efd; }
        .descargar-doc { color: #198754; }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <h1 class="me-3">Órdenes de pedidos</h1>
        <span class="badge bg-primary"></span>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Lista de Órdenes de Pedidos</strong>
        </div>
        <div class="card-body">
            <table id="tablaPedidos" class="table table-striped table-bordered text-center align-middle">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>EMPRESA</th>
                    <th>PERSONA</th>
                    <th>VER DOC.</th>
                    <th>DESCARGAR</th>
                </tr>
                </thead>
                <tbody>
                <!-- Los datos se cargarán dinámicamente -->
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
            $('#tablaPedidos').DataTable({
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
                },
                // Datos de ejemplo (simulados)
                data: [
                    {
                        "no": 1,
                        "empresa": "Textiles del Norte S.A. de C.V.",
                        "persona": "María Fernanda Castillo Gómez"
                    },
                    {
                        "no": 2,
                        "empresa": "Hilados y Telas del Bajío S.A. de C.V.",
                        "persona": "Luis Alberto Ramírez Torres"
                    },
                    {
                        "no": 3,
                        "empresa": "Confecciones Industriales de México S.A. de C.V.",
                        "persona": "Karla Sofía Méndez Ríos"
                    },
                    {
                        "no": 4,
                        "empresa": "Moda y Estilo Textil S.A. de C.V",
                        "persona": "Jorge Antonio Herrera Díaz"
                    }
                ],
                columns: [
                    { data: 'no' },
                    { data: 'empresa' },
                    { data: 'persona' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <i class="bi bi-file-earmark-pdf doc-icon ver-doc" title="Ver PDF"></i>
                                <i class="bi bi-file-earmark-image doc-icon ver-doc" title="Ver imagen"></i>
                                <i class="bi bi-file-earmark-spreadsheet doc-icon ver-doc" title="Ver hoja de cálculo"></i>
                            `;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <i class="bi bi-download doc-icon descargar-doc" title="Descargar documento"></i>
                            `;
                        }
                    }
                ]
            });

            // Evento para los iconos de ver documento
            $('#tablaPedidos').on('click', '.ver-doc', function() {
                const docType = $(this).attr('title');
                alert(`Función para ${docType} - Pendiente de implementar con backend`);
            });

            // Evento para los iconos de descargar
            $('#tablaPedidos').on('click', '.descargar-doc', function() {
                const downloadType = $(this).attr('title');
                alert(`Función para ${downloadType} - Pendiente de implementar con backend`);
            });
        });

        function eliminarPedido(id) {
            // Pendiente: implementar con backend cuando la BD esté lista
            alert("Función no disponible hasta conectar la BD.");
        }
    </script>
<?= $this->endSection() ?>