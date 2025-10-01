<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SourceController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\UserPreferenceController;
use App\Http\Controllers\Api\V1\PersonalizedFeedController;

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

    Route::get('search', [SearchController::class, 'search'])->name('search');

    Route::get('sources', [SourceController::class, 'index'])->name('sources.index');
    Route::get('sources/{source}', [SourceController::class, 'show'])->name('sources.show');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

    Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
    Route::get('authors/{author}', [AuthorController::class, 'show'])->name('authors.show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('feed', [PersonalizedFeedController::class, 'index'])->name('feed');
        Route::get('preferences', [UserPreferenceController::class, 'show'])->name('preferences.show');
        Route::post('preferences', [UserPreferenceController::class, 'store'])->name('preferences.store');
    });
});
