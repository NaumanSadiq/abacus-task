<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'provider', 'provider_ref', 'status', 'amount_cents', 'currency', 'payload'];
    protected $casts = ['payload' => 'array'];
}
