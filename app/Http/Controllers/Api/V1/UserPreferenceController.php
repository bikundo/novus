<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Authenticated;
use App\Http\Requests\Api\V1\StorePreferenceRequest;
use App\Http\Resources\Api\V1\UserPreferenceResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('User Preferences', 'Endpoints for managing user preferences (requires authentication)')]
#[Authenticated]
class UserPreferenceController extends Controller
{
    /**
     * Get user preferences
     *
     * Retrieve the authenticated user's news preferences including preferred sources, categories, and authors.
     */
    #[ResponseFromApiResource(UserPreferenceResource::class, UserPreference::class)]
    public function show(): UserPreferenceResource|JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $preference = UserPreference::query()
            ->where('user_id', $user->id)
            ->first();

        if (!$preference) {
            return new UserPreferenceResource((object) [
                'id'                   => null,
                'preferred_sources'    => [],
                'preferred_categories' => [],
                'preferred_authors'    => [],
            ]);
        }

        return new UserPreferenceResource($preference);
    }

    /**
     * Create or update user preferences
     *
     * Store or update the authenticated user's news preferences. This will sync the selected sources, categories, and authors.
     */
    #[BodyParam('preferred_sources', 'array', 'Array of source IDs', required: false, example: [1, 2, 3])]
    #[BodyParam('preferred_categories', 'array', 'Array of category IDs', required: false, example: [1, 2])]
    #[BodyParam('preferred_authors', 'array', 'Array of author IDs', required: false, example: [1, 2, 3, 4])]
    #[ResponseFromApiResource(UserPreferenceResource::class, UserPreference::class, status: 201)]
    public function store(StorePreferenceRequest $request): UserPreferenceResource|JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $preference = UserPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_sources'    => $request->input('preferred_sources', []),
                'preferred_categories' => $request->input('preferred_categories', []),
                'preferred_authors'    => $request->input('preferred_authors', []),
            ]
        );

        return new UserPreferenceResource($preference);
    }
}
