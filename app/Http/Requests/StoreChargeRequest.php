<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $methods = ['pix', 'credit_card', 'boleto'];

        return [
            // Validações básicas
            'customer_id' => 'required|integer|exists:customers,id', // Cliente deve existir
            'amount' => 'required|numeric|min:1.00',
            'currency' => 'nullable|string|size:3|in:BRL', // 'BRL' por padrão
            'payment_method' => ['required', 'string', Rule::in($methods)],
            
            'due_date' => 'nullable|date_format:Y-m-d|after:today', // Para Boleto/Pix
            'installments' => [
                'nullable', 
                'integer', 
                'min:1', 
                'max:12', 
                // Obrigatório se o método for 'credit_card'
                Rule::requiredIf($this->payment_method === 'credit_card') 
            ],
            
            // Idempotency Key (opcional, aceita qualquer string)
            'idempotency_key' => 'nullable|string|max:255' 
        ];
    }
    
    // Captura a chave de idempotência do cabeçalho
    public function prepareForValidation()
    {
        $idempotencyKey = $this->header('Idempotency-Key');
        if ($idempotencyKey) {
            $this->merge(['idempotency_key' => $idempotencyKey]);
        }
    }
}