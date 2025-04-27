<?php

namespace MyCommands;

enum Language: string
{
    case ENGLISH = 'English';
    case PORTUGUESE = 'Portuguese';
    case FRENCH = 'French';
    case SPANISH = 'Spanish';

    /**
     * Get all available languages as an array.
     *
     * @return array<string>
     */
    public static function getAllLanguages(): array
    {
        return array_column(self::cases(), 'value');
    }
}