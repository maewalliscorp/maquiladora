<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class StorageProxy extends BaseController
{
    public function list(): ResponseInterface
    {
        $bucket = trim($this->request->getPost('bucket') ?? '');
        $prefix = $this->request->getPost('prefix') ?? '';

        if ($bucket === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'bucket required']);
        }

        $baseUrl   = rtrim(env('SUPABASE_URL') ?? '', '/');
        $serviceKey= env('SUPABASE_SERVICE_ROLE_KEY') ?? env('SUPABASE_SERVICE_KEY') ?? '';

        if ($baseUrl === '' || $serviceKey === '') {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Supabase env missing']);
        }

        $url = $baseUrl . '/storage/v1/object/list/' . rawurlencode($bucket);

        $client = \Config\Services::curlrequest();
        try {
            $resp = $client->post($url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'apikey'        => $serviceKey,
                    'Authorization' => 'Bearer ' . $serviceKey,
                ],
                'body' => json_encode([
                    'prefix' => $prefix,         // '' = raíz
                    'limit'  => 1000,
                    'offset' => 0,
                    'sortBy' => ['column' => 'name', 'order' => 'asc'],
                ], JSON_UNESCAPED_SLASHES),
                'http_errors' => false,
                'timeout'     => 10,
            ]);

            $status = $resp->getStatusCode();
            $data   = json_decode($resp->getBody(), true);

            return $this->response->setStatusCode($status)->setJSON($data);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'proxy_failed',
                'msg'   => $e->getMessage(),
            ]);
        }
    }
}
