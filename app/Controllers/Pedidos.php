<?php

namespace App\Controllers;

class Pedidos extends BaseController
{
    public function index()
    {
        return view('pedidos'); // busca el archivo app/Views/pedidos.php
    }

    public function editar($id)
    {
        // Aquí deberías buscar el pedido en la BD
        $pedido = $this->obtenerPedidoPorId($id);

        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'pedido' => $pedido,
            'titulo' => 'Editar Pedido'
        ];

        return view('editarpedido', $data);
    }

    public function actualizar($id)
    {
        // Aquí actualizas en la BD con los datos recibidos
        // $this->pedidoModel->update($id, $this->request->getPost());

        // Guardamos mensaje flash
        session()->setFlashdata('success', '✅ Pedido actualizado correctamente');

        // Redirigimos a la lista de pedidos
        return redirect()->to('pedidos');
    }

    public function detalles($id)
    {
        // Obtener datos del pedido
        $pedido = $this->obtenerPedidoPorId($id);

        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'pedido' => $pedido,
            'titulo' => 'Detalle del Pedido'
        ];

        return view('detalle_pedido', $data);
    }

    private function obtenerPedidoPorId($id)
    {
        // Datos de ejemplo (en una aplicación real, esto vendría de la BD)
        $pedidos = [
            1 => [
                'id' => 1,
                'empresa' => 'Textiles del Norte S.A. de C.V.',
                'contacto' => 'Juan Pérez',
                'direccion' => 'Av. Industrial 123, Ciudad de México',
                'telefono' => '55-1234-5678',
                'email' => 'juan@textilesnorte.com',
                'rfc' => 'TEX123456789',
                'descripcion' => 'Camiseta de piqué algodón 100%, cuello redondo, corte regular',
                'tallas' => 'S, M, L, XL',
                'materiales' => 'Algodón 100%',
                'color' => 'Azul marino',
                'cantidad' => '100',
                'fecha_entrega' => '30 de Noviembre de 2025',
                'progreso' => 75,
                'modelo' => 'MODELO 1'
            ],
            2 => [
                'id' => 2,
                'empresa' => 'Hilados y Telas del Bajío S.A. de C.V.',
                'contacto' => 'María García',
                'direccion' => 'Calle Textil 456, Guadalajara',
                'telefono' => '33-9876-5432',
                'email' => 'maria@hiladosbajio.com',
                'rfc' => 'HIL987654321',
                'descripcion' => 'Jeans tipo "Skinny" dama, lavado claro',
                'tallas' => '24-36',
                'materiales' => 'Mezclilla elastizada',
                'color' => 'Azul claro',
                'cantidad' => '5,000',
                'fecha_entrega' => '15 de Diciembre de 2025',
                'progreso' => 50,
                'modelo' => 'MODELO 2'
            ],
            3 => [
                'id' => 3,
                'empresa' => 'Confecciones Industriales de México S.A. de C.V.',
                'contacto' => 'Carlos Rodríguez',
                'direccion' => 'Blvd. Industrial 789, Monterrey',
                'telefono' => '81-5555-1234',
                'email' => 'carlos@confecciones.com',
                'rfc' => 'CON456789123',
                'descripcion' => 'Conjunto de uniforme recepción',
                'tallas' => 'XS, S, M, L, XL',
                'materiales' => 'Poliéster 65%, Algodón 35%',
                'color' => 'Negro y blanco',
                'cantidad' => '2,500',
                'fecha_entrega' => '10 de Enero de 2026',
                'progreso' => 90,
                'modelo' => 'MODELO 3'
            ],
            4 => [
                'id' => 4,
                'empresa' => 'Moda y Estilo Textil S.A. de C.V.',
                'contacto' => 'Ana López',
                'direccion' => 'Av. de la Moda 321, Puebla',
                'telefono' => '222-333-4444',
                'email' => 'ana@modaestilo.com',
                'rfc' => 'MOD789123456',
                'descripcion' => 'Prototipos y muestrario línea chaquetas oversize',
                'tallas' => 'Única',
                'materiales' => 'Mezclilla premium',
                'color' => 'Varios',
                'cantidad' => '500',
                'fecha_entrega' => '20 de Febrero de 2026',
                'progreso' => 0,
                'modelo' => 'OTRO'
            ]
        ];

        return $pedidos[$id] ?? null;
    }
}