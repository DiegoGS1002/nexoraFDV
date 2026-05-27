<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgendaVisita;
use Illuminate\Http\Request;

class AgendaVisitaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = AgendaVisita::with([
            'cliente:id,razao_social,nome_fantasia,latitude,longitude',
            'prospect:id,razao_social,nome_fantasia',
        ]);

        if ($user->perfil === 'vendedor') {
            $query->where('vendedor_id', $user->id);
        } elseif ($user->isSupervisor() && !$user->isGerente()) {
            $subIds = $user->subordinados()->pluck('id')->push($user->id);
            $query->whereIn('vendedor_id', $subIds);
        }

        if ($request->filled('data_de')) {
            $query->whereDate('data_hora_inicio', '>=', $request->data_de);
        }

        if ($request->filled('data_ate')) {
            $query->whereDate('data_hora_inicio', '<=', $request->data_ate);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderBy('data_hora_inicio')->paginate($request->per_page ?? 20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data_hora_inicio' => 'required|date',
            'data_hora_fim' => 'nullable|date|after:data_hora_inicio',
            'tipo' => 'nullable|string|max:50',
        ]);

        $data['vendedor_id'] = $request->user()->id;
        $data['status'] = 'agendado';

        $visita = AgendaVisita::create($data);

        return response()->json($visita, 201);
    }

    public function show(Request $request, AgendaVisita $agendaVisita)
    {
        $this->authorizeAccess($request->user(), $agendaVisita);
        $agendaVisita->load(['cliente', 'prospect', 'vendedor:id,name']);

        return response()->json($agendaVisita);
    }

    public function update(Request $request, AgendaVisita $agendaVisita)
    {
        $this->authorizeAccess($request->user(), $agendaVisita);

        $data = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'data_hora_inicio' => 'sometimes|date',
            'data_hora_fim' => 'nullable|date',
            'status' => 'sometimes|in:agendado,realizado,cancelado,reagendado',
            'resultado' => 'nullable|string',
        ]);

        $agendaVisita->update($data);

        return response()->json($agendaVisita);
    }

    public function checkIn(Request $request, AgendaVisita $agendaVisita)
    {
        $this->authorizeAccess($request->user(), $agendaVisita);

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $agendaVisita->update([
            'check_in_at' => now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'em_andamento',
        ]);

        return response()->json(['message' => 'Check-in realizado.', 'visita' => $agendaVisita]);
    }

    public function checkOut(Request $request, AgendaVisita $agendaVisita)
    {
        $this->authorizeAccess($request->user(), $agendaVisita);

        $request->validate([
            'resultado' => 'required|string',
        ]);

        $agendaVisita->update([
            'check_out_at' => now(),
            'resultado' => $request->resultado,
            'status' => 'realizado',
        ]);

        return response()->json(['message' => 'Check-out realizado.', 'visita' => $agendaVisita]);
    }

    private function authorizeAccess($user, AgendaVisita $visita): void
    {
        if ($user->perfil === 'vendedor' && $user->id !== $visita->vendedor_id) {
            abort(403);
        }
    }
}

