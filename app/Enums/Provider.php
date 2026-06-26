<?php

namespace App\Enums;

enum Provider: string
{
    case NewsApi = 'newsapi';
    case Guardian = 'guardian';
    case Nyt = 'nyt';

    public function label(): string
    {
        return match ($this) {
            self::NewsApi => 'NewsAPI',
            self::Guardian => 'The Guardian',
            self::Nyt => 'New York Times',
        };
    }
}
