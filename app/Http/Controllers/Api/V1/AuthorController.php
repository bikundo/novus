<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Author;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use App\Http\Resources\Api\V1\AuthorResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Authors', 'Endpoints for managing article authors')]
class AuthorController extends Controller
{
    /**
     * List all authors
     *
     * Retrieve a paginated list of all authors in the system.
     */
    #[QueryParam('page', 'integer', 'Page number for pagination', required: false, example: 1)]
    #[QueryParam('per_page', 'integer', 'Number of items per page (max 100)', required: false, example: 20)]
    #[ResponseFromApiResource(AuthorResource::class, Author::class, collection: true, paginate: 20)]
    public function index(): AnonymousResourceCollection
    {
        $perPage = (int) request()->input('per_page', config('news-aggregator.pagination.per_page', 20));

        $authors = Author::query()
            ->orderBy('name')
            ->paginate($perPage);

        return AuthorResource::collection($authors);
    }

    /**
     * Get a single author
     *
     * Retrieve detailed information about a specific author.
     */
    #[ResponseFromApiResource(AuthorResource::class, Author::class)]
    public function show(Author $author): AuthorResource
    {
        return new AuthorResource($author);
    }
}
