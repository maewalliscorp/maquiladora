<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateControlBultosTables extends Migration
{
    public function up()
    {
        // Tabla 1: control_bultos
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'maquiladoraId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ordenProduccionId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Requerido para calcular progreso',
            ],
            'inspeccionId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'estilo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'orden' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'cantidad_total' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Total de prendas en la orden',
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['en_proceso', 'listo_armado', 'completado'],
                'default' => 'en_proceso',
            ],
            'fecha_creacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'usuario_creacion' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('maquiladoraId');
        $this->forge->addKey('ordenProduccionId');
        $this->forge->addKey('inspeccionId');
        $this->forge->createTable('control_bultos', true);

        // Tabla 2: bultos
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'controlBultoId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'numero_bulto' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'talla' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'cantidad' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('controlBultoId');
        $this->forge->addForeignKey('controlBultoId', 'control_bultos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bultos', true);

        // Tabla 3: operaciones_control
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'controlBultoId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nombre_operacion' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'comment' => 'Ej: PEGAR PUÑO',
            ],
            'piezas_requeridas' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Ej: 40 puños para 20 camisas',
            ],
            'piezas_completadas' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'porcentaje_completado' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
                'comment' => 'Calculado automáticamente',
            ],
            'es_componente' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'true si es parte pre-armado',
            ],
            'orden' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Para ordenar operaciones',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('controlBultoId');
        $this->forge->addForeignKey('controlBultoId', 'control_bultos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('operaciones_control', true);

        // Tabla 4: registros_produccion
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'operacionControlId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'empleadoId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'cantidad_producida' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Ej: 20 puños',
            ],
            'fecha_registro' => [
                'type' => 'DATE',
            ],
            'hora_inicio' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'hora_fin' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'tiempo_empleado' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Calculado en minutos',
            ],
            'registrado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('operacionControlId');
        $this->forge->addKey('empleadoId');
        $this->forge->addKey('fecha_registro');
        $this->forge->addForeignKey('operacionControlId', 'operaciones_control', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('registros_produccion', true);

        // Tabla 5: plantillas_operaciones
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'maquiladoraId' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_prenda' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Ej: CAMISA, PANTALÓN',
            ],
            'nombre_plantilla' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'operaciones' => [
                'type' => 'JSON',
                'comment' => 'Array de operaciones con piezas_por_prenda',
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('maquiladoraId');
        $this->forge->addKey('tipo_prenda');
        $this->forge->createTable('plantillas_operaciones', true);
    }

    public function down()
    {
        $this->forge->dropTable('registros_produccion', true);
        $this->forge->dropTable('operaciones_control', true);
        $this->forge->dropTable('bultos', true);
        $this->forge->dropTable('plantillas_operaciones', true);
        $this->forge->dropTable('control_bultos', true);
    }
}
