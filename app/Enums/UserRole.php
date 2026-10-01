<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case MagazijnMedewerker = 'magazijn_medewerker';
    case Klant = 'klant';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::MagazijnMedewerker => 'Medewerker',
            self::Klant => 'Klant',
        };
    }
}
