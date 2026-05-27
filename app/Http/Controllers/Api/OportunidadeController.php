<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Oportunidade;
use Illuminate\Http\Request;

class OportunidadeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Oportunidade::with([
            'cliente:id,razao_social,nome_fantasia',
            'prospect:id,razao_social,nome_fantasia',
            'vendedor:id,name',
        ]);

        if ($user->perfil === 'vendedor') {
            $query->where('vendedor_id', $user->id);
        } elseif ($user->isSupervisor() && !$user->isGerente()) {
            $subIds = $user->subordinados()->pluck('id')->push($user->id);
            $query->whereIn('vendedor_id', $subIds);
        }

        if ($request->filled('estagio')) {
            $query->where('estagio', $request->estagio);
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate($request->per_page ?? 20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'cliente_id' => 'nullable|exists:clientes,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'valor_estimado' => 'nullable|numeric|min:0',
            'estagio' => 'required|string|max:50',
            'probabilidade' => 'nullable|integer|min:0|max:100',
            'data_previsao_fechamento' => 'nullable|date',
            'observacoes' => 'nullable|string',
        ]);

        $data['vendedor_id'] = $request->user()->id;

        $oportunidade = Oportunidade::create($data);

        return response()->json($oportunidade, 201);
    }

    public function show(Request $request, Oportunidade $oportunidade)
    {
        $this->authorizeAccess($request->user(), $oportunidade);
        $oportunidade->load(['cliente', 'prospect', 'vendedor:id,name', 'pedido']);

        return response()->json($oportunidade);
    }

    public function update(Request $request, Oportunidade $oportunidade)
    {
        $this->authorizeAccess($request->user(), $oportunidade);

        $data = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'valor_estimado' => 'nullable|numeric|min:0',
            'estagio' => 'sometimes|string|max:50',
            'probabilidade' => 'nullable|integer|min:0|max:100',
            'data_previsao_fechamento' => 'nullable|date',
            'motivo_perda' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $oportunidade->update($data);

        return response()->json($oportunidade);
    }

    private function authorizeAccess($user, Oportunidade $oportunidade): void
    {
        if ($user->perfil === 'vendedor' && $user->id !== $oportunidade->vendedor_id) {
            abort(403);
        }
    }
}

