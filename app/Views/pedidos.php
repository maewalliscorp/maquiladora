<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Maquiladora</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            background-color: #63677c;
            color: #ffffff;
        }
        .navbar-custom {
            background-color: #5ca0d3;
        }
        .card {
            background-color: #847c84;
            border-radius: 10px;
        }
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
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .dataTables_wrapper .dataTables_filter input {
            background-color: white !important;
            color: #333 !important;
            border: 1px solid #ccc !important;
            border-radius: 5px !important;
            padding: 8px 12px !important;
            font-size: 1rem !important;
            width: 300px !important;
            margin-left: 10px !important;
        }
    </style>
</head>
<body>
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

<div class="container mt-4">
    <h2 class="text-center mb-4">📦 Pedidos</h2>

    <div class="mb-3 text-end">
        <a href="<?= base_url('agregar_pedido') ?>" class="btn btn-primary">➕ Nuevo Pedido</a>
    </div>

    <div class="card p-3">
        <table id="tablaPedidos" class="table table-striped table-bordered text-center align-middle">
            <thead>
            <tr>
                <th>No.</th>
                <th>Empresa</th>
                <th>Descripción</th>
                <th>Estatus</th>
                <th>Acciones</th>
                <th>Documento</th>
            </tr>
            </thead>
            <tbody>
            <!-- Ejemplo de datos locales (si no hay BD aún) -->
            <tr>
                <td>1</td>
                <td>Textiles del Norte S.A. de C.V.</td>
                <td>Camiseta de piqué algodón 100%, cuello redondo, corte regular.</td>
                <td>
                    <a href="<?= base_url('detalle_pedido/1') ?>" class="estatus estatus-verde">
                        75%
                    </a>
                </td>
                <td>
                    <a href="<?= base_url('editarpedido/1') ?>" class="btn btn-warning btn-sm btn-accion">✏️</a>
                    <button class="btn btn-danger btn-sm btn-accion" onclick="eliminarPedido(1)">🗑️</button>
                </td>
                <td><a href="#" class="btn btn-outline-light btn-sm">📄 DOC</a></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Hilados y Telas del Bajío S.A. de C.V.</td>
                <td>Jeans tipo "Skinny" dama, lavado claro.</td>
                <td>
                    <a href="<?= base_url('detalle_pedido/2') ?>" class="estatus estatus-amarillo">
                        50%
                    </a>
                </td>
                <td>
                    <a href="<?= base_url('editarpedido/2') ?>" class="btn btn-warning btn-sm btn-accion">✏️</a>
                    <button class="btn btn-danger btn-sm btn-accion" onclick="eliminarPedido(2)">🗑️</button>
                </td>
                <td><a href="#" class="btn btn-outline-light btn-sm">📄 DOC</a></td>
            </tr>
            <tr>
                <td>3</td>
                <td>Confecciones Industriales de México S.A. de C.V.</td>
                <td>Conjunto de uniforme recepción.</td>
                <td>
                    <a href="<?= base_url('detalle_pedido/3') ?>" class="estatus estatus-verde">
                        90%
                    </a>
                </td>
                <td>
                    <a href="<?= base_url('editarpedido/3') ?>" class="btn btn-warning btn-sm btn-accion">✏️</a>
                    <button class="btn btn-danger btn-sm btn-accion" onclick="eliminarPedido(3)">🗑️</button>
                </td>
                <td><a href="#" class="btn btn-outline-light btn-sm">📄 DOC</a></td>
            </tr>
            <tr>
                <td>4</td>
                <td>Moda y Estilo Textil S.A. de C.V.</td>
                <td>Prototipos y muestrario línea chaquetas oversize.</td>
                <td>
                    <a href="<?= base_url('detalle_pedido/4') ?>" class="estatus estatus-rojo">
                        0%
                    </a>
                </td>
                <td>
                    <a href="<?= base_url('editarpedido/4') ?>" class="btn btn-warning btn-sm btn-accion">✏️</a>
                    <button class="btn btn-danger btn-sm btn-accion" onclick="eliminarPedido(4)">🗑️</button>
                </td>
                <td><a href="#" class="btn btn-outline-light btn-sm">📄 DOC</a></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

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
            }
        });
    });

    function eliminarPedido(id) {
        if (confirm("¿Seguro que deseas eliminar el pedido " + id + "?")) {
            alert("Pedido " + id + " eliminado (aquí iría la lógica real).");
            // Aquí puedes hacer una llamada AJAX a tu backend en CodeIgniter
        }
    }
</script>
</body>
</html>