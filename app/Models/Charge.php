<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Charge extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'due_date',
        'installments',
        'payment_details',
        'idempotency_key',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}