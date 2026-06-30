<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Provider;
use Inertia\Inertia;

class ExportController extends Controller
{
    public function page()
    {
        return Inertia::render('Admin/Exportacoes');
    }

    public function prestadores()
    {
        $providers = Provider::with('services')
            ->orderBy('name')
            ->get([
                'id', 'name', 'cpf', 'email', 'phone',
                'status', 'has_license', 'cnh_status',
                'city', 'state', 'mei_cnpj',
                'created_at', 'approved_at',
            ]);

        $filename = 'prestadores_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($providers) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para o Excel abrir corretamente
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Nome', 'CPF', 'E-mail', 'Telefone',
                'Status', 'CNH', 'Status CNH',
                'MEI CNPJ', 'Serviços',
                'Cidade', 'Estado',
                'Cadastro', 'Aprovação',
            ], ';');

            $statusMap = [
                'approved' => 'Aprovado',
                'pending'  => 'Pendente',
                'rejected' => 'Rejeitado',
            ];

            foreach ($providers as $p) {
                fputcsv($out, [
                    $p->id,
                    $p->name,
                    $p->cpf,
                    $p->email,
                    $p->phone ?? '',
                    $statusMap[$p->status] ?? $p->status,
                    $p->has_license ? 'Sim' : 'Não',
                    $p->cnh_status ? ($statusMap[$p->cnh_status] ?? $p->cnh_status) : '',
                    $p->mei_cnpj ?? '',
                    $p->services->pluck('name')->join(', '),
                    $p->city ?? '',
                    $p->state ?? '',
                    $p->created_at?->format('d/m/Y') ?? '',
                    $p->approved_at?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function empresas()
    {
        $companies = Company::orderBy('trade_name')
            ->get([
                'id', 'trade_name', 'cnpj', 'email', 'phone',
                'status', 'city', 'state',
                'created_at', 'approved_at',
            ]);

        $filename = 'empresas_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ];

        $callback = function () use ($companies) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Razão Social', 'CNPJ', 'E-mail', 'Telefone',
                'Status', 'Cidade', 'Estado',
                'Cadastro', 'Aprovação',
            ], ';');

            $statusMap = [
                'approved' => 'Aprovada',
                'pending'  => 'Pendente',
                'rejected' => 'Rejeitada',
            ];

            foreach ($companies as $c) {
                fputcsv($out, [
                    $c->id,
                    $c->trade_name,
                    $c->cnpj,
                    $c->email,
                    $c->phone ?? '',
                    $statusMap[$c->status] ?? $c->status,
                    $c->city ?? '',
                    $c->state ?? '',
                    $c->created_at?->format('d/m/Y') ?? '',
                    $c->approved_at?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
