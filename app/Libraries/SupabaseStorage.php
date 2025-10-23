<?php namespace App\Libraries;

use GuzzleHttp\Client;

class SupabaseStorage
{
    private Client $http;
    private string $baseUrl;
    private string $serviceKey;
    private array  $privateBuckets;

    public function __construct()
    {
        $this->http           = new Client(['http_errors' => false, 'timeout' => 30]);
        $this->baseUrl        = rtrim(getenv('SUPABASE_URL'), '/');
        $this->serviceKey     = getenv('SUPABASE_SERVICE_KEY');
        $priv = trim(getenv('SUPABASE_PRIVATE_BUCKETS') ?: '');
        $this->privateBuckets = $priv ? array_map('trim', explode(',', $priv)) : [];
    }

    private function isPrivate(string $bucket): bool
    {
        return in_array($bucket, $this->privateBuckets, true);
    }

    /** Sube bytes al bucket/ruta indicada (upsert=true) */
    public function put(string $bucket, string $objectPath, string $bytes, string $contentType): array
    {
        $objectPath = ltrim($objectPath, '/');
        $url = "{$this->baseUrl}/storage/v1/object/{$bucket}/{$objectPath}";

        $res = $this->http->post($url, [
            'headers' => [
                'Authorization'  => "Bearer {$this->serviceKey}",
                'apikey'         => $this->serviceKey,
                'Content-Type'   => $contentType,
                'x-upsert'       => 'true',
                'Content-Length' => strlen($bytes),
                'Accept'         => 'application/json',
            ],
            'body' => $bytes,
        ]);

        if ($res->getStatusCode() >= 200 && $res->getStatusCode() < 300) {
            return [
                'ok'   => true,
                'path' => $objectPath,
                'url'  => $this->publicUrl($bucket, $objectPath),
            ];
        }

        return [
            'ok'  => false,
            'msg' => "Supabase upload error {$res->getStatusCode()}: " . (string)$res->getBody(),
        ];
    }

    /** URL pública directa o firmada (1h) si el bucket es privado */
    public function publicUrl(string $bucket, string $objectPath): string
    {
        $objectPath = ltrim($objectPath, '/');

        if (!$this->isPrivate($bucket)) {
            return "{$this->baseUrl}/storage/v1/object/public/{$bucket}/{$objectPath}";
        }

        // Firmada por 3600 segundos
        $signUrl = "{$this->baseUrl}/storage/v1/object/sign/{$bucket}/{$objectPath}";
        $res = $this->http->post($signUrl, [
            'headers' => [
                'Authorization' => "Bearer {$this->serviceKey}",
                'apikey'        => $this->serviceKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => ['expiresIn' => 3600],
        ]);

        $data = json_decode((string)$res->getBody(), true);
        return !empty($data['signedURL']) ? $this->baseUrl . $data['signedURL'] : '';
    }
}
