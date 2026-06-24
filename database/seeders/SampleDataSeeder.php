<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Serviços ────────────────────────────────────────────────────────
        $services = Service::upsert([
            ['name' => 'Motorista',          'hourly_rate' => 35.00, 'provider_rate' => 25.00, 'requires_license' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ajudante de Carga',  'hourly_rate' => 22.00, 'provider_rate' => 16.00, 'requires_license' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Operador de Empilhadeira', 'hourly_rate' => 40.00, 'provider_rate' => 29.00, 'requires_license' => true,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Estoquista',         'hourly_rate' => 20.00, 'provider_rate' => 14.00, 'requires_license' => false, 'created_at' => now(), 'updated_at' => now()],
        ], ['name'], ['hourly_rate', 'provider_rate', 'requires_license', 'updated_at']);

        $svcMotorista   = Service::where('name', 'Motorista')->first();
        $svcAjudante    = Service::where('name', 'Ajudante de Carga')->first();
        $svcEstoquista  = Service::where('name', 'Estoquista')->first();

        // ─── Empresas ────────────────────────────────────────────────────────
        $emp1 = Company::firstOrCreate(['cnpj' => '12.345.678/0001-90'], [
            'trade_name'    => 'Logística Rápida Ltda',
            'legal_name'    => 'Logística Rápida Comércio e Transportes Ltda',
            'email'         => 'contato@logisticarapida.com.br',
            'password'      => Hash::make('empresa123'),
            'phone'         => '(11) 98765-4321',
            'zip_code'      => '01310-100',
            'street'        => 'Avenida Paulista',
            'number'        => '1000',
            'neighborhood'  => 'Bela Vista',
            'city'          => 'São Paulo',
            'state'         => 'SP',
            'status'        => 'approved',
        ]);

        $emp2 = Company::firstOrCreate(['cnpj' => '98.765.432/0001-10'], [
            'trade_name'    => 'Distribuidora Norte',
            'email'         => 'rh@distribuidoranorte.com.br',
            'password'      => Hash::make('empresa123'),
            'phone'         => '(92) 3232-1234',
            'zip_code'      => '69010-060',
            'street'        => 'Rua Sete de Setembro',
            'number'        => '250',
            'neighborhood'  => 'Centro',
            'city'          => 'Manaus',
            'state'         => 'AM',
            'status'        => 'pending',
        ]);

        // ─── Prestadores ─────────────────────────────────────────────────────
        $prest1 = Provider::firstOrCreate(['cpf' => '123.456.789-00'], [
            'name'        => 'Carlos Eduardo Silva',
            'email'       => 'carlos@email.com',
            'password'    => Hash::make('prestador123'),
            'phone'       => '(11) 97777-8888',
            'has_license' => true,
            'is_digital_license' => false,
            'license_number' => 'SP-123456789',
            'status'      => 'approved',
        ]);

        $prest2 = Provider::firstOrCreate(['cpf' => '987.654.321-00'], [
            'name'        => 'Ana Paula Ferreira',
            'email'       => 'ana@email.com',
            'password'    => Hash::make('prestador123'),
            'phone'       => '(11) 96666-7777',
            'has_license' => false,
            'status'      => 'pending',
        ]);

        $prest3 = Provider::firstOrCreate(['cpf' => '456.789.123-00'], [
            'name'        => 'Roberto Melo',
            'email'       => 'roberto@email.com',
            'password'    => Hash::make('prestador123'),
            'phone'       => '(21) 95555-6666',
            'has_license' => true,
            'is_digital_license' => true,
            'status'      => 'approved',
        ]);

        // ─── Demandas ────────────────────────────────────────────────────────
        $dem1 = Demand::firstOrCreate(
            ['company_id' => $emp1->id, 'title' => 'Motorista para entrega SP capital'],
            [
                'service_id'     => $svcMotorista->id,
                'description'    => 'Entrega de mercadorias na região da Grande São Paulo. Caminhão 3/4 fornecido pela empresa.',
                'date'           => now()->addDays(3)->format('Y-m-d'),
                'start_time'     => '07:00',
                'end_time'       => '17:00',
                'slots_needed'   => 2,
                'slots_confirmed'=> 1,
                'city'           => 'São Paulo',
                'state'          => 'SP',
                'status'         => 'open',
            ]
        );

        $dem2 = Demand::firstOrCreate(
            ['company_id' => $emp1->id, 'title' => 'Ajudantes para carga e descarga'],
            [
                'service_id'     => $svcAjudante->id,
                'description'    => 'Carga e descarga de pallets no armazém. EPI fornecido.',
                'date'           => now()->addDays(5)->format('Y-m-d'),
                'start_time'     => '06:00',
                'end_time'       => '14:00',
                'slots_needed'   => 3,
                'slots_confirmed'=> 0,
                'city'           => 'São Paulo',
                'state'          => 'SP',
                'status'         => 'open',
            ]
        );

        // ─── Propostas ───────────────────────────────────────────────────────
        Proposal::firstOrCreate(
            ['demand_id' => $dem1->id, 'provider_id' => $prest1->id],
            ['message' => 'Tenho experiência com entregas na Grande SP. Disponível.', 'status' => 'accepted']
        );

        Proposal::firstOrCreate(
            ['demand_id' => $dem1->id, 'provider_id' => $prest3->id],
            ['message' => 'Posso fazer, tenho CNH E e veículo próprio também.', 'status' => 'pending_admin_approval']
        );

        Proposal::firstOrCreate(
            ['demand_id' => $dem2->id, 'provider_id' => $prest1->id],
            ['message' => 'Disponível também para carga e descarga.', 'status' => 'pending_admin_approval']
        );

        // ─── Config SMTP (placeholder) ───────────────────────────────────────
        DB::table('smtp_settings')->upsert([
            ['id' => 1, 'smtp_host' => 'smtp.gmail.com', 'smtp_port' => 587, 'smtp_secure' => 'tls', 'smtp_username' => '', 'smtp_password' => '', 'from_email' => 'noreply@freeflex.com.br', 'from_name' => 'FreeFlex', 'created_at' => now(), 'updated_at' => now()],
        ], ['id'], ['smtp_host', 'smtp_port', 'smtp_secure', 'from_name', 'updated_at']);

        $this->command->info('✓ Serviços: Motorista, Ajudante de Carga, Operador de Empilhadeira, Estoquista');
        $this->command->info('✓ Empresas: Logística Rápida (aprovada) | Distribuidora Norte (pendente)');
        $this->command->info('✓ Prestadores: Carlos (aprovado) | Ana (pendente) | Roberto (aprovado)');
        $this->command->info('✓ Demandas e propostas de teste criadas');
        $this->command->info('');
        $this->command->info('Credenciais de acesso:');
        $this->command->info('  Admin 1: admin1@freeflex.com.br (senha original preservada)');
        $this->command->info('  Admin 2: admin2@freeflex.com.br (senha original preservada)');
        $this->command->info('  Empresa: contato@logisticarapida.com.br / empresa123');
        $this->command->info('  Prestador: carlos@email.com / prestador123');
    }
}
