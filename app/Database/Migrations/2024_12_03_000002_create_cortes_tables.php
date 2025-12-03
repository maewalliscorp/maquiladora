<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCortesTables extends Migration
{
    public function up()
    {
        // 1. Tabla 'cortes' (Encabezado)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'idmaquiladora' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'numero_corte' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'estilo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'prenda' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'precio' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'fecha_entrada' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_embarque' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'cortador' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'tendedor' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'tela' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'largo_trazo' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4', // 4 decimales para precisión en metros
                'default' => 0.0000,
            ],
            'ancho_tela' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'total_prendas' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'total_tela_usada' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'consumo_promedio' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
                'default' => 0.0000,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('idmaquiladora');
        $this->forge->createTable('cortes');

        // 2. Tabla 'cortes_detalles' (Rollos)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'corte_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'numero_rollo' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'lote' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'color_rollo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'peso_kg' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'longitud_mts' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'metros_usados' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'merma_danada' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'merma_faltante' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'merma_desperdicio' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'tela_sobrante' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'diferencia' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'cantidad_lienzos' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'total_prendas_rollo' => [
                'type' => 'INT',
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('corte_id', 'cortes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cortes_detalles');

        // 3. Tabla 'cortes_tallas' (Matriz de Tallas)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'corte_detalle_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'talla' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'cantidad' => [
                'type' => 'INT',
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('corte_detalle_id', 'cortes_detalles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cortes_tallas');
    }

    public function down()
    {
        $this->forge->dropTable('cortes_tallas');
        $this->forge->dropTable('cortes_detalles');
        $this->forge->dropTable('cortes');
    }
}
