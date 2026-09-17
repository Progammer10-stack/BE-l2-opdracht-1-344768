<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $medewerker = User::firstOrNew(['email' => 'magazijn@example.com']);
        $medewerker->name = 'Magazijn Medewerker';
        $medewerker->password = Hash::make('password');
        $medewerker->email_verified_at = now();
        $medewerker->role = UserRole::MagazijnMedewerker;
        $medewerker->save();
    }
}
