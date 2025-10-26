<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChargeRequest;
use App\Services\ChargeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class ChargeController extends Controller
{
    protected ChargeService $chargeService;

    public function __construct(ChargeService $chargeService)
    {
        $this->chargeService = $chargeService;
    }

    public function store(StoreChargeRequest $request): JsonResponse
    {
        // Pega a chave de idempotência do cabeçalho HTTP
        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            $charge = $this->chargeService->createCharge($request->validated(), $idempotencyKey);
            
            // Se a cobrança já existia (idempotente), retorna 200 OK.
            // Se foi criada agora, retorna 201 Created.
            $statusCode = $charge->wasRecentlyCreated ? 201 : 200;

            return response()->json([
                'message' => ($statusCode === 201) ? 'Cobrança criada com sucesso.' : 'Cobrança retornada (Idempotente).',
                'charge_id' => $charge->id,
                'status' => $charge->status,
                'charge' => $charge->load('customer:id,name,email'), // Retorna dados do cliente
            ], $statusCode);

        } catch (ModelNotFoundException $e) {
            // Cliente não encontrado (404 Not Found)
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Cliente com ID ' . $request->customer_id . ' não encontrado.',
            ], 404);
        } catch (\Exception $e) {
             // Erros de lógica de pagamento (ex: parcelas inválidas) (400 Bad Request)
             $httpCode = $e->getCode() === 400 ? 400 : 500;
             return response()->json([
                'error' => 'Bad Request',
                'message' => $e->getMessage(),
            ], $httpCode);
        }
    }
}