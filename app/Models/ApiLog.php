<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'method',
        'endpoint',
        'status_code',
        'ip_address',
        'user_agent',
        'client_type',
        'response_time_ms',
        'request_payload',
    ];
}
