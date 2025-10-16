<?php
namespace App\Models;

use CodeIgniter\Model;

class DocumentoEnvioModel extends Model
{
    protected $table         = 'doc_embarque';   // << usa tu tabla real
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    // Dejamos un set amplio; el controlador filtra contra columnas reales,
    // así no truena si alguna no existe aún.
    protected $protectFields = true;
    protected $allowedFields = [
        'embarqueId', 'tipo', 'archivoRuta',    // columnas que TIENES
        'numero', 'fecha', 'estado', 'urlPdf', 'archivoPdf' // opcionales
    ];
}
