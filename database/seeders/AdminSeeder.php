<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Mesmos hashes do banco original — credenciais preservadas
        DB::table('admins')->upsert([
            ['id' => 1, 'email' => 'admin1@freeflex.com.br', 'password' => '$2y$10$wBYV0yG31mhyMMXzgMquFeEWQ6zHWAeeUFK8Or4OBkOwiQA0Cw2VW', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'email' => 'admin2@freeflex.com.br', 'password' => '$2y$10$NYSWPNMcW2cYCM8qTDvmKO66VEZTsmnETEiebboflf6I3mBvl/a4e', 'created_at' => now(), 'updated_at' => now()],
        ], ['id'], ['email', 'password', 'updated_at']);

        $this->command->info('✓ Admins: admin1@freeflex.com.br | admin2@freeflex.com.br');
    }
}
