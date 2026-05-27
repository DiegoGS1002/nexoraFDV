<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Pedido::with(['cliente:id,razao_social,nome_fantasia', 'vendedor:id,name'])
            ->withCount('itens');

        if ($user->perfil === 'vendedor') {
            $query->doVendedor($user->id);
        } elseif ($user->isSupervisor() && !$user->isGerente()) {
            $subIds = $user->subordinados()->pluck('id')->push($user->id);
            $query->whereIn('vendedor_id', $subIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('data_de')) {
            $query->whereDate('data_pedido', '>=', $request->data_de);
        }

        if ($request->filled('data_ate')) {
            $query->whereDate('data_pedido', '<=', $request->data_ate);
        }

        return response()->json(
            $query->orderByDesc('data_pedido')->paginate($request->per_page ?? 20)
        );
    }

    public function show(Request $request, Pedido $pedido)
    {
        $this->authorize($request->user(), $pedido);

        $pedido->load([
            'cliente',
            'vendedor:id,name',
            'condicaoPagamento',
            'tabelaPreco:id,nome',
            'itens.produto:id,nome,sku,unidade,imagem_url',
        ]);

        return response()->json($pedido);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'condicao_pagamento_id' => 'required|exists:condicoes_pagamento,id',
            'tabela_preco_id' => 'nullable|exists:tabelas_preco,id',
            'data_pedido' => 'nullable|date',
            'data_entrega_prevista' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'obs_interna' => 'nullable|string',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:0.001',
            'itens.*.preco_unitario' => 'required|numeric|min:0',
            'itens.*.desconto_percentual' => 'nullable|numeric|min:0|max:100',
            'itens.*.desconto_valor' => 'nullable|numeric|min:0',
            'itens.*.acrescimo_percentual' => 'nullable|numeric|min:0',
            'itens.*.observacoes' => 'nullable|string',
        ]);

        $user = $request->user();

        // Valida bloqueio financeiro do cliente
        $cliente = \App\Models\Cliente::findOrFail($data['cliente_id']);
        if ($cliente->isBloqueado()) {
            return response()->json(['message' => 'Cliente bloqueado. Pedido não permitido.'], 422);
        }

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'cliente_id' => $data['cliente_id'],
                'vendedor_id' => $user->id,
                'condicao_pagamento_id' => $data['condicao_pagamento_id'],
                'tabela_preco_id' => $data['tabela_preco_id'] ?? null,
                'data_pedido' => $data['data_pedido'] ?? now()->toDateString(),
                'data_entrega_prevista' => $data['data_entrega_prevista'] ?? null,
                'status' => 'rascunho',
                'observacoes' => $data['observacoes'] ?? null,
                'obs_interna' => $data['obs_interna'] ?? null,
                'subtotal' => 0,
                'desconto_total' => 0,
                'acrescimo_total' => 0,
                'total' => 0,
            ]);

            foreach ($data['itens'] as $itemData) {
                $produto = \App\Models\Produto::findOrFail($itemData['produto_id']);
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'produto_id' => $produto->id,
                    'produto_erp_code' => $produto->erp_code,
                    'produto_nome' => $produto->nome,
                    'unidade' => $produto->unidade,
                    'quantidade' => $itemData['quantidade'],
                    'preco_unitario' => $itemData['preco_unitario'],
                    'desconto_percentual' => $itemData['desconto_percentual'] ?? 0,
                    'desconto_valor' => $itemData['desconto_valor'] ?? 0,
                    'acrescimo_percentual' => $itemData['acrescimo_percentual'] ?? 0,
                    'observacoes' => $itemData['observacoes'] ?? null,
                ]);
            }

            $pedido->load('itens');
            $pedido->recalcularTotais();

            DB::commit();

            return response()->json($pedido->load('itens.produto'), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirmar(Request $request, Pedido $pedido)
    {
        $this->authorize($request->user(), $pedido);

        if ($pedido->status !== 'rascunho') {
            return response()->json(['message' => 'Pedido não pode ser confirmado neste status.'], 422);
        }

        $pedido->update(['status' => 'confirmado']);

        return response()->json(['message' => 'Pedido confirmado.', 'pedido' => $pedido]);
    }

    public function cancelar(Request $request, Pedido $pedido)
    {
        $this->authorize($request->user(), $pedido);

        if (in_array($pedido->status, ['faturado', 'entregue'])) {
            return response()->json(['message' => 'Pedido não pode ser cancelado.'], 422);
        }

        $pedido->update(['status' => 'cancelado']);

        return response()->json(['message' => 'Pedido cancelado.', 'pedido' => $pedido]);
    }

    public function aprovar(Request $request, Pedido $pedido)
    {
        if (!$request->user()->isSupervisor()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $pedido->update(['status' => 'aprovado']);

        return response()->json(['message' => 'Pedido aprovado.', 'pedido' => $pedido]);
    }

    private function authorize($user, Pedido $pedido): void
    {
        if ($user->perfil === 'vendedor' && $user->id !== $pedido->vendedor_id) {
            abort(403);
        }
    }
}

