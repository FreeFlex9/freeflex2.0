<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Admins do sistema FreeFlex (mesmos hashes do banco original)
        $admins = [
            [
                'id'    => 1,
                'email' => 'admin1@freeflex.com.br',
                'senha' => '$2y$10$wBYV0yG31mhyMMXzgMquFeEWQ6zHWAeeUFK8Or4OBkOwiQA0Cw2VW',
            ],
            [
                'id'    => 2,
                'email' => 'admin2@freeflex.com.br',
                'senha' => '$2y$10$NYSWPNMcW2cYCM8qTDvmKO66VEZTsmnETEiebboflf6I3mBvl/a4e',
            ],
        ];

        foreach ($admins as $admin) {
            DB::table('admins')->updateOrInsert(
                ['id' => $admin['id']],
                ['email' => $admin['email'], 'senha' => $admin['senha']]
            );
        }

        $this->command->info('Admins inseridos: admin1@freeflex.com.br e admin2@freeflex.com.br');
    }
}
