<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Kita;
use App\Models\User;
use App\Models\TrainingCategory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Kitas
        $kitas = [
            ['name' => 'Sonnenschein', 'short_code' => 'SONN', 'address' => 'Sonnenstraße 1, 12345 Musterstadt', 'phone' => '030 1234560', 'email' => 'sonnenschein@kita-traeger.de', 'min_first_aid' => 2],
            ['name' => 'Regenbogen', 'short_code' => 'REGEN', 'address' => 'Regenbogenweg 5, 12345 Musterstadt', 'phone' => '030 1234561', 'email' => 'regenbogen@kita-traeger.de', 'min_first_aid' => 2],
            ['name' => 'Schmetterlinge', 'short_code' => 'SCHM', 'address' => 'Schmetterlingsallee 10, 12345 Musterstadt', 'phone' => '030 1234562', 'email' => 'schmetterlinge@kita-traeger.de', 'min_first_aid' => 2],
            ['name' => 'Sternchen', 'short_code' => 'STERN', 'address' => 'Sternstraße 3, 12345 Musterstadt', 'phone' => '030 1234563', 'email' => 'sternchen@kita-traeger.de', 'min_first_aid' => 2],
            ['name' => 'Löwenzahn', 'short_code' => 'LOEWE', 'address' => 'Löwenzahnweg 7, 12345 Musterstadt', 'phone' => '030 1234564', 'email' => 'loewenzahn@kita-traeger.de', 'min_first_aid' => 2],
        ];

        $createdKitas = [];
        foreach ($kitas as $kitaData) {
            $createdKitas[] = Kita::create($kitaData);
        }

        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@kita-traeger.de',
            'password' => Hash::make('Admin123!'),
            'role' => 'ADMIN',
            'kita_id' => null,
        ]);

        // Create Kita Managers (one per kita)
        $managerNames = [
            'SONN' => ['name' => 'Maria Sonnenschein', 'email' => 'leitung.sonn@kita-traeger.de'],
            'REGEN' => ['name' => 'Thomas Regenbogen', 'email' => 'leitung.regen@kita-traeger.de'],
            'SCHM' => ['name' => 'Sabine Schmetterling', 'email' => 'leitung.schm@kita-traeger.de'],
            'STERN' => ['name' => 'Klaus Sternberg', 'email' => 'leitung.stern@kita-traeger.de'],
            'LOEWE' => ['name' => 'Andrea Löwenstein', 'email' => 'leitung.loewe@kita-traeger.de'],
        ];

        foreach ($createdKitas as $kita) {
            $managerData = $managerNames[$kita->short_code];
            User::create([
                'name' => $managerData['name'],
                'email' => $managerData['email'],
                'password' => Hash::make('Manager123!'),
                'role' => 'KITA_MANAGER',
                'kita_id' => $kita->id,
            ]);
        }

        // Create Training Categories
        TrainingCategory::create([
            'name' => 'Erste Hilfe',
            'description' => 'Erste-Hilfe-Kurs für Kita-Personal inkl. Säuglings- und Kinderwiederbelebung',
            'validity_months' => 24,
            'is_first_aid' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        TrainingCategory::create([
            'name' => 'Brandschutz',
            'description' => 'Brandschutzunterweisung und Umgang mit Feuerlöschern',
            'validity_months' => 12,
            'is_first_aid' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        TrainingCategory::create([
            'name' => 'Datenschutz',
            'description' => 'DSGVO-Schulung und datenschutzrechtliche Grundlagen im Kita-Betrieb',
            'validity_months' => 24,
            'is_first_aid' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        TrainingCategory::create([
            'name' => 'Kinderschutz',
            'description' => 'Schulung zum Kinderschutz, §8a SGB VIII und Kindeswohlgefährdung',
            'validity_months' => 36,
            'is_first_aid' => false,
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}
