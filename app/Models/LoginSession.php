<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'logged_in_at', 'logged_out_at', 'duration_seconds', 'auth_guard'];
    protected $casts = ['logged_in_at' => 'datetime', 'logged_out_at' => 'datetime'];
}
