<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        // Injeção de dependência do Service Layer
        $this->customerService = $customerService;
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());
            
            return response()->json([
                'message' => 'Cliente cadastrado com sucesso.',
                'customer_id' => $customer->id,
                'customer' => $customer,
            ], 201); // 201 Created

        } catch (\Exception $e) {
            // Trata a exceção lançada pelo Service, caso a validação do FormRequest tenha falhado por algum motivo
            if ($e->getCode() === 409) {
                 return response()->json([
                    'error' => 'Conflict',
                    'message' => 'E-mail ou documento já cadastrado.',
                ], 409);
            }
            // Para outros erros (ex: erro no servidor 500)
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'Ocorreu um erro ao processar o cadastro.',
            ], 500);
        }
    }
}