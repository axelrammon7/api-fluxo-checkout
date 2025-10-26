<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'document', 'phone'];

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }
}