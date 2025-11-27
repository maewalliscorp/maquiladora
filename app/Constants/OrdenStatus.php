<?php
namespace App\Constants;

class OrdenStatus
{
    public const EN_PROCESO = 'En proceso';
    public const EN_CORTE = 'En corte';
    public const PAUSADA = 'Pausada';
    public const COMPLETADA = 'Completada';
    public const ACEPTADA = 'Aceptada';
    public const FINALIZADA = 'Finalizada';

    // Lista de todos los estatus válidos para validación
    public static function getAll(): array
    {
        return [
            self::EN_PROCESO,
            self::EN_CORTE,
            self::PAUSADA,
            self::COMPLETADA,
            self::ACEPTADA,
            self::FINALIZADA,
        ];
    }
}
