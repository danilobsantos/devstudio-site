<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait CanonicalTranslationKey
{
    protected static function bootCanonicalTranslationKey(): void
    {
        static::saving(function (Model $model): void {
            $key = $model->getAttribute('translation_key');

            if (is_string($key)) {
                $model->setAttribute('translation_key', strtolower($key));
            }
        });
    }
}
