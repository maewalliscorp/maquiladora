<?php
namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\DashboardService;

class DashboardServiceTest extends CIUnitTestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
    }

    public function testGetKPIs()
    {
        $kpis = $this->service->getKPIs();
        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('ordenes_activas', $kpis);
        $this->assertArrayHasKey('wip_cantidad', $kpis);
        $this->assertArrayHasKey('tasa_defectos', $kpis);
        $this->assertArrayHasKey('stock_critico', $kpis);
    }

    public function testGetProduccionStats()
    {
        $stats = $this->service->getProduccionStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('labels', $stats);
        $this->assertArrayHasKey('datasets', $stats);
    }

    public function testGetCalidadStats()
    {
        $stats = $this->service->getCalidadStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('labels', $stats);
        $this->assertArrayHasKey('datasets', $stats);
    }

    public function testGetInventarioStats()
    {
        $stats = $this->service->getInventarioStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('labels', $stats);
        $this->assertArrayHasKey('datasets', $stats);
    }

    public function testGetNotifications()
    {
        // Asumimos un usuario ID 1 para la prueba
        $notifs = $this->service->getNotifications(1);
        $this->assertIsArray($notifs);
        // No podemos asegurar que haya notificaciones, pero debe devolver un array
    }
}
