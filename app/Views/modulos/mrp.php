<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center mb-4">
    <h1 class="me-3">MRP</h1>
    <span class="badge bg-primary">Planificación de Materiales</span>
</div>

<div class="row g-3">
    <!-- Cálculo de necesidades -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Cálculo automático de necesidades</strong></div>
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Orden de Cliente/Producción</label>
                        <input class="form-control" placeholder="OC-2025-0012">
                    </div>
                    <div class="col-12">
                        <label class="form-label">BOM</label>
                        <select class="form-select">
                            <option>BOM-TSHIRT-001</option>
                            <option>BOM-HOODIE-042</option>
                            <option>BOM-PANTS-317</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="button">Calcular</button>
                        <button class="btn btn-outline-primary" type="button">Importar BOM</button>
                    </div>
                </form>
                <hr>
                <?php $reqs = $reqs ?? [
                    ['mat'=>'Tela Algodón 180g','u'=>'m','necesidad'=>1200,'stock'=>450,'comprar'=>750],
                    ['mat'=>'Hilo 40/2','u'=>'rollo','necesidad'=>35,'stock'=>10,'comprar'=>25],
                    ['mat'=>'Etiqueta talla','u'=>'pz','necesidad'=>1000,'stock'=>1200,'comprar'=>0],
                ]; ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-primary"><tr>
                            <th>Material</th><th>U.</th><th>Necesidad</th><th>Stock</th><th>A comprar</th></tr></thead>
                        <tbody>
                        <?php foreach($reqs as $r): ?>
                            <tr>
                                <td><?= esc($r['mat']) ?></td>
                                <td><?= esc($r['u']) ?></td>
                                <td><?= esc($r['necesidad']) ?></td>
                                <td><?= esc($r['stock']) ?></td>
                                <td><strong><?= esc($r['comprar']) ?></strong></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- OC sugeridas -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Órdenes de Compra sugeridas</strong></div>
            <div class="card-body">
                <?php $ocs = $ocs ?? [
                    ['prov'=>'Textiles MX','mat'=>'Tela Algodón 180g','cant'=>750,'u'=>'m','eta'=>'2025-10-02'],
                    ['prov'=>'Hilos del Norte','mat'=>'Hilo 40/2','cant'=>25,'u'=>'rollo','eta'=>'2025-09-30'],
                ]; ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-primary"><tr>
                            <th>Proveedor</th><th>Material</th><th>Cantidad</th><th>ETA</th><th class="text-end">Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach($ocs as $o): ?>
                            <tr>
                                <td><?= esc($o['prov']) ?></td>
                                <td><?= esc($o['mat']) ?></td>
                                <td><?= esc($o['cant']) ?> <?= esc($o['u']) ?></td>
                                <td><?= esc($o['eta']) ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="#">Editar</a>
                                    <button class="btn btn-sm btn-primary" type="button">Generar OC</button>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-success mt-2" type="button">Generar todas</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
