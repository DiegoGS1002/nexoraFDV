<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::ativos();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nome', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhere('erp_code', 'like', "%$s%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('tabela_preco_id')) {
            $tabelaId = $request->tabela_preco_id;
            $query->whereHas('tabelasPrecoItens', fn($q) => $q->where('tabela_preco_id', $tabelaId));
        }

        $produtos = $query->orderBy('nome')->paginate($request->per_page ?? 30);

        return response()->json($produtos);
    }

    public function show(Produto $produto)
    {
        $produto->load('tabelasPreco', 'promocoes');

        return response()->json($produto);
    }

    public function precos(Request $request, Produto $produto)
    {
        $request->validate(['tabela_preco_id' => 'required|exists:tabelas_preco,id']);

        $item = $produto->tabelasPrecoItens()
            ->where('tabela_preco_id', $request->tabela_preco_id)
            ->first();

        return response()->json([
            'produto_id' => $produto->id,
            'preco_base' => $produto->preco_base,
            'preco_tabela' => $item?->preco ?? $produto->preco_base,
            'desconto_maximo' => $item?->desconto_maximo ?? 0,
            'preco_minimo' => $produto->preco_minimo,
            'estoque_disponivel' => $produto->estoque_disponivel,
        ]);
    }
}

