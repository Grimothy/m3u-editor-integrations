<?php

namespace App\Enums;

enum MediaType: string
{
    case Url = 'url';
    case LocalFile = 'local_file';

    public function getColor(): string
    {
        return match ($this) {
            self::Url => 'info',
            self::LocalFile => 'success',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Url => 'URL',
            self::LocalFile => 'Local File',
        };
    }
}
