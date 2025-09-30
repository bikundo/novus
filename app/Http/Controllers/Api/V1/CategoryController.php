<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use App\Http\Resources\Api\V1\CategoryResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Categories', 'Endpoints for managing article categories')]
class CategoryController extends Controller
{
    /**
     * List all categories
     *
     * Retrieve a list of all available article categories in the system.
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class, collection: true)]
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Get a single category
     *
     * Retrieve detailed information about a specific category.
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }
}
