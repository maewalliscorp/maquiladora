<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<style>
    .editor-container {
        font-size: 0.85rem;
    }

    .table-editor input {
        border: none;
        background: transparent;
        width: 100%;
        padding: 2px 5px;
        text-align: center;
    }

    .table-editor input:focus {
        background: #fff;
        outline: 2px solid #86b7fe;
    }

    .table-editor td {
        padding: 0 !important;
        vertical-align: middle;
    }

    .table-editor th {
        vertical-align: middle;
        text-align: center;
        font-size: 0.8rem;
    }

    .header-input {
        font-weight: bold;
        border: none;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0;
    }

    .bg-readonly {
        background-color: #f8f9fa;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid editor-container mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Hoja de Corte</h4>
            <small
                class="text-muted"><?= empty($corte['id']) ? 'Nuevo Corte' : 'Editando Corte: ' . esc($corte['numero_corte']) ?></small>
        </div>
        <div>
            <a href="<?= base_url('modulo3/cortes') ?>" class="btn btn-secondary me-2">Cancelar</a>
            <button class="btn btn-primary" id="btnGuardar">
                <i class="fas fa-save me-1"></i> Guardar Corte
            </button>
        </div>
    </div>

    <form id="formCorte">
        <input type="hidden" name="id" value="<?= $corte['id'] ?? '' ?>">

        <!-- Header Info -->
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Fila 1 -->
                    <div class="col-md-2">
                        <label class="small text-muted">No. Corte</label>
                        <input type="text" class="form-control header-input" name="numero_corte"
                            value="<?= $corte['numero_corte'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Estilo</label>
                        <input type="text" class="form-control header-input" name="estilo"
                            value="<?= $corte['estilo'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Prenda</label>
                        <input type="text" class="form-control header-input" name="prenda"
                            value="<?= $corte['prenda'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Cliente</label>
                        <input type="text" class="form-control header-input" name="cliente"
                            value="<?= $corte['cliente'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Color</label>
                        <input type="text" class="form-control header-input" name="color"
                            value="<?= $corte['color'] ?? '' ?>">
                    </div>

                    <!-- Fila 2 -->
                    <div class="col-md-2">
                        <label class="small text-muted">Precio</label>
                        <input type="number" class="form-control header-input" name="precio" step="0.01"
                            value="<?= $corte['precio'] ?? '0.00' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Fecha Entrada</label>
                        <input type="date" class="form-control header-input" name="fecha_entrada"
                            value="<?= $corte['fecha_entrada'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Fecha Embarque</label>
                        <input type="date" class="form-control header-input" name="fecha_embarque"
                            value="<?= $corte['fecha_embarque'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Cortador</label>
                        <input type="text" class="form-control header-input" name="cortador"
                            value="<?= $corte['cortador'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted">Tendedor</label>
                        <input type="text" class="form-control header-input" name="tendedor"
                            value="<?= $corte['tendedor'] ?? '' ?>">
                    </div>

                    <!-- Fila 3 -->
                    <div class="col-md-3">
                        <label class="small text-muted">Tela</label>
                        <input type="text" class="form-control header-input" name="tela"
                            value="<?= $corte['tela'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Largo Trazo (m)</label>
                        <input type="number" class="form-control header-input" id="largoTrazo" name="largo_trazo"
                            step="0.0001" value="<?= $corte['largo_trazo'] ?? '0.0000' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted">Ancho Tela (m)</label>
                        <input type="number" class="form-control header-input" name="ancho_tela" step="0.01"
                            value="<?= $corte['ancho_tela'] ?? '0.00' ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tallas Config -->
        <div class="card mb-3 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <span class="me-2 fw-bold small">Tallas:</span>
                    <div id="tallasContainer" class="d-flex flex-wrap gap-2">
                        <!-- Tallas badges generated by JS -->
                    </div>
                    <div class="input-group input-group-sm ms-3" style="width: 150px;">
                        <input type="text" class="form-control" id="newTalla" placeholder="Nueva Talla">
                        <button class="btn btn-outline-secondary" type="button" id="btnAddTalla">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-editor mb-0" id="gridCorte">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" style="width: 40px;">No.</th>
                                <th rowspan="2">Lote</th>
                                <th rowspan="2">Color</th>
                                <th colspan="2">Cantidad en Rollos</th>
                                <th rowspan="2" style="width: 60px;">Mts Usados</th>
                                <th colspan="3">Mermas</th>
                                <th rowspan="2" style="width: 60px;">Tela Sobrante</th>
                                <th rowspan="2" style="width: 60px;">Diferencia</th>
                                <th rowspan="2" style="width: 50px;">Lienzos</th>
                                <th id="thTallas" colspan="1">Tallas</th> <!-- Colspan dynamic -->
                                <th rowspan="2" style="width: 60px;">Total</th>
                                <th rowspan="2" style="width: 30px;"></th>
                            </tr>
                            <tr id="trTallasSubheader">
                                <th style="width: 60px;">KG</th>
                                <th style="width: 60px;">MTS</th>
                                <th style="width: 50px;" title="Dañada">Dañ</th>
                                <th style="width: 50px;" title="Faltante">Fal</th>
                                <th style="width: 50px;" title="Desperdicio">Des</th>
                                <!-- Dynamic Tallas Headers -->
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRollos">
                            <!-- Rows loaded via JS -->
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr id="trTotales">
                                <td colspan="3" class="text-end pe-2">Totales:</td>
                                <td id="totalKg" class="text-center">0.00</td>
                                <td id="totalMts" class="text-center">0.00</td>
                                <td id="totalUsados" class="text-center">0.00</td>
                                <td id="totalMermaD" class="text-center">0.00</td>
                                <td id="totalMermaF" class="text-center">0.00</td>
                                <td id="totalMermaDes" class="text-center">0.00</td>
                                <td id="totalSobrante" class="text-center">0.00</td>
                                <td id="totalDiferencia" class="text-center">0.00</td>
                                <td id="totalLienzos" class="text-center">0</td>
                                <!-- Dynamic Tallas Totals -->
                                <td class="talla-total-placeholder">-</td>
                                <td id="grandTotalPrendas" class="text-center">0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button type="button" class="btn btn-sm btn-outline-success" id="btnAddRollo">
                    <i class="fas fa-plus"></i> Agregar Rollo
                </button>
            </div>
        </div>

        <!-- Summary Metrics -->
        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-sm table-bordered">
                    <tr>
                        <td class="bg-light">Total Tela Usada (m):</td>
                        <td class="text-end fw-bold" id="summaryTelaUsada">0.00</td>
                    </tr>
                    <tr>
                        <td class="bg-light">Total Prendas:</td>
                        <td class="text-end fw-bold" id="summaryPrendas">0</td>
                    </tr>
                    <tr>
                        <td class="bg-light">Consumo Promedio (m/prenda):</td>
                        <td class="text-end fw-bold" id="summaryConsumo">0.0000</td>
                    </tr>
                </table>
            </div>
        </div>

    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Estado inicial
    let tallas = [];
    // Datos cargados desde PHP si es edición
    const detallesIniciales = <?= isset($detalles) ? json_encode($detalles) : '[]' ?>;

    // Extraer tallas de los detalles si existen
    if (detallesIniciales.length > 0 && detallesIniciales[0].tallas) {
        detallesIniciales[0].tallas.forEach(t => tallas.push(t.talla));
    } else {
        // Tallas por defecto si es nuevo
        tallas = ['S', 'M', 'L', 'XL'];
    }

    $(document).ready(function () {
        renderTallasHeader();

        if (detallesIniciales.length > 0) {
            detallesIniciales.forEach(d => addRollo(d));
        } else {
            for (let i = 0; i < 5; i++) addRollo();
        }

        // Eventos Tallas
        $('#btnAddTalla').click(addTalla);
        $('#newTalla').keypress(function (e) {
            if (e.which == 13) { e.preventDefault(); addTalla(); }
        });

        // Eventos Grid
        $('#btnAddRollo').click(() => addRollo());
        $('#gridCorte').on('click', '.btn-remove', function () {
            $(this).closest('tr').remove();
            renumerarRollos();
            calcularTotales();
        });

        // Cálculos
        $('#gridCorte').on('input', 'input', function () {
            const row = $(this).closest('tr');
            calcularFila(row);
            calcularTotales();
        });

        $('#largoTrazo').on('input', function () {
            recalcularTodo();
        });

        // Guardar
        $('#btnGuardar').click(guardarCorte);
    });

    function addTalla() {
        const talla = $('#newTalla').val().trim().toUpperCase();
        if (talla && !tallas.includes(talla)) {
            tallas.push(talla);
            $('#newTalla').val('');
            renderTallasHeader();
            // Agregar columna a filas existentes
            $('#tbodyRollos tr').each(function () {
                const td = `<td class="p-0"><input type="number" class="talla-qty" data-talla="${talla}" value="0"></td>`;
                $(this).find('.td-total').before(td);
            });
            calcularTotales();
        }
    }

    function removeTalla(talla) {
        tallas = tallas.filter(t => t !== talla);
        renderTallasHeader();
        // Remover columnas (esto es destructivo, recargamos la tabla mejor o manipulamos DOM complejo)
        // Por simplicidad, en este MVP, al quitar talla, reconstruimos la tabla (perdiendo inputs no guardados si no tenemos cuidado)
        // Mejor estrategia: ocultar o eliminar por índice.
        // Simplificación: Recargar página o advertir. 
        // Implementación rápida: Eliminar columna por índice es difícil sin redibujar.
        // Vamos a redibujar todo preservando datos en memoria.
        const currentData = extractDataFromGrid();
        $('#tbodyRollos').empty();
        currentData.forEach(d => addRollo(d));
        calcularTotales();
    }

    function renderTallasHeader() {
        // Badges
        const container = $('#tallasContainer');
        container.empty();
        tallas.forEach(t => {
            container.append(`
                <span class="badge bg-info text-dark">
                    ${t} <i class="fas fa-times ms-1" style="cursor:pointer" onclick="removeTalla('${t}')"></i>
                </span>
            `);
        });

        // Table Header
        $('#thTallas').attr('colspan', tallas.length);
        const subheader = $('#trTallasSubheader');
        subheader.find('th:gt(4)').remove(); // Remover anteriores headers de tallas (índice 5 en adelante)

        tallas.forEach(t => {
            subheader.append(`<th>${t}</th>`);
        });

        // Totales Footer
        const trTotales = $('#trTotales');
        trTotales.find('.talla-total').remove();
        trTotales.find('.talla-total-placeholder').remove();

        tallas.forEach(t => {
            $(`<td class="text-center fw-bold talla-total" data-talla="${t}">0</td>`).insertBefore('#grandTotalPrendas');
        });
    }

    function addRollo(data = null) {
        const index = $('#tbodyRollos tr').length + 1;
        let tallasInputs = '';

        tallas.forEach(t => {
            const val = (data && data.tallas) ? (data.tallas.find(x => x.talla === t)?.cantidad || 0) : 0;
            tallasInputs += `<td class="p-0"><input type="number" class="talla-qty" data-talla="${t}" value="${val}"></td>`;
        });

        const row = `
            <tr>
                <td class="text-center row-index">${index}</td>
                <td><input type="text" class="rollo-lote" value="${data?.lote || ''}"></td>
                <td><input type="text" class="rollo-color" value="${data?.color_rollo || ''}"></td>
                <td><input type="number" class="rollo-kg" value="${data?.peso_kg || 0}" step="0.01"></td>
                <td><input type="number" class="rollo-mts" value="${data?.longitud_mts || 0}" step="0.01"></td>
                <td class="bg-readonly"><input type="number" class="rollo-usados" value="${data?.metros_usados || 0}" readonly></td>
                <td><input type="number" class="rollo-merma-d" value="${data?.merma_danada || 0}" step="0.01"></td>
                <td><input type="number" class="rollo-merma-f" value="${data?.merma_faltante || 0}" step="0.01"></td>
                <td><input type="number" class="rollo-merma-des" value="${data?.merma_desperdicio || 0}" step="0.01"></td>
                <td><input type="number" class="rollo-sobrante" value="${data?.tela_sobrante || 0}" step="0.01"></td>
                <td class="bg-readonly"><input type="number" class="rollo-diferencia" value="${data?.diferencia || 0}" readonly></td>
                <td><input type="number" class="rollo-lienzos" value="${data?.cantidad_lienzos || 0}"></td>
                ${tallasInputs}
                <td class="bg-readonly td-total"><input type="number" class="rollo-total-prendas" value="${data?.total_prendas_rollo || 0}" readonly></td>
                <td class="text-center"><i class="fas fa-times text-danger btn-remove" style="cursor:pointer"></i></td>
            </tr>
        `;
        $('#tbodyRollos').append(row);
        if (!data) calcularFila($('#tbodyRollos tr').last());
    }

    function calcularFila(row) {
        const largoTrazo = parseFloat($('#largoTrazo').val()) || 0;
        const lienzos = parseInt(row.find('.rollo-lienzos').val()) || 0;
        const mts = parseFloat(row.find('.rollo-mts').val()) || 0;

        // Mermas
        const md = parseFloat(row.find('.rollo-merma-d').val()) || 0;
        const mf = parseFloat(row.find('.rollo-merma-f').val()) || 0;
        const mdes = parseFloat(row.find('.rollo-merma-des').val()) || 0;
        const sobrante = parseFloat(row.find('.rollo-sobrante').val()) || 0;

        // Cálculos
        const usados = largoTrazo * lienzos;
        row.find('.rollo-usados').val(usados.toFixed(2));

        const diferencia = mts - usados - md - mf - mdes - sobrante;
        row.find('.rollo-diferencia').val(diferencia.toFixed(2));

        // Colorizar diferencia
        const inputDif = row.find('.rollo-diferencia');
        if (Math.abs(diferencia) > 0.1) inputDif.css('color', 'red');
        else inputDif.css('color', 'green');

        // Total Prendas (Suma de tallas)
        let totalPrendas = 0;
        row.find('.talla-qty').each(function () {
            totalPrendas += parseInt($(this).val()) || 0;
        });
        row.find('.rollo-total-prendas').val(totalPrendas);
    }

    function calcularTotales() {
        let tKg = 0, tMts = 0, tUsados = 0, tMd = 0, tMf = 0, tMdes = 0, tSob = 0, tDif = 0, tLienzos = 0, tPrendas = 0;
        const tallasSum = {};
        tallas.forEach(t => tallasSum[t] = 0);

        $('#tbodyRollos tr').each(function () {
            tKg += parseFloat($(this).find('.rollo-kg').val()) || 0;
            tMts += parseFloat($(this).find('.rollo-mts').val()) || 0;
            tUsados += parseFloat($(this).find('.rollo-usados').val()) || 0;
            tMd += parseFloat($(this).find('.rollo-merma-d').val()) || 0;
            tMf += parseFloat($(this).find('.rollo-merma-f').val()) || 0;
            tMdes += parseFloat($(this).find('.rollo-merma-des').val()) || 0;
            tSob += parseFloat($(this).find('.rollo-sobrante').val()) || 0;
            tDif += parseFloat($(this).find('.rollo-diferencia').val()) || 0;
            tLienzos += parseInt($(this).find('.rollo-lienzos').val()) || 0;
            tPrendas += parseInt($(this).find('.rollo-total-prendas').val()) || 0;

            $(this).find('.talla-qty').each(function () {
                const t = $(this).data('talla');
                tallasSum[t] += parseInt($(this).val()) || 0;
            });
        });

        $('#totalKg').text(tKg.toFixed(2));
        $('#totalMts').text(tMts.toFixed(2));
        $('#totalUsados').text(tUsados.toFixed(2));
        $('#totalMermaD').text(tMd.toFixed(2));
        $('#totalMermaF').text(tMf.toFixed(2));
        $('#totalMermaDes').text(tMdes.toFixed(2));
        $('#totalSobrante').text(tSob.toFixed(2));
        $('#totalDiferencia').text(tDif.toFixed(2));
        $('#totalLienzos').text(tLienzos);
        $('#grandTotalPrendas').text(tPrendas);

        // Totales por talla
        for (const [t, sum] of Object.entries(tallasSum)) {
            $(`.talla-total[data-talla="${t}"]`).text(sum);
        }

        // Summary
        $('#summaryTelaUsada').text(tUsados.toFixed(2));
        $('#summaryPrendas').text(tPrendas);
        const consumo = tPrendas > 0 ? (tUsados / tPrendas) : 0;
        $('#summaryConsumo').text(consumo.toFixed(4));
    }

    function recalcularTodo() {
        $('#tbodyRollos tr').each(function () { calcularFila($(this)); });
        calcularTotales();
    }

    function renumerarRollos() {
        $('#tbodyRollos tr').each(function (i) {
            $(this).find('.row-index').text(i + 1);
        });
    }

    function extractDataFromGrid() {
        const data = [];
        $('#tbodyRollos tr').each(function () {
            const row = $(this);
            const tallasRow = [];
            row.find('.talla-qty').each(function () {
                tallasRow.push({
                    talla: $(this).data('talla'),
                    cantidad: parseInt($(this).val()) || 0
                });
            });

            data.push({
                lote: row.find('.rollo-lote').val(),
                color_rollo: row.find('.rollo-color').val(),
                peso_kg: parseFloat(row.find('.rollo-kg').val()) || 0,
                longitud_mts: parseFloat(row.find('.rollo-mts').val()) || 0,
                metros_usados: parseFloat(row.find('.rollo-usados').val()) || 0,
                merma_danada: parseFloat(row.find('.rollo-merma-d').val()) || 0,
                merma_faltante: parseFloat(row.find('.rollo-merma-f').val()) || 0,
                merma_desperdicio: parseFloat(row.find('.rollo-merma-des').val()) || 0,
                tela_sobrante: parseFloat(row.find('.rollo-sobrante').val()) || 0,
                diferencia: parseFloat(row.find('.rollo-diferencia').val()) || 0,
                cantidad_lienzos: parseInt(row.find('.rollo-lienzos').val()) || 0,
                total_prendas_rollo: parseInt(row.find('.rollo-total-prendas').val()) || 0,
                tallas: tallasRow
            });
        });
        return data;
    }

    function guardarCorte() {
        const formData = new FormData(document.getElementById('formCorte'));
        const data = Object.fromEntries(formData.entries());

        // Agregar detalles
        data.detalles = extractDataFromGrid();
        // Agregar número de rollo basado en índice
        data.detalles.forEach((d, i) => d.numero_rollo = i + 1);

        // Agregar totales calculados
        data.total_prendas = parseInt($('#summaryPrendas').text());
        data.total_tela_usada = parseFloat($('#summaryTelaUsada').text());
        data.consumo_promedio = parseFloat($('#summaryConsumo').text());

        $.ajax({
            url: '<?= base_url('modulo3/cortes/guardar') ?>',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.ok) {
                    Swal.fire('Éxito', 'Corte guardado correctamente', 'success')
                        .then(() => window.location.href = '<?= base_url('modulo3/cortes') ?>');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    }
</script>
<?= $this->endSection() ?>