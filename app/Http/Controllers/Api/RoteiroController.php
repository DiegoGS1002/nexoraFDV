<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Roteiro;
use Illuminate\Http\Request;

class RoteiroController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Roteiro::with(['clientes:id,razao_social,nome_fantasia,latitude,longitude,cidade,estado']);

        if ($user->perfil === 'vendedor') {
            $query->where('vendedor_id', $user->id);
        }

        if ($request->filled('dia_semana')) {
            $query->where('dia_semana', $request->dia_semana);
        }

        return response()->json($query->where('ativo', true)->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'dia_semana' => 'nullable|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'data_especifica' => 'nullable|date',
            'clientes' => 'nullable|array',
            'clientes.*.id' => 'exists:clientes,id',
            'clientes.*.ordem' => 'integer|min:0',
        ]);

        $data['vendedor_id'] = $request->user()->id;
        $data['ativo'] = true;

        $roteiro = Roteiro::create($data);

        if (!empty($data['clientes'])) {
            $sync = collect($data['clientes'])->mapWithKeys(fn($c) => [
                $c['id'] => ['ordem' => $c['ordem'] ?? 0]
            ]);
            $roteiro->clientes()->sync($sync);
        }

        return response()->json($roteiro->load('clientes'), 201);
    }

    public function show(Request $request, Roteiro $roteiro)
    {
        $this->authorizeAccess($request->user(), $roteiro);
        $roteiro->load('clientes');

        return response()->json($roteiro);
    }

    public function update(Request $request, Roteiro $roteiro)
    {
        $this->authorizeAccess($request->user(), $roteiro);

        $data = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'dia_semana' => 'nullable|in:segunda,terca,quarta,quinta,sexta,sabado,domingo',
            'data_especifica' => 'nullable|date',
            'ativo' => 'sometimes|boolean',
            'clientes' => 'nullable|array',
            'clientes.*.id' => 'exists:clientes,id',
            'clientes.*.ordem' => 'integer|min:0',
        ]);

        $roteiro->update($data);

        if (array_key_exists('clientes', $data)) {
            $sync = collect($data['clientes'])->mapWithKeys(fn($c) => [
                $c['id'] => ['ordem' => $c['ordem'] ?? 0]
            ]);
            $roteiro->clientes()->sync($sync);
        }

        return response()->json($roteiro->load('clientes'));
    }

    private function authorizeAccess($user, Roteiro $roteiro): void
    {
        if ($user->perfil === 'vendedor' && $user->id !== $roteiro->vendedor_id) {
            abort(403);
        }
    }
}

