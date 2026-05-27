<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comissao;
use Illuminate\Http\Request;

class ComissaoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Comissao::with(['vendedor:id,name', 'pedido:id,numero,total']);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderByDesc('created_at')->paginate($request->per_page ?? 20));
    }

    public function resumo(Request $request)
    {
        $user = $request->user();
        $mes = $request->mes ?? now()->month;
        $ano = $request->ano ?? now()->year;

        $query = Comissao::where('mes', $mes)->where('ano', $ano);

        if ($user->perfil === 'vendedor') {
            $query->where('vendedor_id', $user->id);
        }

        $resumo = $query->selectRaw('
            SUM(valor_comissao) as total_comissao,
            SUM(CASE WHEN status = "pago" THEN valor_comissao ELSE 0 END) as total_pago,
            SUM(CASE WHEN status = "pendente" THEN valor_comissao ELSE 0 END) as total_pendente,
            COUNT(*) as total_registros
        ')->first();

        return response()->json($resumo);
    }
}

