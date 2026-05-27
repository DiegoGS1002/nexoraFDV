<?php

use App\Http\Controllers\Api\AgendaVisitaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ComissaoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\OportunidadeController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\ProspectController;
use App\Http\Controllers\Api\RoteiroController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TituloFinanceiroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - FDV Nexora
|--------------------------------------------------------------------------
*/

// Autenticação pública
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Rotas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('ranking-vendedores', [DashboardController::class, 'rankingVendedores']);
    });

    // Clientes
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'index']);
        Route::post('/', [ClienteController::class, 'store']);
        Route::get('{cliente}', [ClienteController::class, 'show']);
        Route::put('{cliente}', [ClienteController::class, 'update']);
        Route::patch('{cliente}/bloquear', [ClienteController::class, 'bloquear']);
        Route::patch('{cliente}/desbloquear', [ClienteController::class, 'desbloquear']);
        Route::get('{cliente}/timeline', [ClienteController::class, 'timeline']);
    });

    // Prospects
    Route::prefix('prospects')->group(function () {
        Route::get('/', [ProspectController::class, 'index']);
        Route::post('/', [ProspectController::class, 'store']);
        Route::get('{prospect}', [ProspectController::class, 'show']);
        Route::put('{prospect}', [ProspectController::class, 'update']);
        Route::post('{prospect}/converter', [ProspectController::class, 'converter']);
    });

    // Produtos / Catálogo
    Route::prefix('produtos')->group(function () {
        Route::get('/', [ProdutoController::class, 'index']);
        Route::get('{produto}', [ProdutoController::class, 'show']);
        Route::get('{produto}/precos', [ProdutoController::class, 'precos']);
    });

    // Pedidos
    Route::prefix('pedidos')->group(function () {
        Route::get('/', [PedidoController::class, 'index']);
        Route::post('/', [PedidoController::class, 'store']);
        Route::get('{pedido}', [PedidoController::class, 'show']);
        Route::patch('{pedido}/confirmar', [PedidoController::class, 'confirmar']);
        Route::patch('{pedido}/cancelar', [PedidoController::class, 'cancelar']);
        Route::patch('{pedido}/aprovar', [PedidoController::class, 'aprovar']);
    });

    // Agenda de Visitas
    Route::prefix('visitas')->group(function () {
        Route::get('/', [AgendaVisitaController::class, 'index']);
        Route::post('/', [AgendaVisitaController::class, 'store']);
        Route::get('{agendaVisita}', [AgendaVisitaController::class, 'show']);
        Route::put('{agendaVisita}', [AgendaVisitaController::class, 'update']);
        Route::patch('{agendaVisita}/check-in', [AgendaVisitaController::class, 'checkIn']);
        Route::patch('{agendaVisita}/check-out', [AgendaVisitaController::class, 'checkOut']);
    });

    // Oportunidades CRM
    Route::prefix('oportunidades')->group(function () {
        Route::get('/', [OportunidadeController::class, 'index']);
        Route::post('/', [OportunidadeController::class, 'store']);
        Route::get('{oportunidade}', [OportunidadeController::class, 'show']);
        Route::put('{oportunidade}', [OportunidadeController::class, 'update']);
    });

    // Metas
    Route::prefix('metas')->group(function () {
        Route::get('/', [MetaController::class, 'index']);
        Route::post('/', [MetaController::class, 'store']);
        Route::put('{meta}', [MetaController::class, 'update']);
    });

    // Comissões
    Route::prefix('comissoes')->group(function () {
        Route::get('/', [ComissaoController::class, 'index']);
        Route::get('resumo', [ComissaoController::class, 'resumo']);
    });

    // Financeiro - Títulos
    Route::prefix('titulos-financeiros')->group(function () {
        Route::get('/', [TituloFinanceiroController::class, 'index']);
        Route::get('cliente/{clienteId}', [TituloFinanceiroController::class, 'porCliente']);
        Route::patch('{tituloFinanceiro}/registrar-pagamento', [TituloFinanceiroController::class, 'registrarPagamento']);
    });

    // Roteiros
    Route::prefix('roteiros')->group(function () {
        Route::get('/', [RoteiroController::class, 'index']);
        Route::post('/', [RoteiroController::class, 'store']);
        Route::get('{roteiro}', [RoteiroController::class, 'show']);
        Route::put('{roteiro}', [RoteiroController::class, 'update']);
    });

    // Sincronização Offline
    Route::prefix('sync')->group(function () {
        Route::get('download', [SyncController::class, 'download']);
        Route::post('upload', [SyncController::class, 'upload']);
    });

});

