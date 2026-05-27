<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Cliente::with(['vendedor:id,name', 'tabelaPreco:id,nome']);

        // Vendedores só veem sua carteira
        if ($user->perfil === 'vendedor') {
            $query->doVendedor($user->id);
        } elseif ($user->isSupervisor() && !$user->isGerente()) {
            $subIds = $user->subordinados()->pluck('id')->push($user->id);
            $query->whereIn('vendedor_id', $subIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'like', "%$search%")
                  ->orWhere('nome_fantasia', 'like', "%$search%")
                  ->orWhere('cnpj_cpf', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clientes = $query->orderBy('razao_social')->paginate($request->per_page ?? 20);

        return response()->json($clientes);
    }

    public function show(Request $request, Cliente $cliente)
    {
        $this->authorizeVendedor($request->user(), $cliente->vendedor_id);

        $cliente->load([
            'vendedor:id,name',
            'tabelaPreco:id,nome',
            'contatos',
            'titulosFinanceiros' => fn($q) => $q->whereNull('data_pagamento')->orderBy('data_vencimento'),
        ]);

        return response()->json($cliente);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj_cpf' => 'nullable|string|max:18',
            'ie' => 'nullable|string|max:30',
            'tipo' => 'in:juridica,fisica',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'limite_credito' => 'nullable|numeric|min:0',
            'tabela_preco_id' => 'nullable|exists:tabelas_preco,id',
            'vendedor_id' => 'nullable|exists:users,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Vendedor só pode criar cliente para si mesmo
        if ($request->user()->perfil === 'vendedor') {
            $data['vendedor_id'] = $request->user()->id;
        }

        $cliente = Cliente::create($data);

        return response()->json($cliente, 201);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->authorizeVendedor($request->user(), $cliente->vendedor_id);

        $data = $request->validate([
            'razao_social' => 'sometimes|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj_cpf' => 'nullable|string|max:18',
            'ie' => 'nullable|string|max:30',
            'tipo' => 'in:juridica,fisica',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'limite_credito' => 'nullable|numeric|min:0',
            'tabela_preco_id' => 'nullable|exists:tabelas_preco,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $cliente->update($data);

        return response()->json($cliente);
    }

    public function bloquear(Request $request, Cliente $cliente)
    {
        $request->validate(['motivo' => 'required|string|max:255']);

        $user = $request->user();
        if (!$user->isSupervisor()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $cliente->update(['status' => 'bloqueado', 'motivo_bloqueio' => $request->motivo]);

        return response()->json(['message' => 'Cliente bloqueado.', 'cliente' => $cliente]);
    }

    public function desbloquear(Request $request, Cliente $cliente)
    {
        if (!$request->user()->isSupervisor()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $cliente->update(['status' => 'ativo', 'motivo_bloqueio' => null]);

        return response()->json(['message' => 'Cliente desbloqueado.', 'cliente' => $cliente]);
    }

    public function timeline(Request $request, Cliente $cliente)
    {
        $this->authorizeVendedor($request->user(), $cliente->vendedor_id);

        return response()->json([
            'pedidos' => $cliente->pedidos()->orderByDesc('created_at')->limit(10)->get(),
            'visitas' => $cliente->agendaVisitas()->orderByDesc('data_hora_inicio')->limit(10)->get(),
            'oportunidades' => $cliente->oportunidades()->orderByDesc('created_at')->limit(10)->get(),
            'titulos' => $cliente->titulosFinanceiros()->orderByDesc('data_vencimento')->limit(10)->get(),
            'contatos' => $cliente->contatos()->orderByDesc('created_at')->limit(10)->get(),
        ]);
    }

    private function authorizeVendedor($user, $vendedorId): void
    {
        if ($user->perfil === 'vendedor' && $user->id !== $vendedorId) {
            abort(403, 'Acesso não autorizado a este cliente.');
        }
    }
}

