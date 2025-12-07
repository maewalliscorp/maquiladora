<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="theme-color" content="#ffffff">
    <?php if (function_exists('csrf_token')): ?>
        <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <?php endif; ?>

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

        .navbar-custom .navbar-toggler {
            border: none;
        }

        .badge-dot {
            display: inline-block;
            width: .6rem;
            height: .6rem;
            border-radius: 50%;
            margin-right: .35rem
        }

        /* Forzar mismo color y espaciado en todas las opciones del menú móvil */
        .navbar-nav.d-lg-none .nav-link,
        .navbar-nav.d-lg-none .dropdown-item {
            color: #000 !important;
            opacity: 1 !important;
            display: block;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        .navbar-nav.d-lg-none > li + li,
        .navbar-nav.d-lg-none .dropdown-item + .dropdown-item {
            margin-top: 0.1rem;
        }

        /* Mejorar visibilidad de botones e iconos */
        .btn-outline-secondary {
            border-color: #6c757d !important;
            color: #6c757d !important;
            background-color: transparent !important;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        /* Forzar visibilidad de iconos */
        .nav-link i,
        .dropdown-item i,
        .btn i,
        .navbar-toggler i,
        .fa,
        .fas,
        .far,
        .fab,
        .bi {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            font-size: 1.1rem !important;
            width: auto !important;
            height: auto !important;
        }

        /* Iconos específicos del navbar */
        .navbar .nav-link i {
            font-size: 1.2rem !important;
        }

        .navbar .btn i {
            font-size: 1rem !important;
        }

        /* Ajustar espaciado de iconos */
        .navbar .nav-link i.me-2,
        .navbar .dropdown-item i.me-2 {
            margin-right: 0.5rem !important;
        }

        /* Mejorar contraste en modo claro */
        .text-dark i {
            color: #343a40 !important;
        }

        /* Forzar colores consistentes en modo claro */
        .navbar .text-dark i,
        .navbar .nav-link i,
        .navbar .dropdown-item i {
            color: #343a40 !important;
        }

        /* Forzar colores consistentes en modo oscuro */
        body[data-theme="dark"] .navbar .text-dark i,
        body[data-theme="dark"] .navbar .nav-link i,
        body[data-theme="dark"] .navbar .dropdown-item i {
            color: #f8f9fa !important;
        }

        /* Botón toggle de tema específico */
        .js-theme-toggle {
            padding: 0.375rem 0.5rem !important;
            border-radius: 0.375rem !important;
            transition: all 0.2s ease-in-out !important;
            margin-left: 0.5rem !important;
            margin-right: 0.5rem !important;
        }

        .js-theme-toggle:hover {
            transform: scale(1.05) !important;
        }

        /* Iconos de usuario y notificaciones */
        .fa-user-circle,
        .bi-bell {
            font-size: 1.3rem !important;
        }

        /* Navbar toggler icon */
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2833, 37, 41, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        /* Navbar toggler icon en modo oscuro */
        body[data-theme="dark"] .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28248, 249, 250, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        /* Botón toggler en modo oscuro */
        body[data-theme="dark"] .navbar-toggler {
            border-color: rgba(248, 249, 250, 0.3) !important;
        }

        body[data-theme="dark"] .navbar-toggler:hover {
            border-color: rgba(248, 249, 250, 0.6) !important;
        }

        /* Mejorar espaciado en navbar móvil */
        .d-lg-none .nav-item {
            margin-right: 0.25rem;
        }

        .d-lg-none .nav-link {
            padding: 0.5rem !important;
        }

        /* Espaciado entre elementos del navbar */
        .navbar-nav .nav-item {
            margin-left: 0.25rem;
        }

        .navbar-nav .nav-item:first-child {
            margin-left: 0;
        }

        /* Ajustar contenedor de elementos móviles */
        .d-flex.align-items-center.d-lg-none > * {
            margin-right: 0.75rem;
        }

        .d-flex.align-items-center.d-lg-none > *:last-child {
            margin-right: 0;
        }

        /* Mejorar espaciado en menú escritorio */
        .d-none.d-lg-flex .nav-item {
            margin-left: 0.5rem;
        }

        .d-none.d-lg-flex .nav-item:first-child {
            margin-left: 0;
        }

        /* Botones con mejor espaciado */
        .btn-sm {
            padding: 0.4rem 0.6rem !important;
            margin: 0 0.25rem !important;
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

    /* ← AQUI SE AGREGA menu.proveedores */
    $secPlanificacion = can('menu.planificacion_materiales') || can('menu.desperdicios') || can('menu.proveedores');

    $secMantenimiento = can('menu.inv_maquinas') || can('menu.mant_correctivo');
    $secLogistica = can('menu.logistica_preparacion') || can('menu.logistica_gestion') || can('menu.logistica_documentos') || can('menu.inventario_almacen');
    $secAdmin = can('menu.reportes') || can('menu.roles') || can('menu.usuarios');
    ?>
    <nav class="navbar navbar-expand-lg navbar-custom px-3" role="navigation" aria-label="Menú principal">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?= esc(base_url('modulo3/dashboard')) ?>">
                <img src="<?= esc(base_url('img/logo_Maquiladora.png')) ?>" alt="Logo" width="85"
                    class="me-1 d-lg-none">
                <img src="<?= esc(base_url('img/logo_Maquiladora.png')) ?>" alt="Logo" width="85"
                    class="me-2 d-none d-lg-block">
                <span class="fw-bold text-dark d-none d-md-inline">Sistema de Maquiladora</span>
            </a>

            <!-- Usuario y notificaciones (móvil) -->
            <?php if (session()->get('logged_in') || session()->get('user_id')): ?>
                <div class="d-flex align-items-center d-lg-none">
                    <!-- Usuario con dropdown integrado -->
                    <div class="dropdown">
                        <a class="nav-link d-flex align-items-center text-dark text-decoration-none hover-color ms-lg-3"
                            href="#" id="userMenuMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Usuario">
                            <i class="fa-solid fa-user-circle me-2 fs-5" aria-hidden="true"></i>
                            <?= esc(session()->get('user_name') ?? session()->get('username') ?? 'Usuario') ?>
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
                    </div>

                    <!-- ENLACE ACTUALIZADO: Notificaciones -> notificaciones2 -->
                    <a class="nav-link position-relative text-dark ms-lg-2"
                        href="<?= esc(base_url('modulo3/notificaciones2')) ?>" aria-label="Notificaciones">
                        <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= esc($notifCount) ?>
                                <span class="visually-hidden">notificaciones</span>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Toggle modo claro/oscuro (móvil) -->
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1 js-theme-toggle d-lg-none"
                            title="Cambiar tema">
                        <i class="bi bi-moon-fill js-theme-icon"></i>
                    </button>

                    <!-- Botón toggler -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav"
                        aria-controls="topnav" aria-expanded="false" aria-label="Abrir menú">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="collapse navbar-collapse" id="topnav">
                <!-- Menú móvil (colapsado) -->
                <ul class="navbar-nav d-lg-none">
                    <?php if (can('menu.maquiladora')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('maquiladora') ?>">
                                <i class="bi bi-building me-1"></i> Mi Maquiladora
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('menu.catalogo_disenos')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('modulo2/catalogodisenos')) ?>">
                                <i class="bi bi-brush me-2"></i>Catálogo de Diseños
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('menu.pedidos')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('modulo1/pedidos')) ?>">
                                <i class="bi bi-bag me-2"></i>Pedidos
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('menu.pagos')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('modulo1/pagos')) ?>">
                                <i class="bi bi-credit-card me-2"></i>Pagos
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('menu.ordenes_clientes')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('clientes')) ?>">
                                <i class="bi bi-people me-2"></i>Clientes
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('menu.ordenes')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('modulo1/ordenes')) ?>">
                                <i class="bi bi-card-checklist me-2"></i>Órdenes en proceso
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('menu.produccion')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('modulo1/produccion')) ?>">
                                <i class="bi bi-gear-wide-connected me-2"></i>Producción
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('modulo3/cortes')) ?>">
                                <i class="bi bi-scissors me-2"></i>Gestión de Cortes
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($secGestion && $secMuestrasInspeccion): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>

                    <?php if (can('menu.muestras')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= esc(base_url('muestras')) ?>">
                                <i class="bi bi-palette2 me-2"></i>Muestras
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (can('menu.inspeccion')): ?>
                        
                        <a class="nav-link text-dark" href="<?= esc(base_url('modulo3/control-bultos')) ?>">
                            <i class="bi bi-box me-2"></i>Inspección Bultos
                        </a>
                        <a class="nav-link text-dark" href="<?= esc(base_url('modulo3/inspeccion')) ?>">
                            <i class="bi bi-search me-2"></i>Inspección de producto
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.incidencias')): ?>
                        <?php
                        $roleName = current_role_name();
                        $roleNorm = $roleName ? mb_strtolower(trim($roleName)) : '';
                        ?>
                        <?php if ($roleNorm === 'empleado'): ?>
                            <a class="dropdown-item js-open-incidencia-modal" href="#">
                                <i class="bi bi-exclamation-triangle me-2"></i>Incidencias
                            </a>
                        <?php else: ?>
                            <a class="dropdown-item" href="<?= esc(base_url('modulo3/incidencias')) ?>">
                                <i class="bi bi-exclamation-triangle me-2"></i>Incidencias
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($secIncidencias && $secPlanificacion): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>

                    <?php if (can('menu.planificacion_materiales')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/mrp')) ?>">
                            <i class="bi bi-diagram-2 me-2"></i>Planificación materiales
                        </a>
                    <?php endif; ?>

                    <!-- NUEVO: Proveedores (escritorio - accesos rápidos) -->
                    <?php if (can('menu.proveedores')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('proveedores')) ?>">
                            <i class="bi bi-truck-front me-2"></i>Proveedores
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.desperdicios')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/desperdicios')) ?>">
                            <i class="bi bi-recycle me-2"></i>Desperdicios
                        </a>
                    <?php endif; ?>

                    <?php if ($secPlanificacion && $secMantenimiento): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>

                    <?php if (can('menu.inv_maquinas')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/mantenimiento_inventario')) ?>">
                            <i class="bi bi-tools me-2"></i>Inventario Maq.
                        </a>
                    <?php endif; ?>

                    <?php if ($secMantenimiento): ?>
                        <!-- SOLO Calendario Mtto (Prog. Mtto eliminado) -->
                        <a class="dropdown-item" href="<?= esc(base_url('mtto/calendario')) ?>">
                            <i class="bi bi-calendar3 me-2"></i>Calendario Mtto
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.mant_correctivo')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/mantenimiento_correctivo')) ?>">
                            <i class="bi bi-wrench-adjustable-circle me-2"></i>Mant. Correctivo
                        </a>
                    <?php endif; ?>

                    <?php if ($secMantenimiento && $secLogistica): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>

                    <?php if (can('menu.logistica_preparacion')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/logistica_preparacion')) ?>">
                            <i class="bi bi-box-seam me-2"></i>Prep. Envíos
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.logistica_gestion')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/logistica_gestion')) ?>">
                            <i class="bi bi-truck me-2"></i>Gestión Envíos
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.logistica_documentos')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/logistica_documentos')) ?>">
                            <i class="bi bi-file-earmark-text me-2"></i>Docs. Embarque
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.inventario_almacen')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('almacen/inventario')) ?>">
                            <i class="bi bi-boxes me-2"></i>Inventario Almacén
                        </a>
                    <?php endif; ?>

                    <?php if ($secLogistica && $secAdmin): ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>

                    <?php if (can('menu.reportes')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/reportes')) ?>">
                            <i class="bi bi-bar-chart-line me-2"></i>Reportes
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.roles')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo11/roles')) ?>">
                            <i class="bi bi-person-gear me-2"></i>Roles
                        </a>
                    <?php endif; ?>

                    <?php if (can('menu.usuarios')): ?>
                        <a class="dropdown-item" href="<?= esc(base_url('modulo11/usuarios')) ?>">
                            <i class="bi bi-shield-lock me-2"></i>Gestión Usuarios
                        </a>
                    <?php endif; ?>
                </ul>

                <!-- Menú escritorio -->
                <?php if (session()->get('logged_in') || session()->get('user_id')): ?>
                    <ul class="navbar-nav ms-auto d-none d-lg-flex align-items-lg-center">
                        <!-- Usuario -->
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link d-flex align-items-center text-dark text-decoration-none position-relative hover-color"
                                href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                                title="Menú de usuario">
                                <i class="fa-solid fa-user-circle me-2 fs-5" aria-hidden="true"></i>
                                <span class="d-none d-lg-inline fw-medium">
                                    <?= esc(session()->get('user_name') ?? session()->get('username') ?? 'Usuario') ?>
                                </span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 animate__animated animate__fadeIn"
                                aria-labelledby="userMenu">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="<?= esc(base_url('modulo1/perfilempleado')) ?>">
                                        <i class="fa-solid fa-id-badge me-2 text-primary"></i> Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center text-danger"
                                        href="<?= esc(base_url('logout')) ?>">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Notificaciones (ACTUALIZADO) -->
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link text-dark position-relative d-inline-block hover-color"
                                href="<?= esc(base_url('modulo3/notificaciones2')) ?>" title="Notificaciones"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Notificaciones">
                                <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= esc($notifCount) ?>
                                    <span class="visually-hidden">notificaciones</span>
                                </span>
                            </a>
                        </li>

                        <!-- Toggle modo claro/oscuro (escritorio) -->
                        <li class="nav-item ms-lg-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center js-theme-toggle"
                                    title="Cambiar tema">
                                <i class="bi bi-moon-fill js-theme-icon"></i>
                            </button>
                        </li>

                        <!-- Accesos rápidos -->
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link text-dark d-flex align-items-center hover-color" href="#" id="quickMenu"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Accesos rápidos">
                                <i class="fa fa-bars fs-5" aria-hidden="true"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="quickMenu"
                                style="min-width: 280px;">
                                <?php if (can('menu.maquiladora')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('maquiladora')) ?>">
                                        <i class="bi bi-building me-1"></i> Mi Maquiladora
                                    </a>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <?php if (can('menu.ordenes_clientes')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('clientes')) ?>">
                                        <i class="bi bi-people me-2"></i>Clientes
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.catalogo_disenos')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo2/catalogodisenos')) ?>">
                                        <i class="bi bi-brush me-2"></i>Catálogo de Diseños
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.pedidos')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo1/pedidos')) ?>">
                                        <i class="bi bi-bag me-2"></i>Pedidos
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.pagos')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo1/pagos')) ?>">
                                        <i class="bi bi-credit-card me-2"></i>Pagos
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.ordenes')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo1/ordenes')) ?>">
                                        <i class="bi bi-card-checklist me-2"></i>Órdenes en proceso
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.produccion')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo1/produccion')) ?>">
                                        <i class="bi bi-gear-wide-connected me-2"></i>Producción
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.produccion')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/cortes')) ?>">
                                        <i class="bi bi-scissors me-2"></i>Gestión de Cortes
                                    </a>
                                <?php endif; ?>

                                <?php if ($secGestion && $secMuestrasInspeccion): ?>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <?php if (can('menu.muestras')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('muestras')) ?>">
                                        <i class="bi bi-palette2 me-2"></i>Muestras
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.inspeccion')): ?>
                                    
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/control-bultos')) ?>">
                                        <i class="bi bi-box me-2"></i>Inspección Bultos
                                    </a>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/inspeccion')) ?>">
                                        <i class="bi bi-search me-2"></i>Inspección de producto
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.incidencias')): ?>
                                    <?php
                                    $roleName = current_role_name();
                                    $roleNorm = $roleName ? mb_strtolower(trim($roleName)) : '';
                                    ?>
                                    <?php if ($roleNorm === 'empleado'): ?>
                                        <a class="dropdown-item js-open-incidencia-modal" href="#">
                                            <i class="bi bi-exclamation-triangle me-2"></i>Incidencias
                                        </a>
                                    <?php else: ?>
                                        <a class="dropdown-item" href="<?= esc(base_url('modulo3/incidencias')) ?>">
                                            <i class="bi bi-exclamation-triangle me-2"></i>Incidencias
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($secIncidencias && $secPlanificacion): ?>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <?php if (can('menu.planificacion_materiales')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/mrp')) ?>">
                                        <i class="bi bi-diagram-2 me-2"></i>Planificación materiales
                                    </a>
                                <?php endif; ?>

                                <!-- NUEVO: Proveedores (escritorio - accesos rápidos) -->
                                <?php if (can('menu.proveedores')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('proveedores')) ?>">
                                        <i class="bi bi-truck-front me-2"></i>Proveedores
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.desperdicios')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/desperdicios')) ?>">
                                        <i class="bi bi-recycle me-2"></i>Desperdicios
                                    </a>
                                <?php endif; ?>

                                <?php if ($secPlanificacion && $secMantenimiento): ?>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <?php if (can('menu.inv_maquinas')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/mantenimiento_inventario')) ?>">
                                        <i class="bi bi-tools me-2"></i>Inventario Maq.
                                    </a>
                                <?php endif; ?>

                                <?php if ($secMantenimiento): ?>
                                    <!-- SOLO Calendario Mtto (Prog. Mtto eliminado) -->
                                    <a class="dropdown-item" href="<?= esc(base_url('mtto/calendario')) ?>">
                                        <i class="bi bi-calendar3 me-2"></i>Calendario Mtto
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.mant_correctivo')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/mantenimiento_correctivo')) ?>">
                                        <i class="bi bi-wrench-adjustable-circle me-2"></i>Mant. Correctivo
                                    </a>
                                <?php endif; ?>

                                <?php if ($secMantenimiento && $secLogistica): ?>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <?php if (can('menu.logistica_preparacion')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/logistica_preparacion')) ?>">
                                        <i class="bi bi-box-seam me-2"></i>Prep. Envíos
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.logistica_gestion')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/logistica_gestion')) ?>">
                                        <i class="bi bi-truck me-2"></i>Gestión Envíos
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.logistica_documentos')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/logistica_documentos')) ?>">
                                        <i class="bi bi-file-earmark-text me-2"></i>Docs. Embarque
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.inventario_almacen')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('almacen/inventario')) ?>">
                                        <i class="bi bi-boxes me-2"></i>Inventario Almacén
                                    </a>
                                <?php endif; ?>

                                <?php if ($secLogistica && $secAdmin): ?>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <?php if (can('menu.reportes')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo3/reportes')) ?>">
                                        <i class="bi bi-bar-chart-line me-2"></i>Reportes
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.roles')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo11/roles')) ?>">
                                        <i class="bi bi-person-gear me-2"></i>Roles
                                    </a>
                                <?php endif; ?>

                                <?php if (can('menu.usuarios')): ?>
                                    <a class="dropdown-item" href="<?= esc(base_url('modulo11/usuarios')) ?>">
                                        <i class="bi bi-shield-lock me-2"></i>Gestión Usuarios
                                    </a>
                                <?php endif; ?>

                            </div>
                        </li>
                    </ul>
                <?php endif; ?>
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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" defer></script>

    <!-- Modo oscuro / claro -->
    <script>
        (function () {
            const STORAGE_KEY = 'theme';

            function getPreferredTheme() {
                try {
                    const stored = localStorage.getItem(STORAGE_KEY);
                    if (stored === 'light' || stored === 'dark') return stored;
                } catch (e) {
                    // ignore storage errors
                }
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return 'dark';
                }
                return 'light';
            }

            function applyTheme(theme) {
                const body = document.body;
                if (!body) return;
                body.setAttribute('data-theme', theme);
                document.querySelectorAll('.js-theme-icon').forEach(function (icon) {
                    icon.classList.toggle('bi-moon-fill', theme === 'light');
                    icon.classList.toggle('bi-sun-fill', theme === 'dark');
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                const initial = getPreferredTheme();
                applyTheme(initial);

                // Soportar varios toggles (escritorio + móvil)
                document.querySelectorAll('.js-theme-toggle').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const current = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                        const next = current === 'dark' ? 'light' : 'dark';
                        try { localStorage.setItem(STORAGE_KEY, next); } catch (e) {}
                        applyTheme(next);
                    });
                });

                if (window.matchMedia) {
                    const mq = window.matchMedia('(prefers-color-scheme: dark)');
                    if (mq.addEventListener) {
                        mq.addEventListener('change', function (e) {
                            try {
                                const stored = localStorage.getItem(STORAGE_KEY);
                                if (stored === 'light' || stored === 'dark') return; // usuario ya eligió
                            } catch (err) {
                                // ignore
                            }
                            applyTheme(e.matches ? 'dark' : 'light');
                        });
                    }
                }
            });
        })();
    </script>

    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', () => {
                try {
                    if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                            new bootstrap.Tooltip(el);
                        });
                    }
                } catch (err) {
                    console.warn('Error inicializando tooltips', err);
                }

                document.querySelectorAll('table').forEach(tbl => {
                    tbl.classList.add('table', 'table-striped', 'table-hover', 'align-middle');
                    const parent = tbl.parentElement;
                    if (!parent) return;
                    if (parent.classList && parent.classList.contains('table-responsive')) return;
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive';
                    parent.replaceChild(wrapper, tbl);
                    wrapper.appendChild(tbl);
                });

                document.addEventListener('click', async (ev) => {
                    const a = ev.target.closest('.js-open-incidencia-modal');
                    if (!a) return;
                    ev.preventDefault();
                    try {
                        const url = '<?= esc(base_url('modulo3/incidencias/modal')) ?>' + '?t=' + Date.now();
                        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const html = await res.text();
                        let wrap = document.getElementById('incidencias-modal-wrap');
                        if (!wrap) {
                            wrap = document.createElement('div');
                            wrap.id = 'incidencias-modal-wrap';
                            document.body.appendChild(wrap);
                        }
                        wrap.innerHTML = html;
                        const modalEl = document.getElementById('incidenciaModal');
                        if (window.bootstrap && modalEl) {
                            const m = new bootstrap.Modal(modalEl, { backdrop: 'static' });
                            m.show();
                        }
                    } catch (e) {
                        console.error('Error cargando modal incidencias', e);
                    }
                });
            });

            window.addEventListener('pageshow', function (e) {
                if (e.persisted) {
                    location.reload();
                }
            });
            window.addEventListener('unload', function () { });
        })();
    </script>

    <!-- Notification Polling System -->
    <script src="<?= base_url('js/notification-poller.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>