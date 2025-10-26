<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir que qualquer usuário (por enquanto) acesse a rota
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:customers,email|max:150',
            'document' => 'required|string|unique:customers,document|max:50',
            'phone' => 'nullable|string|max:20',
        ];
    }

    // Mensagens de erro de validação
    public function messages(): array
    {
        return [
            'email.unique' => 'O e-mail informado já está cadastrado.',
            'document.unique' => 'O documento informado já está cadastrado.',
            'required' => 'O campo :attribute é obrigatório.',
        ];
    }
}