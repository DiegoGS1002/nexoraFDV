<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TituloFinanceiro;
use Illuminate\Http\Request;

class TituloFinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = TituloFinanceiro::with('cliente:id,razao_social,nome_fantasia');

        if ($user->perfil === 'vendedor') {
            $query->whereHas('cliente', fn($q) => $q->where('vendedor_id', $user->id));
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('vencidos')) {
            $query->vencidos();
        }

        return response()->json(
            $query->orderBy('data_vencimento')->paginate($request->per_page ?? 20)
        );
    }

    public function porCliente(Request $request, int $clienteId)
    {
        $user = $request->user();

        if ($user->perfil === 'vendedor') {
            $cliente = \App\Models\Cliente::where('id', $clienteId)
                ->where('vendedor_id', $user->id)
                ->firstOrFail();
        }

        $titulos = TituloFinanceiro::where('cliente_id', $clienteId)
            ->orderBy('data_vencimento')
            ->get();

        $resumo = [
            'total_aberto' => $titulos->where('status', 'aberto')->sum('valor'),
            'total_vencido' => $titulos->filter(fn($t) => $t->isVencido())->sum('valor'),
            'total_pago' => $titulos->where('status', 'pago')->sum('valor'),
        ];

        return response()->json(['resumo' => $resumo, 'titulos' => $titulos]);
    }

    public function registrarPagamento(Request $request, TituloFinanceiro $tituloFinanceiro)
    {
        if (!in_array($request->user()->perfil, ['financeiro', 'admin', 'gerente'])) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $request->validate([
            'valor_pago' => 'required|numeric|min:0.01',
            'data_pagamento' => 'required|date',
            'forma_pagamento' => 'required|string',
        ]);

        $tituloFinanceiro->update([
            'valor_pago' => $request->valor_pago,
            'data_pagamento' => $request->data_pagamento,
            'forma_pagamento' => $request->forma_pagamento,
            'status' => 'pago',
        ]);

        return response()->json(['message' => 'Pagamento registrado.', 'titulo' => $tituloFinanceiro]);
    }
}

