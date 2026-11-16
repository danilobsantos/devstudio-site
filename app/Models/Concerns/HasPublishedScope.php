<?php

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

trait HasPublishedScope
{
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PublicationStatus::Published->value)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopePublishedInLocale(Builder $query, string $locale): void
    {
        if (! preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/D', $locale)) {
            throw new InvalidArgumentException('Locale inválido.');
        }

        $query->where('locale', $locale)->published();
    }
}
