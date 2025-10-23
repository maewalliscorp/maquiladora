<?php namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Libraries\SupabaseStorage;

class PdfSupabaseController extends BaseController
{
    private function bucketByKind(string $kind): string
    {
        $map = [
            'Doc_Embarque' => getenv('SUPABASE_BUCKET_DOC_EMBARQUE') ?: 'Doc_Embarque',
            'Etiqueta'     => getenv('SUPABASE_BUCKET_ETIQUETA')     ?: 'Etiqueta',
            'Facturas'     => getenv('SUPABASE_BUCKET_FACTURAS')     ?: 'Facturas',
            'Foto_Usuario' => getenv('SUPABASE_BUCKET_FOTO_USUARIO') ?: 'Foto_Usuario',
            'Img_Material' => getenv('SUPABASE_BUCKET_IMG_MATERIAL') ?: 'Img_Material',
        ];
        return $map[$kind] ?? $map['Doc_Embarque'];
    }

    /** POST /supabase/guardar  { html, filename?, kind?, prefix? } */
    public function guardar()
    {
        $html     = $this->request->getPost('html');
        if (!$html) {
            return $this->response->setStatusCode(400)->setJSON(['ok'=>false,'msg'=>'Falta HTML']);
        }

        $filename = $this->request->getPost('filename') ?: ('doc-'.date('Ymd-His').'.pdf');
        $kind     = $this->request->getPost('kind') ?: 'Doc_Embarque';     // <- por defecto Doc_Embarque
        $prefix   = trim($this->request->getPost('prefix') ?? '', '/');    // subcarpeta opcional

        // Render PDF
        $opt = new Options(); $opt->set('isRemoteEnabled', true);
        $dom = new Dompdf($opt);
        $dom->loadHtml($html);
        $dom->setPaper('letter', 'portrait');
        $dom->render();
        $pdf = $dom->output();

        // Bucket y ruta
        $bucket    = $this->bucketByKind($kind);
        $objectKey = ($prefix ? "{$prefix}/" : '') . ltrim($filename, '/');

        $store = new SupabaseStorage();
        $res   = $store->put($bucket, $objectKey, $pdf, 'application/pdf');

        if (!($res['ok'] ?? false)) {
            return $this->response->setStatusCode(500)->setJSON($res);
        }

        return $this->response->setJSON([
            'ok'     => true,
            'bucket' => $bucket,
            'path'   => $objectKey,
            'url'    => $res['url'],
            'msg'    => "PDF subido a {$bucket}",
        ]);
    }

    public function ping()
    {
        return $this->response->setJSON(['pong'=>true, 'time'=>date('c')]);
    }
}
