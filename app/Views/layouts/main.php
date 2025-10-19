<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= esc($title ?? 'Maquiladora') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= base_url('css/maquila.css') ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <?= $this->renderSection('head') ?>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <div class="container-fluid">
        <!-- Logo compacto para móvil -->
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('modulo3/dashboard') ?>">
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="32" class="me-1 d-lg-none">
            <img src="<?= base_url('img/logo_Maquiladora.png') ?>" alt="Logo" width="48" class="me-2 d-none d-lg-block">
            <span class="fw-bold text-dark d-none d-md-inline">Sistema de Maquiladora</span>
        </a>

        <!-- Indicador de usuario en móvil -->
        <div class="d-flex align-items-center d-lg-none">
            <span class="badge bg-light text-dark border me-2">
                <i class="bi bi-person-circle me-1"></i>
                <?= esc(session()->get('user_name') ?? 'Usuario') ?>
            </span>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="topnav">
            <!-- Menú móvil -->
            <div class="d-lg-none">

                <!-- Acciones principales -->
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-light w-100 d-flex flex-column align-items-center p-2 border"
                           href="<?= base_url('dashboard') ?>">
                            <i class="bi bi-house fs-5 text-primary mb-1"></i>
                            <small>Inicio</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-light w-100 d-flex flex-column align-items-center p-2 border"
                           href="<?= base_url('modulo1/perfilempleado') ?>">
                            <i class="bi bi-person-circle fs-5 text-success mb-1"></i>
                            <small>Perfil</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-light w-100 d-flex flex-column align-items-center p-2 border position-relative"
                           href="<?= base_url('modulo3/notificaciones') ?>">
                            <i class="bi bi-bell fs-5 text-warning mb-1"></i>
                            <small>Alertas</small>
                            <?php if (isset($notifCount) && $notifCount > 0): ?>
                                <span class="position-absolute top-0 end-0 badge rounded-pill bg-danger">
                                    <?= esc($notifCount) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <!-- Gestión Principal -->
                <h6 class="text-muted small mb-2">GESTIÓN PRINCIPAL</h6>
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo2/catalogodisenos') ?>">
                            <i class="bi bi-brush"></i>
                            <small>Diseños</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo1/pedidos') ?>">
                            <i class="bi bi-bag"></i>
                            <small>Pedidos</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo1/ordenes') ?>">
                            <i class="bi bi-list-check"></i>
                            <small>Órdenes</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo1/produccion') ?>">
                            <i class="bi bi-gear"></i>
                            <small>Producción</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/ordenesclientes') ?>">
                            <i class="bi bi-people"></i>
                            <small>Clientes</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-primary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('muestras') ?>">
                            <i class="bi bi-palette2"></i>
                            <small>Muestras</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-success w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/inspeccion') ?>">
                            <i class="bi bi-search"></i>
                            <small>Inspección</small>
                        </a>
                    </div>
                </div>

                <!-- Control de Calidad -->
                <h6 class="text-muted small mb-2">CONTROL DE CALIDAD</h6>
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-outline-success w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/wip') ?>">
                            <i class="bi bi-diagram-3"></i>
                            <small>Trabajo en proceso</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-success w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/incidencias') ?>">
                            <i class="bi bi-exclamation-triangle"></i>
                            <small>Incidencias</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-success w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/desperdicios') ?>">
                            <i class="bi bi-recycle"></i>
                            <small>Desperdicios</small>
                        </a>
                    </div>
                </div>

                <!-- Planificación -->
                <h6 class="text-muted small mb-2">PLANIFICACIÓN</h6>
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-outline-info w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/mrp') ?>">
                            <i class="bi bi-diagram-2"></i>
                            <small>Planificacion Materiales</small>
                        </a>
                    </div>
                </div>

                <!-- Mantenimiento -->
                <h6 class="text-muted small mb-2">MANTENIMIENTO</h6>
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-outline-warning w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/mantenimiento_inventario') ?>">
                            <i class="bi bi-tools"></i>
                            <small>Inventario</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-warning w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/mantenimiento_correctivo') ?>">
                            <i class="bi bi-wrench"></i>
                            <small>Correctivo</small>
                        </a>
                    </div>
                </div>

                <!-- Logística -->
                <h6 class="text-muted small mb-2">LOGÍSTICA</h6>
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/logistica_preparacion') ?>">
                            <i class="bi bi-box-seam"></i>
                            <small>Prep. Envíos</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/logistica_gestion') ?>">
                            <i class="bi bi-truck"></i>
                            <small>Gestión</small>
                        </a>
                    </div>
                    <div class="col-4">
                        <a class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/logistica_documentos') ?>">
                            <i class="bi bi-file-text"></i>
                            <small>Documentos</small>
                        </a>
                    </div>
                    <!-- INVENTARIO DE ALMACÉN (nuevo) -->
                    <div class="col-4">
                        <a class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center p-2"
                           href="<?= base_url('almacen/inventario') ?>">
                            <i class="bi bi-boxes"></i>
                            <small>Inventario Almacén</small>
                        </a>
                    </div>
                </div>

                <!-- Administración -->
                <h6 class="text-muted small mb-2">ADMINISTRACIÓN</h6>
                <div class="row g-1 mb-3">
                    <div class="col-4">
                        <a class="btn btn-outline-success w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo3/reportes') ?>">
                            <i class="bi bi-bar-chart-line"></i>
                            <small>Reportes</small>
                        </a>
                    </div>
                    <a class="btn btn-outline-dark w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo11/roles') ?>">
                        <i class="bi bi-person-gear"></i>
                        <small>Roles</small>
                    </a>
                    <div class="col-4">
                        <a class="btn btn-outline-dark w-100 d-flex flex-column align-items-center p-2" href="<?= base_url('modulo11/usuarios') ?>">
                            <i class="bi bi-shield-lock"></i>
                            <small>Usuarios</small>
                        </a>
                    </div>
                </div>

                <!-- Cerrar sesión -->
                <div class="mt-3 pt-3 border-top">
                    <a class="btn btn-outline-danger w-100" href="<?= base_url('logout') ?>">
                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                    </a>
                </div>
            </div>

            <!-- Menú para escritorio -->
            <ul class="navbar-nav ms-auto d-none d-lg-flex align-items-lg-center">
                <!-- Inicio -->
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?= base_url('dashboard') ?>"
                       title="Inicio" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Inicio">
                        <i class="bi bi-house fs-5"></i>
                        <span class="visually-hidden">Inicio</span>
                    </a>
                </li>

                <!-- Perfil -->
                <li class="nav-item ms-lg-2">
                    <a class="nav-link text-dark" href="<?= base_url('modulo1/perfilempleado') ?>"
                       title="Perfil" data-bs-toggle="tooltip" aria-label="Perfil">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="visually-hidden">Perfil</span>
                    </a>
                </li>

                <!-- Notificaciones -->
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

                <!-- Accesos rápidos -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle text-dark d-flex align-items-center"
                       href="#" id="quickMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       title="Accesos rápidos" data-bs-placement="bottom">
                        <i class="fa fa-bars fs-5"></i>
                        <span class="visually-hidden">Accesos rápidos</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="quickMenu" style="min-width: 280px;">
                        <a class="dropdown-item" href="<?= base_url('modulo2/catalogodisenos') ?>"><i class="bi bi-brush me-2"></i>Catálogo de Diseños</a>
                        <a class="dropdown-item" href="<?= base_url('modulo1/pedidos') ?>"><i class="bi bi-bag me-2"></i>Pedidos</a>
                        <a class="dropdown-item" href="<?= base_url('modulo1/ordenes') ?>"><i class="bi bi-card-checklist me-2"></i>Órdenes en proceso</a>
                        <a class="dropdown-item" href="<?= base_url('modulo1/produccion') ?>"><i class="bi bi-gear-wide-connected me-2"></i>Producción</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/ordenesclientes') ?>"><i class="bi bi-people me-2"></i>Órdenes Clientes</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('muestras') ?>"><i class="bi bi-palette2 me-2"></i>Muestras</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/inspeccion') ?>"><i class="bi bi-search me-2"></i>Inspección</a>
                        <div class="dropdown-divider"></div>
                        <!--<a class="dropdown-item" href="<?= base_url('modulo3/wip') ?>"><i class="bi bi-diagram-3 me-2"></i>Trabajo en proceso</a>-->
                        <a class="dropdown-item" href="<?= base_url('modulo3/incidencias') ?>"><i class="bi bi-exclamation-triangle me-2"></i>Incidencias</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/mrp') ?>"><i class="bi bi-diagram-2 me-2"></i>Planificacion materiales</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/desperdicios') ?>"><i class="bi bi-recycle me-2"></i>Desperdicios</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/mantenimiento_inventario') ?>"><i class="bi bi-tools me-2"></i>Inventario Maq.</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/mantenimiento_correctivo') ?>"><i class="bi bi-wrench-adjustable-circle me-2"></i>Mant. Correctivo</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/logistica_preparacion') ?>"><i class="bi bi-box-seam me-2"></i>Prep. Envíos</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/logistica_gestion') ?>"><i class="bi bi-truck me-2"></i>Gestión Envíos</a>
                        <a class="dropdown-item" href="<?= base_url('modulo3/logistica_documentos') ?>"><i class="bi bi-file-earmark-text me-2"></i>Docs. Embarque</a>
                        <!-- INVENTARIO DE ALMACÉN (nuevo) -->
                        <a class="dropdown-item" href="<?= base_url('almacen/inventario') ?>"><i class="bi bi-boxes me-2"></i>Inventario Almacén</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('modulo3/reportes') ?>"><i class="bi bi-bar-chart-line me-2"></i>Reportes</a>
                        <a class="dropdown-item" href="<?= base_url('modulo11/roles') ?>"><i class="bi bi-person-gear me-2"></i>Roles</a>
                        <a class="dropdown-item" href="<?= base_url('modulo11/usuarios') ?>"><i class="bi bi-shield-lock me-2"></i>Gestión Usuarios</a>
                    </div>
                </li>

                <!-- Usuario -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle text-dark d-flex align-items-center"
                       href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                       title="Usuario" data-bs-placement="bottom">
                        <span class="badge rounded-pill bg-white text-dark border me-lg-2">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= esc(session()->get('user_name') ?? 'Usuario') ?>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                        <a class="dropdown-item d-flex align-items-center text-dark"
                           href="<?= base_url('logout') ?>"
                           title="Cerrar sesión">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                        </a>
                    </div>
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

        // Envolver automáticamente todas las tablas en .table-responsive si aún no lo están
        document.querySelectorAll('table').forEach(tbl => {
            tbl.classList.add('table','table-striped','table-hover','align-middle');
            const parent = tbl.parentElement;
            if (!parent || !parent.classList || !parent.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                parent?.insertBefore(wrapper, tbl);
                wrapper.appendChild(tbl);
            }
        });
    });
</script>

<script>
    // Fuerza recarga si la página regresa desde el historial (bfcache) para que se valide sesión
    (function () {
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                location.reload();
            }
        });
    })();
    // Nota: dejar un listener 'unload' vacío evita algunos cachés agresivos en ciertos navegadores
    window.addEventListener('unload', function () {});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
