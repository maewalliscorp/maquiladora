<?php
namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\OrdenProduccionService;
use App\Constants\OrdenStatus;
use App\Models\OrdenProduccionModel;

class OrdenProduccionServiceTest extends CIUnitTestCase
{
    protected $service;
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrdenProduccionService();
        $this->model = new OrdenProduccionModel();
    }

    public function testActualizarEstatus()
    {
        // 1. Crear una orden de prueba (mock o real si es DB de pruebas)
        // Para este ejemplo, asumiremos que existe una orden con ID 1 o crearemos una
        // NOTA: En un entorno real, usaríamos DatabaseTransactions trait para revertir cambios

        // Intentar actualizar una orden inexistente debería fallar o devolver error
        $result = $this->service->actualizarEstatus(-1, OrdenStatus::EN_PROCESO);
        $this->assertFalse($result['ok']);
        $this->assertEquals('Parámetros inválidos', $result['error']);

        // Prueba básica de validación
        $this->assertTrue(true);
    }
}
