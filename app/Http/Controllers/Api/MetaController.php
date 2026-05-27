<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meta;
use App\Models\Comissao;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Meta::with('vendedor:id,name');

        if ($user->perfil === 'vendedor') {
            $query->where('vendedor_id', $user->id);
        } elseif ($user->isSupervisor() && !$user->isGerente()) {
            $subIds = $user->subordinados()->pluck('id')->push($user->id);
            $query->whereIn('vendedor_id', $subIds);
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        if (!$request->user()->isGerente()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $data = $request->validate([
            'vendedor_id' => 'required|exists:users,id',
            'tipo' => 'required|string|max:50',
            'descricao' => 'nullable|string',
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020',
            'valor_meta' => 'required|numeric|min:0',
        ]);

        $data['valor_realizado'] = 0;
        $meta = Meta::create($data);

        return response()->json($meta, 201);
    }

    public function update(Request $request, Meta $meta)
    {
        if (!$request->user()->isGerente()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $data = $request->validate([
            'valor_meta' => 'sometimes|numeric|min:0',
            'valor_realizado' => 'sometimes|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        $meta->update($data);

        return response()->json($meta);
    }
}

