<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Prospect::with('vendedor:id,name');

        if ($user->perfil === 'vendedor') {
            $query->where('vendedor_id', $user->id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('razao_social', 'like', "%$s%")
                ->orWhere('cnpj_cpf', 'like', "%$s%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('razao_social')->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj_cpf' => 'nullable|string|max:18',
            'tipo' => 'in:juridica,fisica',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string',
            'numero' => 'nullable|string',
            'complemento' => 'nullable|string',
            'bairro' => 'nullable|string',
            'cidade' => 'nullable|string',
            'estado' => 'nullable|string|max:2',
            'observacoes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $data['vendedor_id'] = $request->user()->id;
        $data['status'] = 'prospectando';

        $prospect = Prospect::create($data);

        return response()->json($prospect, 201);
    }

    public function show(Request $request, Prospect $prospect)
    {
        $prospect->load(['vendedor:id,name', 'oportunidades', 'agendaVisitas']);

        return response()->json($prospect);
    }

    public function update(Request $request, Prospect $prospect)
    {
        $data = $request->validate([
            'razao_social' => 'sometimes|string|max:255',
            'nome_fantasia' => 'nullable|string',
            'status' => 'sometimes|string|max:50',
            'observacoes' => 'nullable|string',
        ]);

        $prospect->update($data);

        return response()->json($prospect);
    }

    public function converter(Request $request, Prospect $prospect)
    {
        if ($prospect->cliente_id) {
            return response()->json(['message' => 'Prospect já convertido em cliente.'], 422);
        }

        $cliente = \App\Models\Cliente::create([
            'razao_social' => $prospect->razao_social,
            'nome_fantasia' => $prospect->nome_fantasia,
            'cnpj_cpf' => $prospect->cnpj_cpf,
            'tipo' => $prospect->tipo,
            'email' => $prospect->email,
            'phone' => $prospect->phone,
            'cep' => $prospect->cep,
            'logradouro' => $prospect->logradouro,
            'numero' => $prospect->numero,
            'complemento' => $prospect->complemento,
            'bairro' => $prospect->bairro,
            'cidade' => $prospect->cidade,
            'estado' => $prospect->estado,
            'vendedor_id' => $prospect->vendedor_id,
            'latitude' => $prospect->latitude,
            'longitude' => $prospect->longitude,
            'status' => 'ativo',
        ]);

        $prospect->update([
            'cliente_id' => $cliente->id,
            'status' => 'convertido',
            'sincronizado_erp' => false,
        ]);

        return response()->json([
            'message' => 'Prospect convertido em cliente com sucesso.',
            'cliente' => $cliente,
        ], 201);
    }
}

