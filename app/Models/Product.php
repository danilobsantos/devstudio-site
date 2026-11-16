<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\CanonicalTranslationKey;
use App\Models\Concerns\HasPublishedScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'tagline', 'description', 'features', 'audiences', 'cta_label', 'cta_url', 'external_url', 'sort_order', 'is_featured', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description', 'canonical_url'])]
class Product extends Model
{
    use CanonicalTranslationKey, HasPublishedScope;

    protected function casts(): array
    {
        return ['gallery' => 'array', 'features' => 'array', 'audiences' => 'array', 'is_featured' => 'boolean', 'status' => PublicationStatus::class, 'published_at' => 'datetime'];
    }
}
