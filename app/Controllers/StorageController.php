<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class StorageController extends BaseController
{
    /**
     * Recibe: multipart/form-data con:
     *  - file:     PDF (Blob)
     *  - name:     nombre de archivo (ej. EMB-2025-0012.pdf)
     *  - bucket:   opcional (por defecto Doc_Embarque)
     *
     * Devuelve: { ok:bool, url?:string, name?:string, error?:string }
     */
    public function guardarPdf(): ResponseInterface
    {
        $bucket = $this->request->getPost('bucket')
            ?: env('SUPABASE_BUCKET_DOC_EMBARQUE', env('SUPABASE_BUCKET', 'Doc_Embarque'));

        $name = trim($this->request->getPost('name') ?? '');
        if ($name === '') {
            $name = 'doc_' . date('Ymd_His') . '.pdf';
        }
        // Asegura extensión .pdf
        if (!str_ends_with(strtolower($name), '.pdf')) {
            $name .= '.pdf';
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok'    => false,
                'error' => 'Falta archivo'
            ]);
        }

        // Lee bytes del temporal
        $bytes = file_get_contents($file->getTempName());

        // Configuración Supabase
        $baseUrl   = rtrim((string) env('SUPABASE_URL'), '/');
        $apiUrl    = "{$baseUrl}/storage/v1/object/{$bucket}/{$name}";
        // Usa SERVICE_ROLE_KEY si existe; si no, usa SERVICE_KEY (ambas funcionan en backend)
        $token     = env('SUPABASE_SERVICE_ROLE_KEY', env('SUPABASE_SERVICE_KEY'));

        try {
            $client = service('curlrequest', ['timeout' => 20]);

            $res = $client->request('POST', $apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/pdf',
                    'X-Upsert'      => 'true', // sobre-escribe si ya existe
                ],
                'body' => $bytes,
            ]);

            $status = $res->getStatusCode();

            if ($status >= 200 && $status < 300) {
                // Si el bucket es público: URL pública directa
                $publicUrl = "{$baseUrl}/storage/v1/object/public/{$bucket}/{$name}";
                return $this->response->setJSON([
                    'ok'   => true,
                    'name' => $name,
                    'url'  => $publicUrl,
                ]);
            }

            return $this->response->setStatusCode(500)->setJSON([
                'ok'     => false,
                'status' => $status,
                'body'   => $res->getBody(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok'    => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
