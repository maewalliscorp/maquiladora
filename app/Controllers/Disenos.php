<?php

namespace App\Controllers;

class Disenos extends BaseController
{
    // Devuelve catálogo completo de diseños con su versión más reciente
    public function json_catalogo()
    {
        try {
            // Obtener ID de maquiladora desde la sesión
            $maquiladoraId = session()->get('maquiladora_id');
            
            // Log para debug
            log_message('debug', 'json_catalogo: maquiladoraId = ' . ($maquiladoraId ?? 'NULL'));
            
            $disenoModel = new \App\Models\DisenoModel();
            $catalogo = $disenoModel->getCatalogoDisenos($maquiladoraId);
            
            // Log para debug
            log_message('debug', 'json_catalogo: count = ' . count($catalogo));
            
            $out = array_map(function($r){
                return [
                    'id'          => $r['id'] ?? null,
                    'codigo'      => $r['codigo'] ?? null,
                    'nombre'      => $r['nombre'] ?? null,
                    'descripcion' => $r['descripcion'] ?? null,
                    'version'     => $r['version'] ?? null,
                    'fecha'       => $r['fecha'] ?? null,
                    'aprobado'    => $r['aprobado'] ?? null,
                ];
            }, $catalogo ?: []);
            
            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            // Log del error para debug
            log_message('error', 'json_catalogo error: ' . $e->getMessage());
            log_message('error', 'json_catalogo trace: ' . $e->getTraceAsString());
            
            // En desarrollo, devolver el error; en producción, array vacío
            if (ENVIRONMENT === 'development') {
                return $this->response->setJSON([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return $this->response->setJSON([]);
        }
    }
    
    // Devuelve el detalle completo de un diseño específico
    public function json_detalle($id = null)
    {
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'ID inválido'
            ]);
        }
        
        try {
            $disenoModel = new \App\Models\DisenoModel();
            $detalle = $disenoModel->getDisenoDetalle($id);
            
            if (!$detalle) {
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'Diseño no encontrado'
                ]);
            }
            
            return $this->response->setJSON($detalle);
        } catch (\Throwable $e) {
            log_message('error', 'json_detalle error: ' . $e->getMessage());
            
            if (ENVIRONMENT === 'development') {
                return $this->response->setJSON([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al obtener el diseño'
            ]);
        }
    }
    
    // Método temporal de diagnóstico
    public function debug()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        
        $info = [
            'session_maquiladora_id' => $maquiladoraId,
            'session_all' => session()->get(),
        ];
        
        // Verificar campos de la tabla diseno
        try {
            $fields = $db->getFieldNames('diseno');
            $info['diseno_fields'] = $fields;
            
            // Verificar qué campo de maquiladora existe
            $fieldToUse = null;
            if (in_array('maquiladoraID', $fields)) {
                $fieldToUse = 'maquiladoraID';
            } elseif (in_array('maquiladoraIdFK', $fields)) {
                $fieldToUse = 'maquiladoraIdFK';
            } elseif (in_array('maquiladora_id', $fields)) {
                $fieldToUse = 'maquiladora_id';
            }
            $info['field_maquiladora_detectado'] = $fieldToUse;
        } catch (\Throwable $e) {
            $info['diseno_fields_error'] = $e->getMessage();
        }
        
        // Obtener todos los diseños sin filtro
        try {
            $sql = "SELECT id, codigo, nombre, maquiladoraID FROM diseno LIMIT 20";
            $info['disenos_sin_filtro'] = $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            $info['disenos_sin_filtro_error'] = $e->getMessage();
        }
        
        // Contar diseños por maquiladora
        try {
            $sql = "SELECT maquiladoraID, COUNT(*) as total FROM diseno GROUP BY maquiladoraID";
            $info['disenos_por_maquiladora'] = $db->query($sql)->getResultArray();
        } catch (\Throwable $e) {
            $info['disenos_por_maquiladora_error'] = $e->getMessage();
        }
        
        // Intentar con el modelo
        try {
            $disenoModel = new \App\Models\DisenoModel();
            $catalogo = $disenoModel->getCatalogoDisenos($maquiladoraId);
            $info['catalogo_count'] = count($catalogo);
            $info['catalogo_sample'] = array_slice($catalogo, 0, 5);
        } catch (\Throwable $e) {
            $info['catalogo_error'] = $e->getMessage();
            $info['catalogo_trace'] = $e->getTraceAsString();
        }
        
        return $this->response->setJSON($info);
    }
}
