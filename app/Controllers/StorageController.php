<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class StorageController extends BaseController
{
    public function ping()
    {
        return $this->response->setJSON(['ok' => true, 'now' => date('c')]);
    }

    public function guardarPdf()
    {
        try {
            // --- Validaciones básicas
            if (!$this->request->is('post')) {
                return $this->failBadRequest('Solo POST');
            }
            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                return $this->failBadRequest('No llegó el archivo o es inválido');
            }
            if ($file->getClientMimeType() !== 'application/pdf') {
                // Algunos navegadores envían octet-stream; lo permitimos si termina en .pdf
                $ext = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    return $this->failBadRequest('El archivo debe ser PDF');
                }
            }

            $bucket  = $this->request->getPost('bucket') ?: getenv('SUPABASE_BUCKET_DOC_EMBARQUE') ?: 'Doc_Embarque';
            $folio   = $this->request->getPost('folio')  ?: ('DOC-' . date('Ymd-His'));
            $nameOut = preg_replace('~[^A-Za-z0-9._-]+~', '_', $folio) . '.pdf';

            // --- Carga de credenciales
            $url   = rtrim(getenv('SUPABASE_URL') ?: '', '/');
            $key   = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: getenv('SUPABASE_SERVICE_KEY');
            if (!$url || !$key) {
                return $this->failServerError('Faltan variables SUPABASE_URL o SUPABASE_SERVICE_ROLE_KEY');
            }

            // --- Ruta objeto (sin carpetas por solicitud del usuario)
            $objectPath = $nameOut;

            // --- Leer el binario (Windows/Mac ok)
            $tmpPath = $file->getTempName();
            if (!is_file($tmpPath)) {
                return $this->failServerError('No se pudo leer el archivo temporal');
            }
            $data = file_get_contents($tmpPath);
            if ($data === false) {
                return $this->failServerError('file_get_contents falló');
            }

            // --- Llamada HTTP a Supabase Storage (upload o overwrite)
            $endpoint = $url . '/storage/v1/object/' . rawurlencode($bucket) . '/' . $objectPath;

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => 'PUT',            // PUT sobrescribe si existe
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $key,
                    'Content-Type: application/pdf',
                    'x-upsert: true', // crea o sobreescribe
                ],
                // En Windows algunos entornos necesitan verificar SSL off (no recomendado en prod)
                // CURLOPT_SSL_VERIFYPEER => false,
                // CURLOPT_SSL_VERIFYHOST => false,
            ]);
            $respBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($respBody === false || $httpCode >= 400) {
                // Devuelve texto de supabase si lo hay
                return $this->failServerError('Supabase respondió ' . $httpCode . ' ' . ($respBody ?: $curlErr));
            }

            // --- URL pública (si el bucket es público)
            $publicUrl = $url . '/storage/v1/object/public/' . rawurlencode($bucket) . '/' . $objectPath;

            return $this->response->setJSON([
                'ok'        => true,
                'bucket'    => $bucket,
                'object'    => $objectPath,
                'publicUrl' => $publicUrl,
            ])->setStatusCode(ResponseInterface::HTTP_OK);

        } catch (\Throwable $e) {
            log_message('error', 'guardarPdf: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            return $this->failServerError($e->getMessage());
        }
    }

    // Helpers de respuesta
    private function failBadRequest(string $msg)
    {
        return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => $msg]);
    }
    private function failServerError(string $msg)
    {
        return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => $msg]);
    }
}
