<?php
namespace App\Models;

use CodeIgniter\Model;

class WipModel extends Model
{
    protected $returnType = 'array';

    /**
     * Listado de diseños (SQL tal cual lo pediste)
     */
    public function getDatosDiseno(): array
    {
        $db = \Config\Database::connect();
        $sql = "
            SELECT 
                d.id          AS Ide,
                d.clienteid   AS numeroCliente,
                d.codigo      AS CodigoDiseno,
                d.nombre      AS NombreDiseno,
                d.descripcion AS DescripcionDiseno
            FROM diseno AS d
            ORDER BY d.id DESC
        ";
        try {
            return $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'getDatosDiseno: '.$e->getMessage());
            return [];
        }
    }
}
