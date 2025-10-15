<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Espaciado y bordes agradables para los botones de exportación */
    .dt-buttons.btn-group .btn{
        margin-right:.5rem;
        border-radius:.375rem !important;
    }
    .dt-buttons.btn-group .btn:last-child{ margin-right:0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">Inspección</h1>
    <span class="badge bg-info">Calidad</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header"><strong>Pedidos para inspección</strong></div>
    <div class="card-body table-responsive">
        <table id="tablaInspeccion" class="table table-striped table-bordered align-middle mb-0 text-center">
            <thead class="table-primary">
            <tr>
                <th style="width:80px">No.</th>
                <th>Empresa</th>
                <th>Descripción</th>
                <th>Estatus</th>
                <th class="text-end" style="width:140px">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($lista) && is_array($lista)): ?>
                <?php foreach ($lista as $r): ?>
                    <tr>
                        <td><?= esc($r['num']) ?></td>
                        <td><?= esc($r['empresa']) ?></td>
                        <td class="text-start"><?= esc($r['descripcion']) ?></td>
                        <td><?= esc($r['estatus']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-info"
                               href="<?= base_url('modulo3/inspeccion/evaluar/'.($r['id'] ?? 0)) ?>">
                                Evaluar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Sin pedidos para inspección.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons + dependencias -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
    $(function(){
        const langES = {
            sProcessing:"Procesando...",
            sLengthMenu:"Mostrar _MENU_ registros",
            sZeroRecords:"No se encontraron resultados",
            sEmptyTable:"Ningún dato disponible en esta tabla",
            sInfo:"Mostrando registros del _START_ al _END_ de _TOTAL_",
            sInfoEmpty:"Mostrando registros del 0 al 0 de un total de 0 registros",
            sInfoFiltered:"(filtrado de un total de _MAX_ registros)",
            sSearch:"Buscar:",
            sInfoThousands:",",
            sLoadingRecords:"Cargando...",
            oPaginate:{ sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" },
            buttons:{ copy:"Copiar" }
        };
        const fecha = new Date().toISOString().slice(0,10);
        const fileName = 'inspeccion_' + fecha;

        $('#tablaInspeccion').DataTable({
            language: langES,
            columnDefs: [
                { targets: -1, orderable: false, searchable: false } // Acciones sin ordenar/buscar
            ],
            dom:
                "<'row mb-2'<'col-12 col-md-6 d-flex align-items-center text-md-start'B><'col-12 col-md-6 text-md-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend:'copy',  text:'Copy',  exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'csv',   text:'CSV',   filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'excel', text:'Excel', filename:fileName, exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'pdf',   text:'PDF',   filename:fileName, title:fileName,
                    orientation:'landscape', pageSize:'A4',
                    exportOptions:{ columns: ':not(:last-child)' } },
                { extend:'print', text:'Print', exportOptions:{ columns: ':not(:last-child)' } }
            ]
        });
    });
</script>
<?= $this->endSection() ?>
