<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= esc($title ?? 'Maquiladora') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('css/maquila.css') ?>" rel="stylesheet">

    <?= $this->renderSection('head') ?>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('modulo3/dashboard') ?>">
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="48" class="me-2">
            <span class="fw-bold text-dark">Sistema de Maquiladora</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav"
                aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topnav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <!-- Inicio (icono) -->
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?= base_url('dashboard') ?>"
                       title="Inicio" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Inicio">
                        <i class="bi bi-house fs-5"></i>
                        <span class="visually-hidden">Inicio</span>
                    </a>
                </li>

                <!-- Perfil (icono) -->
                <li class="nav-item ms-lg-2">
                    <a class="nav-link text-dark" href="<?= base_url('modulo1/perfilempleado') ?>"
                       title="Perfil" data-bs-toggle="tooltip" aria-label="Perfil">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="visually-hidden">Perfil</span>
                    </a>
                </li>

                <!-- Notificaciones (icono con badge) -->
                <li class="nav-item ms-lg-2">
                    <a class="nav-link text-dark position-relative d-inline-block"
                       href="<?= base_url('modulo3/notificaciones') ?>"
                       title="Notificaciones" data-bs-toggle="tooltip" aria-label="Notificaciones">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= esc($notifCount ?? 0) ?>
              <span class="visually-hidden">notificaciones</span>
            </span>
                    </a>
                </li>

                <!-- Accesos rápidos (dropdown) -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle text-dark d-flex align-items-center"
                       href="#" id="quickMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       title="Accesos rápidos" data-bs-placement="bottom">
                        <i class="bi bi-lightning-charge fs-5"></i>
                        <span class="visually-hidden">Accesos rápidos</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="quickMenu" style="min-width: 280px;">
                        <a class="dropdown-item" href="<?= base_url('modulo1/pedidos') ?>"><i class="bi bi-bag me-2"></i>Pedidos</a>
                        <a class="dropdown-item" href="<?= base_url('modulo1/produccion') ?>"><i class="bi bi-gear-wide-connected me-2"></i>Producción</a>
                        <a class="dropdown-item" href="<?= base_url('modulo1/ordenes') ?>"><i class="bi bi-card-checklist me-2"></i>Órdenes</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/ordenesclientes') ?>"><i class="bi bi-people me-2"></i>Órdenes Clientes</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo2/catalogodisenos') ?>"><i class="bi bi-brush me-2"></i>Catálogo de Diseños</a>
                        <a class="dropdown-item" href="<?= base_url('muestras') ?>"><i class="bi bi-palette2 me-2"></i>Muestras</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/inspeccion') ?>"><i class="bi bi-search me-2"></i>Inspección</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/wip') ?>"><i class="bi bi-diagram-3 me-2"></i>WIP</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/incidencias') ?>"><i class="bi bi-exclamation-triangle me-2"></i>Incidencias</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/reportes') ?>"><i class="bi bi-bar-chart-line me-2"></i>Reportes</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/mrp') ?>"><i class="bi bi-diagram-2 me-2"></i>MRP</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/desperdicios') ?>"><i class="bi bi-recycle me-2"></i>Desperdicios</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/mantenimiento_inventario') ?>"><i class="bi bi-tools me-2"></i>Inventario Maq.</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/mantenimiento_correctivo') ?>"><i class="bi bi-wrench-adjustable-circle me-2"></i>Mant. Correctivo</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/logistica_preparacion') ?>"><i class="bi bi-box-seam me-2"></i>Prep. Envíos</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/logistica_gestion') ?>"><i class="bi bi-truck me-2"></i>Gestión Envíos</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/logistica_documentos') ?>"><i class="bi bi-file-earmark-text me-2"></i>Docs. Embarque</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo11/usuarios') ?>"><i class="bi bi-shield-lock me-2"></i>Gestión Usuarios</a>
                    </div>
                </li>

                <!-- Usuario logueado (se mantiene igual) -->
                <li class="nav-item d-lg-flex align-items-center ms-lg-3">
          <span class="nav-user badge rounded-pill bg-white text-dark border me-lg-3">
            <i class="bi bi-person-circle me-1"></i><?= esc(session()->get('user_name') ?? 'Usuario') ?>
            <small class="ms-1">(<?= esc(session()->get('user_role') ?? 'admin') ?>)</small>
          </span>
                </li>

                <!-- Cerrar sesión (icono) -->
                <li class="nav-item ms-lg-2">
                    <a href="<?= base_url('logout') ?>" class="btn btn-dark"
                       title="Cerrar sesión" data-bs-toggle="tooltip" aria-label="Cerrar sesión">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="visually-hidden">Cerrar sesión</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?= $this->renderSection('content') ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</main>

<footer class="border-top py-3 bg-white">
    <div class="container small text-muted">© <?= date('Y') ?> Maquiladora </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tips.forEach(el => new bootstrap.Tooltip(el));
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
