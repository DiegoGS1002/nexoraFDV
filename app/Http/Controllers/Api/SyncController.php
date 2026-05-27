<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\TabelaPreco;
use App\Models\CondicaoPagamento;
use App\Models\Promocao;
use Illuminate\Http\Request;

/**
 * SyncController - Responsável pela sincronização offline/online
 *
 * O mobile app baixa dados em lotes e envia pendências.
 */
class SyncController extends Controller
{
    /**
     * Download incremental de dados para o dispositivo.
     * Suporta sincronização incremental via timestamp.
     */
    public function download(Request $request)
    {
        $user = $request->user();
        $since = $request->since ? \Carbon\Carbon::parse($request->since) : null;

        $log = SyncLog::create([
            'user_id' => $user->id,
            'entity' => 'all',
            'direction' => 'download',
            'status' => 'iniciado',
            'iniciado_em' => now(),
        ]);

        try {
            $data = [];

            // Clientes da carteira do vendedor
            $clientesQuery = Cliente::with(['contatos', 'tabelaPreco:id,nome']);
            if ($user->perfil === 'vendedor') {
                $clientesQuery->doVendedor($user->id);
            }
            if ($since) {
                $clientesQuery->where('updated_at', '>=', $since);
            }
            $data['clientes'] = $clientesQuery->get();

            // Produtos ativos
            $produtosQuery = Produto::ativos();
            if ($since) {
                $produtosQuery->where('updated_at', '>=', $since);
            }
            $data['produtos'] = $produtosQuery->get();

            // Tabelas de preço
            $tabelasQuery = TabelaPreco::with('itens');
            if ($since) {
                $tabelasQuery->where('updated_at', '>=', $since);
            }
            $data['tabelas_preco'] = $tabelasQuery->get();

            // Condições de pagamento
            $data['condicoes_pagamento'] = CondicaoPagamento::where('ativo', true)->get();

            // Promoções ativas
            $data['promocoes'] = Promocao::ativas()
                ->with('produtos:id,nome,sku')
                ->get();

            // Títulos financeiros dos clientes do vendedor (abertos)
            $clienteIds = $data['clientes']->pluck('id');
            $data['titulos_financeiros'] = \App\Models\TituloFinanceiro::whereIn('cliente_id', $clienteIds)
                ->whereIn('status', ['aberto', 'vencido'])
                ->get();

            $total = collect($data)->sum(fn($v) => count($v));

            $log->update([
                'status' => 'sucesso',
                'registros_processados' => $total,
                'finalizado_em' => now(),
            ]);

            return response()->json([
                'sync_at' => now()->toIso8601String(),
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'erro', 'mensagem' => $e->getMessage(), 'finalizado_em' => now()]);
            throw $e;
        }
    }

    /**
     * Upload de dados criados/modificados offline.
     */
    public function upload(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'pedidos' => 'nullable|array',
            'visitas' => 'nullable|array',
            'prospects' => 'nullable|array',
            'oportunidades' => 'nullable|array',
        ]);

        $log = SyncLog::create([
            'user_id' => $user->id,
            'entity' => 'upload',
            'direction' => 'upload',
            'status' => 'iniciado',
            'iniciado_em' => now(),
        ]);

        $processados = 0;
        $erros = 0;
        $detalhes = [];

        // Processar pedidos offline
        foreach ($request->pedidos ?? [] as $pedidoData) {
            try {
                // Verifica se já existe (evita duplicata por offline_uuid)
                if (!empty($pedidoData['offline_uuid'])) {
                    $exists = \App\Models\Pedido::where('numero', 'like', '%' . $pedidoData['offline_uuid'] . '%')->exists();
                    if ($exists) {
                        continue;
                    }
                }

                $pedidoController = new PedidoController();
                $pedidoRequest = new \Illuminate\Http\Request();
                $pedidoRequest->merge($pedidoData);
                $pedidoRequest->setUserResolver(fn() => $user);

                // Revalidar preço e estoque ao sincronizar
                $pedidoController->store($pedidoRequest);
                $processados++;
            } catch (\Throwable $e) {
                $erros++;
                $detalhes[] = ['tipo' => 'pedido', 'dados' => $pedidoData, 'erro' => $e->getMessage()];
            }
        }

        // Processar visitas offline
        foreach ($request->visitas ?? [] as $visitaData) {
            try {
                \App\Models\AgendaVisita::create(array_merge($visitaData, ['vendedor_id' => $user->id]));
                $processados++;
            } catch (\Throwable $e) {
                $erros++;
                $detalhes[] = ['tipo' => 'visita', 'dados' => $visitaData, 'erro' => $e->getMessage()];
            }
        }

        // Processar prospects offline
        foreach ($request->prospects ?? [] as $prospectData) {
            try {
                \App\Models\Prospect::create(array_merge($prospectData, [
                    'vendedor_id' => $user->id,
                    'status' => $prospectData['status'] ?? 'prospectando',
                ]));
                $processados++;
            } catch (\Throwable $e) {
                $erros++;
                $detalhes[] = ['tipo' => 'prospect', 'dados' => $prospectData, 'erro' => $e->getMessage()];
            }
        }

        $log->update([
            'status' => $erros > 0 ? ($processados > 0 ? 'sucesso' : 'erro') : 'sucesso',
            'registros_processados' => $processados,
            'registros_erro' => $erros,
            'detalhes' => $detalhes,
            'finalizado_em' => now(),
        ]);

        return response()->json([
            'processados' => $processados,
            'erros' => $erros,
            'detalhes_erros' => $detalhes,
            'sync_at' => now()->toIso8601String(),
        ]);
    }
}


