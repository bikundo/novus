<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SearchAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'results_count',
        'source_filter',
        'category_filter',
        'from_date',
        'to_date',
        'response_time_ms',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'results_count'    => 'integer',
            'response_time_ms' => 'integer',
            'from_date'        => 'date',
            'to_date'          => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
