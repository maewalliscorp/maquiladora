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


            <ul class="navbar-nav align-items-lg-center">
                <!-- Usuario logueado -->
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?= base_url('dashboard') ?>">
                        <i class="bi bi-house me-1"></i>Inicio
                    </a>
                </li>
                <li class="nav-item d-lg-flex align-items-center ms-lg-3">
                    <a class="nav-link text-dark" href="<?= base_url('modulo1/perfilempleado') ?>">
                        <i class="bi bi-person-circle me-1"></i>Perfil
                    </a>
                </li>
                <!-- Usuario logueado -->
                <li class="nav-item d-lg-flex align-items-center ms-lg-3">
          <span class="nav-user badge rounded-pill bg-white text-dark border me-lg-3">
            <i class="bi bi-person-circle me-1"></i><?= esc(session()->get('user_name') ?? 'Usuario') ?>
            <small class="ms-1">(<?= esc(session()->get('user_role') ?? 'admin') ?>)</small>
          </span>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('logout') ?>" class="btn btn-dark ms-lg-2">Cerrar sesión</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
