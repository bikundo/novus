<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    use Searchable;

    /**
     * Get the columns that should be searched.
     *
     * @return array<int, string>
     */
    public function toSearchableArray(): array
    {
        if (config('scout.driver') === 'typesense') {
            return [
                'id'             => (string) $this->id,
                'title'          => $this->title,
                'description'    => $this->description,
                'content'        => $this->content,
                'source_name'    => $this->source?->name ?? '',
                'source_slug'    => $this->source?->slug ?? '',
                'category_names' => $this->categories->pluck('name')->implode(', '),
                'category_slugs' => $this->categories->pluck('slug')->toArray(),
                'author_names'   => $this->authors->pluck('name')->implode(', '),
                'published_at'   => $this->published_at?->timestamp ?? 0,
            ];
        }

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'content'     => $this->content,
        ];
    }

    /**
     * Get the name of the column used to search the model.
     *
     * @return array<int, string>
     */
    public function searchableColumns(): array
    {
        return ['id', 'title', 'description', 'content'];
    }

    protected $fillable = [
        'external_id',
        'source_id',
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'published_at',
    ];

    /**
     * Get the source that the article belongs to.
     *
     * @return BelongsTo<Source, Article>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the categories for the article.
     *
     * @return BelongsToMany<Category>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the authors of the article.
     *
     * @return BelongsToMany<Author>
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    /**
     * Get the Typesense collection schema.
     *
     * @return array<string, mixed>
     */
    public function getCollectionSchema(): array
    {
        return [
            'name'   => $this->searchableAs(),
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                ],
                [
                    'name' => 'description',
                    'type' => 'string',
                ],
                [
                    'name' => 'content',
                    'type' => 'string',
                ],
                [
                    'name'  => 'source_name',
                    'type'  => 'string',
                    'facet' => true,
                ],
                [
                    'name'  => 'source_slug',
                    'type'  => 'string',
                    'facet' => true,
                ],
                [
                    'name' => 'category_names',
                    'type' => 'string',
                ],
                [
                    'name'  => 'category_slugs',
                    'type'  => 'string[]',
                    'facet' => true,
                ],
                [
                    'name' => 'author_names',
                    'type' => 'string',
                ],
                [
                    'name' => 'published_at',
                    'type' => 'int64',
                ],
            ],
            'default_sorting_field' => 'published_at',
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): mixed
    {
        return (string) $this->id;
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): mixed
    {
        return 'id';
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'articles';
    }

    /**
     * Modify the query used to retrieve models when making all searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['source', 'categories', 'authors']);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
