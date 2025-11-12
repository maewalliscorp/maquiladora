<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
    #calMtto{
        min-height: 650px;
        background:#fff;
        border-radius:.75rem;
        padding:.75rem;
        box-shadow:0 6px 18px rgba(0,0,0,.06);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Calendario de Mantenimiento</h2>
        <a href="<?= site_url('mtto/programacion') ?>" class="btn btn-outline-light">
            <i class="bi bi-list-ul"></i> Ver Programación (lista)
        </a>
    </div>
    <div id="calMtto"></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('calMtto');
        const calendar = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            navLinks: true,
            nowIndicator: true,
            weekNumbers: true,
            events: '<?= site_url('mtto/api/eventos') ?>'
        });
        calendar.render();
    });
</script>
<?= $this->endSection() ?>
