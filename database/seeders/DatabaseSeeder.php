<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Tata Usaha',
            'email' => 'admin@mas.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->call([
            DocumentTypeSeeder::class,
            StudentSeeder::class,
            DocumentRequestSeeder::class,
        ]);
    }
}
