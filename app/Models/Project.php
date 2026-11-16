<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\CanonicalTranslationKey;
use App\Models\Concerns\HasPublishedScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'slug', 'client_name', 'category_id', 'summary', 'challenge', 'solution', 'results', 'technologies', 'external_url', 'is_featured', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description', 'canonical_url'])]
class Project extends Model
{
    use CanonicalTranslationKey, HasPublishedScope;

    protected static function booted(): void
    {
        static::saving(fn (Project $project) => Category::validateFor($project->category_id, 'project', $project->getConnectionName()));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return ['technologies' => 'array', 'gallery' => 'array', 'is_featured' => 'boolean', 'status' => PublicationStatus::class, 'published_at' => 'datetime'];
    }
}
