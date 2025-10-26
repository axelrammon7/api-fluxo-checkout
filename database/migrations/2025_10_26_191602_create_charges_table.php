<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->foreignId('customer_id')->constrained('customers');

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');

            $table->enum('payment_method', PAYMENT_METHODS);
            $table->enum('status', CHARGE_STATUSES)->default('pending');

            $table->date('due_date')->nullable();
            $table->unsignedSmallInteger('installments')->nullable();
            $table->json('payment_details')->nullable();

            // Aceita qualquer string única para permitir flexibilidade nas chaves
            $table->string('idempotency_key', 255)->nullable()->unique(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};