<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'preferred_sources'    => $this->preferred_sources ?? [],
            'preferred_categories' => $this->preferred_categories ?? [],
            'preferred_authors'    => $this->preferred_authors ?? [],
        ];
    }
}
