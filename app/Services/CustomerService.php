<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\QueryException;

class CustomerService
{
    /**
     * Cria um novo cliente, verificando a unicidade de email e documento.
     * * @param array $data Dados do cliente (name, email, document, phone).
     * @return Customer
     * @throws \Exception Se o email ou documento já existirem.
     */
    public function createCustomer(array $data): Customer
    {
        try {
            $customer = Customer::create($data);
            return $customer;

        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new \Exception("E-mail ou Documento já cadastrado.", 409);
            }
            throw $e;
        }
    }
}