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
            'preferred_sources'    => SourceResource::collection($this->whenLoaded('preferredSources')),
            'preferred_categories' => CategoryResource::collection($this->whenLoaded('preferredCategories')),
            'preferred_authors'    => AuthorResource::collection($this->whenLoaded('preferredAuthors')),
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }
}
