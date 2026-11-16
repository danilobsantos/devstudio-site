<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Models\Concerns\CanonicalTranslationKey;
use App\Models\Concerns\HasPublishedScope;
use App\Rules\PageSections;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

#[Fillable(['title', 'slug', 'excerpt', 'template', 'sections', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description', 'og_image', 'canonical_url'])]
class Page extends Model
{
    use CanonicalTranslationKey, HasPublishedScope;

    protected function casts(): array
    {
        return ['sections' => 'array', 'status' => PublicationStatus::class, 'published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            $sections = $page->sections;
            if (! is_array($sections)) {
                return;
            }
            $references = array_filter($sections, fn ($section) => is_array($section) && in_array($section['type'] ?? null, array_keys(PageSections::REFERENCE_TYPES), true));
            if ($references === []) {
                return;
            }
            $validator = Validator::make(
                ['locale' => $page->locale, 'sections' => array_values($references)],
                ['locale' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}(?:_[A-Z]{2})?$/'], 'sections' => [new PageSections($page->locale)]],
            );
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        });
    }

    /**
     * Seções prontas para o site público: remove referências não elegíveis ou de outro idioma e descarta seções vazias.
     * A elegibilidade da própria página é responsabilidade do chamador.
     */
    public function publishedSections(): array
    {
        if (! is_array($this->sections)) {
            return [];
        }
        $locale = is_string($this->locale) && preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/D', $this->locale) ? $this->locale : null;
        $visible = [];
        foreach (array_slice($this->sections, 0, PageSections::MAX_SECTIONS) as $section) {
            if (! is_array($section) || ! is_string($section['type'] ?? null) || ! is_array($section['data'] ?? null)) {
                continue;
            }
            $type = $section['type'];
            if (! isset(PageSections::REFERENCE_TYPES[$type])) {
                $visible[] = $section;

                continue;
            }
            [$model, $field] = PageSections::REFERENCE_TYPES[$type];
            $ids = $section['data'][$field] ?? null;
            if ($locale === null || ! is_array($ids)) {
                continue;
            }
            $allowed = $model::query()->whereKey($ids)->publishedInLocale($locale)->pluck('id')->all();
            $ids = array_values(array_filter($ids, fn ($id) => in_array($id, $allowed, true)));
            if ($ids === []) {
                continue;
            }
            $section['data'][$field] = $ids;
            $visible[] = $section;
        }

        return $visible;
    }
}
