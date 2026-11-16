<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\CanonicalTranslationKey;
use App\Models\Concerns\HasPublishedScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'slug', 'excerpt', 'body', 'category_id', 'tags', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description'])]
class Post extends Model
{
    use CanonicalTranslationKey, HasPublishedScope;

    protected static function booted(): void
    {
        static::saving(fn (Post $post) => Category::validateFor($post->category_id, 'post', $post->getConnectionName()));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return ['tags' => 'array', 'status' => PublicationStatus::class, 'published_at' => 'datetime'];
    }
}
