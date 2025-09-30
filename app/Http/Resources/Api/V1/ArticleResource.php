<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'content'      => $this->content,
            'url'          => $this->url,
            'image_url'    => $this->image_url,
            'published_at' => $this->published_at?->toIso8601String(),
            'source'       => new SourceResource($this->whenLoaded('source')),
            'categories'   => CategoryResource::collection($this->whenLoaded('categories')),
            'authors'      => AuthorResource::collection($this->whenLoaded('authors')),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
