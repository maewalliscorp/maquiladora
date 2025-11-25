<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$embarqueId = $embarque['id']   ?? null;
$folio      = $embarque['folio'] ?? ('#' . ($embarqueId ?? ''));
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">
        Aduanas – Embarque <?= esc($folio) ?>
    </h1>

    <button type="button" class="btn btn-success" id="btnNuevaAduana">
        <i class="bi bi-plus-lg"></i> Agregar Aduana
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <strong>Lista de aduanas</strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaAduanas"
                   class="table table-striped table-bordered align-middle text-center">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Aduana</th>
                    <th>No. pedimento</th>
                    <th>Fracción arancelaria</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($aduanas)): ?>
                    <?php foreach ($aduanas as $a): ?>
                        <tr
                                data-id="<?= esc($a['id']) ?>"
                                data-aduana="<?= esc($a['aduana']) ?>"
                                data-pedimento="<?= esc($a['numeroPedimento']) ?>"
                                data-fraccion="<?= esc($a['fraccionArancelaria']) ?>"
                                data-obs="<?= esc($a['observaciones']) ?>"
                        >
                            <td><?= esc($a['id']) ?></td>
                            <td><?= esc($a['aduana']) ?></td>
                            <td><?= esc($a['numeroPedimento']) ?></td>
                            <td><?= esc($a['fraccionArancelaria']) ?></td>
                            <td><?= esc($a['observaciones']) ?></td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-primary btnEditar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-danger btnEliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Aduana -->
<div class="modal fade" id="modalAduana" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalAduanaLabel">
                    Aduana del embarque
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <form id="formAduana">
                <div class="modal-body">

                    <!-- Campos ocultos -->
                    <input type="hidden" name="id" id="aduanaId">
                    <input type="hidden" name="maquiladoraID"
                           value="<?= esc($maquiladoraID ?? '') ?>">
                    <input type="hidden" name="embarqueId"
                           value="<?= esc($embarqueId) ?>">

                    <div class="mb-3">
                        <label class="form-label">Aduana</label>
                        <input type="text"
                               class="form-control"
                               name="aduana"
                               id="aduanaNombre"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de pedimento</label>
                        <input type="text"
                               class="form-control"
                               name="numeroPedimento"
                               id="numeroPedimento">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fracción arancelaria</label>
                        <input type="text"
                               class="form-control"
                               name="fraccionArancelaria"
                               id="fraccionArancelaria">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control"
                                  name="observaciones"
                                  id="observaciones"
                                  rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Guardar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function () {
        // DataTable
        $('#tablaAduanas').DataTable();

        const guardarUrl  = '<?= site_url('logistica/embarque/aduanas/guardar') ?>';
        const eliminarUrl = '<?= site_url('logistica/embarque/aduanas/eliminar') ?>';

        const modalEl  = document.getElementById('modalAduana');
        const modalObj = new bootstrap.Modal(modalEl);

        // Nuevo registro
        $('#btnNuevaAduana').on('click', function () {
            $('#formAduana')[0].reset();
            $('#aduanaId').val('');
            modalObj.show();
        });

        // Editar registro
        $('#tablaAduanas').on('click', '.btnEditar', function () {
            const tr = $(this).closest('tr');
            $('#aduanaId').val(tr.data('id'));
            $('#aduanaNombre').val(tr.data('aduana'));
            $('#numeroPedimento').val(tr.data('pedimento'));
            $('#fraccionArancelaria').val(tr.data('fraccion'));
            $('#observaciones').val(tr.data('obs'));
            modalObj.show();
        });

        // Guardar (crear/editar)
        $('#formAduana').on('submit', function (e) {
            e.preventDefault();

            $.post(guardarUrl, $(this).serialize())
                .done(function (resp) {
                    // Se espera que el controlador devuelva JSON: {status: 'ok', message: '...'}
                    if (resp.status === 'ok') {
                        modalObj.hide();

                        Swal.fire({
                            title: "Guardado",
                            text: resp.message || "La aduana se guardó correctamente.",
                            icon: "success",
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "Aceptar"
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: resp.message || "Error al guardar la aduana.",
                            icon: "error",
                            confirmButtonColor: "#3085d6"
                        });
                        console.error(resp);
                    }
                })
                .fail(function (xhr) {
                    let msg = "Error al guardar la aduana.";
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (json.message) msg = json.message;
                    } catch (e) {}

                    Swal.fire({
                        title: "Error",
                        text: msg,
                        icon: "error",
                        confirmButtonColor: "#3085d6"
                    });
                    console.error(xhr.responseText);
                });
        });

        // Eliminar
        $('#tablaAduanas').on('click', '.btnEliminar', function () {
            const id = $(this).closest('tr').data('id');

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.post(eliminarUrl, {id: id})
                    .done(function (resp) {
                        if (resp.status === 'ok') {
                            Swal.fire({
                                title: "Eliminada",
                                text: resp.message || "La aduana ha sido eliminada.",
                                icon: "success",
                                confirmButtonColor: "#3085d6"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "No se pudo eliminar",
                                text: resp.message || "Ocurrió un problema al eliminar la aduana.",
                                icon: "error",
                                confirmButtonColor: "#3085d6"
                            });
                            console.error(resp);
                        }
                    })
                    .fail(function (xhr) {
                        let msg = "No se pudo eliminar la aduana.";
                        try {
                            const json = JSON.parse(xhr.responseText);
                            if (json.message) msg = json.message;
                        } catch (e) {}

                        Swal.fire({
                            title: "Error",
                            text: msg,
                            icon: "error",
                            confirmButtonColor: "#3085d6"
                        });
                        console.error(xhr.responseText);
                    });
            });
        });
    });
</script>
<?= $this->endSection() ?>
