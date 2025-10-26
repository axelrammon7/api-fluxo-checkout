<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Definições de ENUMs para consistência dos dados
if (!defined('PAYMENT_METHODS')) {
    define('PAYMENT_METHODS', ['pix', 'credit_card', 'boleto']);
}
if (!defined('CHARGE_STATUSES')) {
    define('CHARGE_STATUSES', ['pending', 'paid', 'failed', 'expired']);
}

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();

            // REQUISITO: Vinculado a um cliente existente
            $table->foreignId('customer_id')->constrained('customers');

            // REQUISITO: Valor e Moeda
            $table->decimal('amount', 10, 2); // Valor em Reais (ou outra moeda)
            $table->string('currency', 3)->default('BRL'); // Ex: BRL

            // REQUISITO: Método de Pagamento e Status
            $table->enum('payment_method', PAYMENT_METHODS);
            $table->enum('status', CHARGE_STATUSES)->default('pending'); // REQUISITO: Inicia como 'pending'

            // Campos Específicos (podem ser nulos)
            $table->date('due_date')->nullable(); // Para Boleto/Pix (vencimento)
            $table->unsignedSmallInteger('installments')->nullable(); // Para Cartão (parcelas)
            $table->json('payment_details')->nullable(); // Para dados JSON extras (código Pix, URL do boleto)

            // Para Idempotência (REQUISITO: Controle de idempotência)
            $table->uuid('idempotency_key')->nullable()->unique(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};