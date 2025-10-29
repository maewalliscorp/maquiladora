<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="theme-color" content="#ffffff">

    <title><?= esc($title ?? 'Maquiladora') ?></title>

    <!-- CSS: Bootstrap, Icons, Animate, DataTables (si usas tablas) y estilos del proyecto -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('css/maquila.css')) ?>" rel="stylesheet">

    <style>
        /* Estilos mínimos en línea */
        .hover-color:hover {
            color: #0d6efd !important;
        }

        /* Mejora visual del toggler para que se vea en temas claros */
        .navbar-custom .navbar-toggler {
            border: none;
        }
    </style>

    <?= $this->renderSection('head') ?>
</head>
<body class="bg-light">
<?php
// --- Defaults y cálculos de visibilidad (una sola vez) ---
$notifCount = $notifCount ?? 0;

$secGestion = can('menu.catalogo_disenos') || can('menu.pedidos') || can('menu.ordenes') || can('menu.produccion') || can('menu.ordenes_clientes');
$secMuestrasInspeccion = can('menu.muestras') || can('menu.inspeccion');
$secIncidencias = can('menu.incidencias') || can('menu.wip');
$secPlanificacion = can('menu.planificacion_materiales') || can('menu.desperdicios');
$secMantenimiento = can('menu.inv_maquinas') || can('menu.mant_correctivo');
$secLogistica = can('menu.logistica_preparacion') || can('menu.logistica_gestion') || can('menu.logistica_documentos') || can('menu.inventario_almacen');
$secAdmin = can('menu.reportes') || can('menu.roles') || can('menu.usuarios');
?>
<nav class="navbar navbar-expand-lg navbar-custom px-3" role="navigation" aria-label="Menú principal">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= esc(base_url('modulo3/dashboard')) ?>">
            <img src="<?= esc(base_url('img/logo_Maquiladora.png')) ?>" alt="Logo" width="32" class="me-1 d-lg-none">
            <img src="<?= esc(base_url('img/logo_Maquiladora.png')) ?>" alt="Logo" width="48"
                 class="me-2 d-none d-lg-block">
            <span class="fw-bold text-dark d-none d-md-inline">Sistema de Maquiladora</span>
        </a>

        <!-- Usuario y notificaciones (móvil) -->
        <div class="d-flex align-items-center d-lg-none">
            <a class="nav-link d-flex align-items-center text-dark text-decoration-none hover-color ms-lg-2"
               href="#" id="userMenuMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false"
               aria-label="Usuario">
                <i class="fa-solid fa-user-circle me-2 fs-5" aria-hidden="true"></i>
                <?= esc(session()->get('user_name') ?? 'Usuario') ?>
            </a>

            <a class="nav-link position-relative text-dark hover-color ms-lg-2"
               href="<?= esc(base_url('modulo3/notificaciones')) ?>" aria-label="Notificaciones">
                <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                <?php if ($notifCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= esc($notifCount) ?>
                        <span class="visually-hidden">notificaciones</span>
                    </span>
                <?php endif; ?>
            </a>

            <ul class="dropdown-menu shadow border-0 animate__animated animate__fadeIn"
                aria-labelledby="userMenuMobile">
                <li>
                    <a class="dropdown-item d-flex align-items-center"
                       href="<?= esc(base_url('modulo1/perfilempleado')) ?>">
                        <i class="fa-solid fa-id-badge me-2 text-primary" aria-hidden="true"></i> Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center text-danger"
                       href="<?= esc(base_url('logout')) ?>">
                        <i class="fa-solid fa-right-from-bracket me-2" aria-hidden="true"></i> Cerrar sesión
                    </a>
                </li>
            </ul>

            <!-- Botón toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav"
                    aria-controls="topnav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="topnav">
            <!-- Menú móvil (colapsado) -->
            <ul class="navbar-nav d-lg-none">
                <li class="nav-item">
                    <a class="nav-link position-relative text-dark ms-lg-2"
                       href="<?= esc(base_url('modulo3/notificaciones')) ?>">
                        <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= esc($notifCount) ?>
                            </span>
                        <?php endif; ?>
                        Notificaciones
                    </a>
                </li>

                <?php if (can('menu.catalogo_disenos')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo2/catalogodisenos')) ?>"><i
                                    class="bi bi-brush me-2"></i>Catálogo de Diseños</a></li>
                <?php endif; ?>
                <?php if (can('menu.pedidos')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('modulo1/pedidos')) ?>"><i
                                    class="bi bi-bag me-2"></i>Pedidos</a></li>
                <?php endif; ?>
                <?php if (can('menu.ordenes')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('modulo1/ordenes')) ?>"><i
                                    class="bi bi-card-checklist me-2"></i>Órdenes en proceso</a></li>
                <?php endif; ?>
                <?php if (can('menu.produccion')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo1/produccion')) ?>"><i
                                    class="bi bi-gear-wide-connected me-2"></i>Producción</a></li>
                <?php endif; ?>
                <?php if (can('menu.ordenes_clientes')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('clientes')) ?>"><i
                                    class="bi bi-people me-2"></i>Clientes</a></li>
                <?php endif; ?>

                <?php if ($secGestion && $secMuestrasInspeccion): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li><?php endif; ?>

                <?php if (can('menu.muestras')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('muestras')) ?>"><i
                                class="bi bi-palette2 me-2"></i>Muestras</a></li><?php endif; ?>
                <?php if (can('menu.inspeccion')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/inspeccion')) ?>"><i
                                class="bi bi-search me-2"></i>Inspección</a></li><?php endif; ?>

                <?php if ($secMuestrasInspeccion && $secIncidencias): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li><?php endif; ?>

                <?php if (can('menu.incidencias')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/incidencias')) ?>"><i
                                class="bi bi-exclamation-triangle me-2"></i>Incidencias</a></li><?php endif; ?>

                <?php if ($secIncidencias && $secPlanificacion): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li><?php endif; ?>

                <?php if (can('menu.planificacion_materiales')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('modulo3/mrp')) ?>"><i
                                class="bi bi-diagram-2 me-2"></i>Planificación Materiales</a></li><?php endif; ?>
                <?php if (can('menu.desperdicios')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/desperdicios')) ?>"><i
                                class="bi bi-recycle me-2"></i>Desperdicios</a></li><?php endif; ?>

                <?php if ($secPlanificacion && $secMantenimiento): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li><?php endif; ?>

                <?php if (can('menu.inv_maquinas')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/mantenimiento_inventario')) ?>"><i
                                class="bi bi-tools me-2"></i>Inventario Maq.</a></li><?php endif; ?>
                <?php if (can('menu.mant_correctivo')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/mantenimiento_correctivo')) ?>"><i
                                class="bi bi-wrench-adjustable-circle me-2"></i>Correctivo</a></li><?php endif; ?>

                <?php if ($secMantenimiento && $secLogistica): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li><?php endif; ?>

                <?php if (can('menu.logistica_preparacion')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/logistica_preparacion')) ?>"><i
                                class="bi bi-box-seam me-2"></i>Prep. Envíos</a></li><?php endif; ?>
                <?php if (can('menu.logistica_gestion')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/logistica_gestion')) ?>"><i
                                class="bi bi-truck me-2"></i>Gestión</a></li><?php endif; ?>
                <?php if (can('menu.logistica_documentos')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/logistica_documentos')) ?>"><i
                                class="bi bi-file-text me-2"></i>Documentos</a></li><?php endif; ?>
                <?php if (can('menu.inventario_almacen')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('almacen/inventario')) ?>"><i
                                class="bi bi-boxes me-2"></i>Inventario Almacén</a></li><?php endif; ?>

                <?php if ($secLogistica && $secAdmin): ?>
                    <li>
                        <hr class="dropdown-divider">
                    </li><?php endif; ?>

                <?php if (can('menu.reportes')): ?>
                    <li class="nav-item"><a class="nav-link text-dark"
                                            href="<?= esc(base_url('modulo3/reportes')) ?>"><i
                                class="bi bi-bar-chart-line me-2"></i>Reportes</a></li><?php endif; ?>
                <?php if (can('menu.roles')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('modulo11/roles')) ?>"><i
                                class="bi bi-person-gear me-2"></i>Roles</a></li><?php endif; ?>
                <?php if (can('menu.usuarios')): ?>
                    <li class="nav-item"><a class="nav-link text-dark" href="<?= esc(base_url('modulo11/usuarios')) ?>"><i
                                class="bi bi-shield-lock me-2"></i>Gestión Usuarios</a></li><?php endif; ?>
            </ul>

            <!-- Menú escritorio -->
            <ul class="navbar-nav ms-auto d-none d-lg-flex align-items-lg-center">
                <!-- Usuario -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link d-flex align-items-center text-dark text-decoration-none position-relative hover-color"
                       href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       title="Menú de usuario">
                        <i class="fa-solid fa-user-circle me-2 fs-5" aria-hidden="true"></i>
                        <span class="d-none d-lg-inline fw-medium"><?= esc(session()->get('user_name') ?? 'Usuario') ?></span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 animate__animated animate__fadeIn"
                        aria-labelledby="userMenu">
                        <li><a class="dropdown-item d-flex align-items-center"
                               href="<?= esc(base_url('modulo1/perfilempleado')) ?>"><i
                                        class="fa-solid fa-id-badge me-2 text-primary"></i> Perfil</a></li>
                        <li><a class="dropdown-item d-flex align-items-center text-danger"
                               href="<?= esc(base_url('logout')) ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>
                                Cerrar sesión</a></li>
                    </ul>
                </li>

                <!-- Notificaciones -->
                <li class="nav-item ms-lg-2">
                    <a class="nav-link text-dark position-relative d-inline-block hover-color"
                       href="<?= esc(base_url('modulo3/notificaciones')) ?>" title="Notificaciones"
                       data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Notificaciones">
                        <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= esc($notifCount) ?>
                            <span class="visually-hidden">notificaciones</span>
                        </span>
                    </a>
                </li>

                <!-- Accesos rápidos -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link text-dark d-flex align-items-center hover-color" href="#" id="quickMenu"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Accesos rápidos">
                        <i class="fa fa-bars fs-5" aria-hidden="true"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="quickMenu"
                         style="min-width: 280px;">
                        <?php if (can('menu.ordenes_clientes')): ?><a class="dropdown-item"
                                                                      href="<?= esc(base_url('clientes')) ?>"><i
                                        class="bi bi-people me-2"></i>Clientes</a><?php endif; ?>
                        <?php if (can('menu.catalogo_disenos')): ?><a class="dropdown-item"
                                                                      href="<?= esc(base_url('modulo2/catalogodisenos')) ?>">
                                <i class="bi bi-brush me-2"></i>Catálogo de Diseños</a><?php endif; ?>
                        <?php if (can('menu.pedidos')): ?><a class="dropdown-item"
                                                             href="<?= esc(base_url('modulo1/pedidos')) ?>"><i
                                        class="bi bi-bag me-2"></i>Pedidos</a><?php endif; ?>
                        <?php if (can('menu.ordenes')): ?><a class="dropdown-item"
                                                             href="<?= esc(base_url('modulo1/ordenes')) ?>"><i
                                        class="bi bi-card-checklist me-2"></i>Órdenes en proceso</a><?php endif; ?>
                        <?php if (can('menu.produccion')): ?><a class="dropdown-item"
                                                                href="<?= esc(base_url('modulo1/produccion')) ?>"><i
                                        class="bi bi-gear-wide-connected me-2"></i>Producción</a><?php endif; ?>
                        <?php if ($secGestion && $secMuestrasInspeccion): ?>
                            <div class="dropdown-divider"></div><?php endif; ?>

                        <?php if (can('menu.muestras')): ?><a class="dropdown-item"
                                                              href="<?= esc(base_url('muestras')) ?>"><i
                                        class="bi bi-palette2 me-2"></i>Muestras</a><?php endif; ?>
                        <?php if (can('menu.inspeccion')): ?><a class="dropdown-item"
                                                                href="<?= esc(base_url('modulo3/inspeccion')) ?>"><i
                                        class="bi bi-search me-2"></i>Inspección</a><?php endif; ?>

                        <?php if ($secMuestrasInspeccion && $secIncidencias): ?>
                            <div class="dropdown-divider"></div><?php endif; ?>

                        <?php if (can('menu.incidencias')): ?><a class="dropdown-item"
                                                                 href="<?= esc(base_url('modulo3/incidencias')) ?>"><i
                                        class="bi bi-exclamation-triangle me-2"></i>Incidencias</a><?php endif; ?>

                        <?php if ($secIncidencias && $secPlanificacion): ?>
                            <div class="dropdown-divider"></div><?php endif; ?>

                        <?php if (can('menu.planificacion_materiales')): ?><a class="dropdown-item"
                                                                              href="<?= esc(base_url('modulo3/mrp')) ?>">
                                <i class="bi bi-diagram-2 me-2"></i>Planificación materiales</a><?php endif; ?>
                        <?php if (can('menu.desperdicios')): ?><a class="dropdown-item"
                                                                  href="<?= esc(base_url('modulo3/desperdicios')) ?>"><i
                                        class="bi bi-recycle me-2"></i>Desperdicios</a><?php endif; ?>

                        <?php if ($secPlanificacion && $secMantenimiento): ?>
                            <div class="dropdown-divider"></div><?php endif; ?>

                        <?php if (can('menu.inv_maquinas')): ?><a class="dropdown-item"
                                                                  href="<?= esc(base_url('modulo3/mantenimiento_inventario')) ?>">
                                <i class="bi bi-tools me-2"></i>Inventario Maq.</a><?php endif; ?>
                        <?php if (can('menu.mant_correctivo')): ?><a class="dropdown-item"
                                                                     href="<?= esc(base_url('modulo3/mantenimiento_correctivo')) ?>">
                                <i class="bi bi-wrench-adjustable-circle me-2"></i>Mant. Correctivo</a><?php endif; ?>

                        <?php if ($secMantenimiento && $secLogistica): ?>
                            <div class="dropdown-divider"></div><?php endif; ?>

                        <?php if (can('menu.logistica_preparacion')): ?><a class="dropdown-item"
                                                                           href="<?= esc(base_url('modulo3/logistica_preparacion')) ?>">
                                <i class="bi bi-box-seam me-2"></i>Prep. Envíos</a><?php endif; ?>
                        <?php if (can('menu.logistica_gestion')): ?><a class="dropdown-item"
                                                                       href="<?= esc(base_url('modulo3/logistica_gestion')) ?>">
                                <i class="bi bi-truck me-2"></i>Gestión Envíos</a><?php endif; ?>
                        <?php if (can('menu.logistica_documentos')): ?><a class="dropdown-item"
                                                                          href="<?= esc(base_url('modulo3/logistica_documentos')) ?>">
                                <i class="bi bi-file-earmark-text me-2"></i>Docs. Embarque</a><?php endif; ?>
                        <?php if (can('menu.inventario_almacen')): ?><a class="dropdown-item"
                                                                        href="<?= esc(base_url('almacen/inventario')) ?>">
                                <i class="bi bi-boxes me-2"></i>Inventario Almacén</a><?php endif; ?>

                        <?php if ($secLogistica && $secAdmin): ?>
                            <div class="dropdown-divider"></div><?php endif; ?>

                        <?php if (can('menu.reportes')): ?><a class="dropdown-item"
                                                              href="<?= esc(base_url('modulo3/reportes')) ?>"><i
                                        class="bi bi-bar-chart-line me-2"></i>Reportes</a><?php endif; ?>
                        <?php if (can('menu.roles')): ?><a class="dropdown-item"
                                                           href="<?= esc(base_url('modulo11/roles')) ?>"><i
                                        class="bi bi-person-gear me-2"></i>Roles</a><?php endif; ?>
                        <?php if (can('menu.usuarios')): ?><a class="dropdown-item"
                                                              href="<?= esc(base_url('modulo11/usuarios')) ?>"><i
                                        class="bi bi-shield-lock me-2"></i>Gestión Usuarios</a><?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?= $this->renderSection('content') ?>
</main>

<footer class="border-top py-3 bg-white">
    <div class="container small text-muted">© <?= date('Y') ?> Maquiladora</div>
</footer>

<!-- Scripts: cargados al final para mejor performance -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" defer></script>

<script>
    // Ejecutar cuando el script de bootstrap ya esté disponible
    (function () {
        // Mejora: espera DOMContentLoaded para manipular DOM y usa replaceChild para envolver tablas
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar tooltips si bootstrap existe
            try {
                if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                        new bootstrap.Tooltip(el);
                    });
                }
            } catch (err) {
                console.warn('Error inicializando tooltips', err);
            }

            // Abrir tablas responsivas automáticamente y añadir clases útiles de bootstrap
            document.querySelectorAll('table').forEach(tbl => {
                tbl.classList.add('table', 'table-striped', 'table-hover', 'align-middle');

                const parent = tbl.parentElement;
                if (!parent) return;

                // No envolver si ya está dentro de un .table-responsive
                if (parent.classList && parent.classList.contains('table-responsive')) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';

                // Reemplaza la tabla por el wrapper y añade la tabla dentro del wrapper
                parent.replaceChild(wrapper, tbl);
                wrapper.appendChild(tbl);
            });
        });

        // Forzar recarga si se vuelve desde bfcache para validar sesión
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                location.reload();
            }
        });

        // Listener 'unload' vacío para ayudar en ciertos navegadores con caches agresivos
        window.addEventListener('unload', function () {
        });
    })();
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
