<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\AgendaVisita;
use App\Models\Meta;
use App\Models\TituloFinanceiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $hoje = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();

        // Escopo de vendedor_ids
        if ($user->perfil === 'vendedor') {
            $vendedorIds = [$user->id];
        } elseif ($user->isSupervisor() && !$user->isGerente()) {
            $vendedorIds = $user->subordinados()->pluck('id')->push($user->id)->toArray();
        } else {
            $vendedorIds = null; // todos
        }

        $pedidosQuery = fn() => $vendedorIds
            ? Pedido::whereIn('vendedor_id', $vendedorIds)
            : Pedido::query();

        $clientesQuery = fn() => $vendedorIds
            ? Cliente::whereIn('vendedor_id', $vendedorIds)
            : Cliente::query();

        // KPIs do dia
        $vendasHoje = $pedidosQuery()
            ->whereDate('data_pedido', $hoje)
            ->whereNotIn('status', ['cancelado', 'rascunho'])
            ->sum('total');

        $pedidosHoje = $pedidosQuery()
            ->whereDate('data_pedido', $hoje)
            ->whereNotIn('status', ['cancelado'])
            ->count();

        // KPIs do mês
        $vendasMes = $pedidosQuery()
            ->whereDate('data_pedido', '>=', $inicioMes)
            ->whereNotIn('status', ['cancelado', 'rascunho'])
            ->sum('total');

        $ticketMedio = $pedidosQuery()
            ->whereDate('data_pedido', '>=', $inicioMes)
            ->whereNotIn('status', ['cancelado', 'rascunho'])
            ->avg('total') ?? 0;

        $clientesAtivos = $clientesQuery()->where('status', 'ativo')->count();

        $clientesPositivados = $pedidosQuery()
            ->whereDate('data_pedido', '>=', $inicioMes)
            ->whereNotIn('status', ['cancelado'])
            ->distinct('cliente_id')
            ->count('cliente_id');

        // Visitas do dia
        $visitasHoje = $vendedorIds
            ? AgendaVisita::whereIn('vendedor_id', $vendedorIds)->whereDate('data_hora_inicio', $hoje)->count()
            : AgendaVisita::whereDate('data_hora_inicio', $hoje)->count();

        // Inadimplência
        $inadimplenciaQuery = $vendedorIds
            ? TituloFinanceiro::whereHas('cliente', fn($q) => $q->whereIn('vendedor_id', $vendedorIds))
            : TituloFinanceiro::query();

        $totalInadimplente = $inadimplenciaQuery->clone()->vencidos()->sum('valor');

        // Metas do mês
        $mes = now()->month;
        $ano = now()->year;
        $metasQuery = Meta::where('mes', $mes)->where('ano', $ano);
        if ($vendedorIds) {
            $metasQuery->whereIn('vendedor_id', $vendedorIds);
        }
        $metas = $metasQuery->get();
        $metaFaturamento = $metas->where('tipo', 'faturamento')->sum('valor_meta');
        $realizadoFaturamento = $metas->where('tipo', 'faturamento')->sum('valor_realizado');

        // Pedidos por status (mês)
        $pedidosPorStatus = $pedidosQuery()
            ->whereDate('data_pedido', '>=', $inicioMes)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'hoje' => [
                'vendas' => (float) $vendasHoje,
                'pedidos' => $pedidosHoje,
                'visitas' => $visitasHoje,
            ],
            'mes' => [
                'vendas' => (float) $vendasMes,
                'ticket_medio' => round((float) $ticketMedio, 2),
                'clientes_positivados' => $clientesPositivados,
                'pedidos_por_status' => $pedidosPorStatus,
            ],
            'carteira' => [
                'clientes_ativos' => $clientesAtivos,
                'inadimplencia' => (float) $totalInadimplente,
            ],
            'metas' => [
                'faturamento_meta' => (float) $metaFaturamento,
                'faturamento_realizado' => (float) $realizadoFaturamento,
                'percentual' => $metaFaturamento > 0 ? round($realizadoFaturamento / $metaFaturamento * 100, 2) : 0,
            ],
        ]);
    }

    public function rankingVendedores(Request $request)
    {
        if (!$request->user()->isSupervisor()) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $mes = $request->mes ?? now()->month;
        $ano = $request->ano ?? now()->year;
        $inicioMes = \Carbon\Carbon::createFromDate($ano, $mes, 1)->startOfMonth()->toDateString();
        $fimMes = \Carbon\Carbon::createFromDate($ano, $mes, 1)->endOfMonth()->toDateString();

        $ranking = Pedido::whereDate('data_pedido', '>=', $inicioMes)
            ->whereDate('data_pedido', '<=', $fimMes)
            ->whereNotIn('status', ['cancelado', 'rascunho'])
            ->join('users', 'users.id', '=', 'pedidos.vendedor_id')
            ->select(
                'vendedor_id',
                'users.name as vendedor_nome',
                DB::raw('SUM(total) as total_vendas'),
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('COUNT(DISTINCT cliente_id) as clientes_positivados')
            )
            ->groupBy('vendedor_id', 'users.name')
            ->orderByDesc('total_vendas')
            ->get();

        return response()->json($ranking);
    }
}

