<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosModel extends Model
{
    protected $table = 'empleado';
    protected $primaryKey = 'id';
    protected $allowedFields = [];
    protected $returnType = 'array';

    /**
     * Obtener todos los empleados de la maquiladora con su forma de pago
     */
    public function getEmpleadosPorMaquiladora($maquiladoraId = null)
    {
        $db = \Config\Database::connect();
        
        try {
            // Si no se proporciona ID, usar el de la sesión
            if (!$maquiladoraId) {
                $maquiladoraId = session()->get('maquiladora_id');
            }
            
            if (!$maquiladoraId) {
                return [];
            }

            // Consulta principal (snake case)
            $empleados = $db->query(
                'SELECT 
                    e.id,
                    e.noEmpleado,
                    e.nombre,
                    e.apellido,
                    e.Forma_Pago AS Forma_pago,
                    e.puesto,
                    e.email,
                    e.telefono,
                    e.domicilio,
                    e.fecha_nac,
                    e.curp,
                    e.activo,
                    CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                    u.username,
                    u.correo AS correo_usuario,
                    u.active AS usuario_activo,
                    m.Nombre_Maquila AS nombre_maquiladora
                 FROM empleado e 
                 INNER JOIN users u ON e.idusuario = u.id
                 LEFT JOIN maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                 WHERE u.maquiladoraIdFK = ? 
                 ORDER BY e.nombre ASC, e.apellido ASC',
                [$maquiladoraId]
            )->getResultArray();

            // Si no hay resultados, intentar con mayúsculas
            if (empty($empleados)) {
                $empleados = $db->query(
                    'SELECT 
                        e.id,
                        e.noEmpleado,
                        e.nombre,
                        e.apellido,
                        e.Forma_Pago AS Forma_pago,
                        e.puesto,
                        e.email,
                        e.telefono,
                        e.domicilio,
                        e.fecha_nac,
                        e.curp,
                        e.activo,
                        CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                        u.username,
                        u.correo AS correo_usuario,
                        u.active AS usuario_activo,
                        m.Nombre_Maquila AS nombre_maquiladora
                     FROM Empleado e 
                     INNER JOIN Users u ON e.idusuario = u.id
                     LEFT JOIN Maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                     WHERE u.maquiladoraIdFK = ? 
                     ORDER BY e.nombre ASC, e.apellido ASC',
                    [$maquiladoraId]
                )->getResultArray();
            }

            // Calcular edad y formatear datos
            foreach ($empleados as &$empleado) {
                // Calcular edad si tiene fecha de nacimiento
                if ($empleado['fecha_nac']) {
                    try {
                        $fechaNac = new \DateTime($empleado['fecha_nac']);
                        $hoy = new \DateTime();
                        $edad = $hoy->diff($fechaNac);
                        $empleado['edad'] = $edad->y;
                    } catch (\Exception $e) {
                        $empleado['edad'] = null;
                    }
                } else {
                    $empleado['edad'] = null;
                }

                // Formatear forma de pago
                $empleado['Forma_pago'] = $empleado['Forma_pago'] ?? 'No registrada';

                // Determinar estatus
                $empleado['estatus_texto'] = ($empleado['activo'] == 1 && $empleado['usuario_activo'] == 1) ? 'Activo' : 'Inactivo';
                $empleado['estatus_clase'] = ($empleado['activo'] == 1 && $empleado['usuario_activo'] == 1) ? 'success' : 'danger';
            }

            return $empleados;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getEmpleadosPorMaquiladora: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un empleado específico por ID
     */
    public function getEmpleadoPorId($empleadoId)
    {
        $db = \Config\Database::connect();
        
        try {
            $empleado = $db->query(
                'SELECT 
                    e.id,
                    e.noEmpleado,
                    e.nombre,
                    e.apellido,
                    e.Forma_Pago AS Forma_pago,
                    e.puesto,
                    e.email,
                    e.telefono,
                    e.domicilio,
                    e.fecha_nac,
                    e.curp,
                    e.activo,
                    CONCAT(e.nombre, " ", e.apellido) AS nombre_completo,
                    u.username,
                    u.correo AS correo_usuario,
                    u.active AS usuario_activo,
                    m.Nombre_Maquila AS nombre_maquiladora
                 FROM empleado e 
                 INNER JOIN users u ON e.idusuario = u.id
                 LEFT JOIN maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                 WHERE e.id = ? AND u.maquiladoraIdFK = ? 
                 LIMIT 1',
                [$empleadoId, session()->get('maquiladora_id')]
            )->getRowArray();

            return $empleado;

        } catch (\Throwable $e) {
            log_message('error', 'Error en getEmpleadoPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar forma de pago de un empleado
     */
    public function actualizarFormaPago($empleadoId, $formaPago)
    {
        $db = \Config\Database::connect();
        
        try {
            $builder = $db->table('empleado');
            $result = $builder->where('id', $empleadoId)->update(['Forma_pago' => $formaPago]);
            
            return $result;
            
        } catch (\Throwable $e) {
            log_message('error', 'Error en actualizarFormaPago: ' . $e->getMessage());
            return false;
        }
    }
}
