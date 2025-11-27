<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF for Purchase Order (OC)
     * 
     * @param array $ocData OC data from database
     * @return string|false Path to generated PDF or false on failure
     */
    public function generateOCPdf(array $ocData): string|false
    {
        try {
            // Ensure uploads/pdfs directory exists
            $pdfDir = WRITEPATH . 'uploads/pdfs/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            // Generate filename
            $timestamp = time();
            $filename = "oc_{$ocData['id']}_{$timestamp}.pdf";
            $filepath = $pdfDir . $filename;

            // Load HTML template
            $html = view('pdfs/oc_template', ['oc' => $ocData]);

            // Generate PDF
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();

            // Save to file
            file_put_contents($filepath, $this->dompdf->output());

            // Return relative path for database storage
            return 'uploads/pdfs/' . $filename;
        } catch (\Throwable $e) {
            log_message('error', '[PDF Generator] ' . $e->getMessage());
            return false;
        }
    }
}
