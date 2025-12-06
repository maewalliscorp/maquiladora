<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Backup extends Controller
{
    private function ensureAdminRole(): void
    {
        // Validación de rol omitida a petición del usuario.
        // Se asume que el acceso ya está protegido por el filtro de autenticación en las rutas.
    }

    public function db()
    {
        $this->ensureAdminRole();
        try {
            $db = Database::connect();

            // Obtener listado de tablas
            $tablesResult = $db->query('SHOW TABLES');
            $tables = $tablesResult->getResultArray();

            if (empty($tables)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'No se encontraron tablas en la base de datos para respaldar',
                ]);
            }

            // La clave del nombre de la tabla es la primera columna de SHOW TABLES
            $firstRow = reset($tables);
            $tableKey = array_keys($firstRow)[0];

            $sqlDump = '';
            $sqlDump .= "-- Backup generado desde Sistema de Maquiladoras\n";
            $sqlDump .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $t) {
                $tableName = $t[$tableKey];
                if (!$tableName) {
                    continue;
                }

                // Definición de la tabla
                $createRes = $db->query('SHOW CREATE TABLE `' . $tableName . '`');
                $createRow = $createRes->getRowArray();
                if (!$createRow) {
                    continue;
                }

                $createSql = $createRow['Create Table'] ?? null;
                if (!$createSql) {
                    // Compatibilidad con índices numéricos
                    $createSql = end($createRow);
                }

                $sqlDump .= "-- --------------------------------------------------------\n";
                $sqlDump .= "-- Estructura de tabla para `{$tableName}`\n";
                $sqlDump .= "-- --------------------------------------------------------\n\n";
                $sqlDump .= 'DROP TABLE IF EXISTS `' . $tableName . '`;' . "\n";
                $sqlDump .= $createSql . ";\n\n";

                // Datos de la tabla
                $dataRes = $db->query('SELECT * FROM `' . $tableName . '`');
                $rows = $dataRes->getResultArray();

                if (!empty($rows)) {
                    $sqlDump .= "-- Volcado de datos para la tabla `{$tableName}`\n";
                    foreach ($rows as $row) {
                        $columns = array_keys($row);
                        $colsEscaped = array_map(static function ($c) {
                            return '`' . str_replace('`', '``', $c) . '`';
                        }, $columns);

                        $values = [];
                        foreach ($columns as $col) {
                            $values[] = $db->escape($row[$col]);
                        }

                        $sqlDump .= 'INSERT INTO `' . $tableName . '` (' . implode(', ', $colsEscaped) . ') VALUES (' . implode(', ', $values) . ');' . "\n";
                    }
                    $sqlDump .= "\n";
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $filename = 'backup_db_' . date('Ymd_His') . '.sql';

            return $this->response
                ->setHeader('Content-Type', 'application/sql')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($sqlDump);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Excepción al generar el respaldo de la base de datos',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function files()
    {
        $this->ensureAdminRole();

        $uploadsPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadsPath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Directorio uploads no encontrado',
            ]);
        }

        $filename = 'backup_uploads_' . date('Ymd_His') . '.zip';
        $tmpFile = tempnam(sys_get_temp_dir(), 'bk_up_');

        $zip = new \ZipArchive();
        if ($zip->open($tmpFile, \ZipArchive::OVERWRITE) !== true) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'No se pudo crear el archivo ZIP de respaldo',
            ]);
        }

        $baseLen = strlen($uploadsPath) + 1;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            $filePath = $file->getPathname();
            $localName = substr($filePath, $baseLen);

            if ($file->isDir()) {
                $zip->addEmptyDir($localName);
            } else {
                $zip->addFile($filePath, $localName);
            }
        }

        $zip->close();

        if (!is_file($tmpFile) || filesize($tmpFile) === 0) {
            @unlink($tmpFile);
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'No se pudo generar el respaldo de archivos',
            ]);
        }

        register_shutdown_function(static function () use ($tmpFile): void {
            if (is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        });

        return $this->response->download($tmpFile, null)->setFileName($filename);
    }
}
