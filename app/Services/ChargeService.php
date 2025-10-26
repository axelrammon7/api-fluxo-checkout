<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChargeService
{
    const PAYMENT_METHODS = ['pix', 'credit_card', 'boleto'];

    /**
     * Cria uma nova cobrança com lógica de idempotência.
     * * @param array $data Dados da cobrança (customer_id, amount, payment_method, etc.).
     * @param string|null $idempotencyKey Chave de idempotência do cabeçalho.
     * @return Charge
     * @throws ModelNotFoundException Se o cliente não for encontrado.
     * @throws \Exception Para erros de validação da lógica de pagamento ou conflito de idempotência.
     */
    public function createCharge(array $data, ?string $idempotencyKey): Charge
    {
        $customer = Customer::findOrFail($data['customer_id']);

        if ($idempotencyKey) {
            $existingCharge = Charge::where('idempotency_key', $idempotencyKey)->first();
            if ($existingCharge) {
                // Se a chave já existe, retornamos a cobrança existente (Comportamento idempotente)
                return $existingCharge;
            }
            $data['idempotency_key'] = $idempotencyKey;
        }

        $specificData = $this->processPaymentMethodData($data);
        
        $chargeData = array_merge(
            $data, 
            $specificData, 
            ['status' => 'pending']
        );

        try {
            return DB::transaction(function () use ($chargeData) {
                return Charge::create($chargeData);
            });
        } catch (\Exception $e) {
            throw new \Exception("Falha ao criar a cobrança: " . $e->getMessage(), 500);
        }
    }

    private function processPaymentMethodData(array $data): array
    {
        $method = $data['payment_method'];
        $processedData = [
            'due_date' => null, 
            'installments' => null, 
            'payment_details' => []
        ];

        switch ($method) {
            case 'boleto':
                $processedData['due_date'] = $data['due_date'] ?? now()->addDays(5)->toDateString();
                $processedData['payment_details']['bar_code'] = Str::random(44);
                break;

            case 'pix':
                $processedData['due_date'] = $data['due_date'] ?? now()->addHours(24)->toDateString();
                $processedData['payment_details']['qr_code'] = 'URL_FAKE_PIX_' . Str::uuid();
                break;

            case 'credit_card':
                if (empty($data['installments']) || !is_numeric($data['installments'])) {
                    throw new \Exception("Parcelas (installments) é obrigatório para Cartão de Crédito.", 400);
                }
                $installments = (int) $data['installments'];
                if ($installments < 1 || $installments > 12) {
                     throw new \Exception("O número de parcelas deve ser entre 1 e 12.", 400);
                }
                $processedData['installments'] = $installments;

                $processedData['payment_details']['card_token'] = Str::uuid(); 
                break;

            default:
                throw new \Exception("Método de pagamento não suportado.", 400);
        }

        return $processedData;
    }
}