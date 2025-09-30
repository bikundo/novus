<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Source;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use App\Services\Cache\ArticleCacheService;
use App\Http\Resources\Api\V1\SourceResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Sources', 'Endpoints for managing news sources')]
class SourceController extends Controller
{
    public function __construct(
        protected readonly ArticleCacheService $cacheService,
    ) {}

    /**
     * List all sources
     *
     * Retrieve a list of all available news sources in the system.
     * Results are cached for 24 hours.
     */
    #[ResponseFromApiResource(SourceResource::class, Source::class, collection: true)]
    public function index(): AnonymousResourceCollection
    {
        $sources = $this->cacheService->getSources();

        return SourceResource::collection($sources);
    }

    /**
     * Get a single source
     *
     * Retrieve detailed information about a specific news source.
     */
    #[ResponseFromApiResource(SourceResource::class, Source::class)]
    public function show(Source $source): SourceResource
    {
        return new SourceResource($source);
    }
}
