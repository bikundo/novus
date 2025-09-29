<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiLog extends Model
{
    /** @use HasFactory<\Database\Factories\ApiLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'api_provider',
        'endpoint',
        'status_code',
        'response_time',
        'articles_fetched',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status_code'      => 'integer',
            'response_time'    => 'integer',
            'articles_fetched' => 'integer',
            'created_at'       => 'datetime',
        ];
    }
}
