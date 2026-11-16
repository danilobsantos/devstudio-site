<?php

namespace App\Rules;

use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PageSections implements ValidationRule
{
    public const MAX_SECTIONS = 20;

    /** @var array<string, array{0: class-string, 1: string}> */
    public const REFERENCE_TYPES = [
        'services_grid' => [Service::class, 'service_ids'],
        'products_showcase' => [Product::class, 'product_ids'],
        'projects_showcase' => [Project::class, 'project_ids'],
    ];

    private bool $referenceMismatch = false;

    public function __construct(private ?string $locale = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->referenceMismatch = false;
        if (! is_array($value) || ! array_is_list($value) || count($value) > self::MAX_SECTIONS) {
            $fail('A lista de seções é inválida.');

            return;
        }

        foreach ($value as $section) {
            if (! $this->validSection($section)) {
                $fail($this->referenceMismatch
                    ? 'Serviços, produtos e projetos referenciados precisam estar publicados e no mesmo idioma da página.'
                    : 'Uma seção contém dados inválidos ou não autorizados.');

                return;
            }
        }
    }

    private function validSection(mixed $section): bool
    {
        if (! is_array($section) || array_diff(array_keys($section), ['type', 'data']) || ! isset($section['type'], $section['data']) || ! is_string($section['type']) || ! is_array($section['data'])) {
            return false;
        }

        $data = $section['data'];
        $type = $section['type'];
        $allowed = match ($type) {
            'hero' => ['title', 'text', 'ctas'],
            'services_grid' => ['title', 'service_ids'],
            'products_showcase' => ['title', 'product_ids'],
            'projects_showcase' => ['title', 'project_ids'],
            'text_image' => ['title', 'text'],
            'features_list', 'faq' => ['title', 'items'],
            'cta' => ['title', 'text', 'label', 'url'],
            default => null,
        };
        if ($allowed === null || array_diff(array_keys($data), $allowed) || ! $this->text($data['title'] ?? null, 120)) {
            return false;
        }
        if (array_key_exists('text', $data) && $data['text'] !== null && ! $this->text($data['text'], 2000)) {
            return false;
        }

        return match ($type) {
            'hero' => ! isset($data['ctas']) || $this->items($data['ctas'], 2, ['label' => 80, 'url' => 2048]),
            'services_grid', 'products_showcase', 'projects_showcase' => $this->references($data[self::REFERENCE_TYPES[$type][1]] ?? null, self::REFERENCE_TYPES[$type][0]),
            'text_image' => $this->text($data['text'] ?? null, 2000),
            'features_list' => $this->items($data['items'] ?? null, 12, ['title' => 120, 'text?' => 2000]),
            'faq' => $this->items($data['items'] ?? null, 12, ['question' => 200, 'answer' => 2000]),
            'cta' => $this->text($data['label'] ?? null, 80) && $this->url($data['url'] ?? null),
        };
    }

    private function text(mixed $value, int $max): bool
    {
        return is_string($value) && mb_check_encoding($value, 'UTF-8') && trim($value) !== '' && mb_strlen(trim($value)) <= $max && ! preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value);
    }

    public function url(mixed $value): bool
    {
        if (! $this->text($value, 2048) || $value !== trim($value) || preg_match('/[\s\\\\]/u', $value) || preg_match('/%(?![0-9a-f]{2})/i', $value)) {
            return false;
        }
        $decoded = rawurldecode($value);
        if (! mb_check_encoding($decoded, 'UTF-8') || preg_match('/[\s\\\\\x00-\x1f\x7f]/u', $decoded) || str_contains($decoded, '%') || str_starts_with($decoded, '//')) {
            return false;
        }
        if (str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
            return str_starts_with($decoded, '/') && ! str_starts_with($decoded, '//');
        }
        $parts = parse_url($value);

        return filter_var($value, FILTER_VALIDATE_URL) !== false && is_array($parts) && ($parts['scheme'] ?? '') === 'https' && ! empty($parts['host']) && ! isset($parts['user']) && ! isset($parts['pass']) && parse_url($decoded, PHP_URL_HOST) === $parts['host'];
    }

    private function items(mixed $items, int $max, array $fields): bool
    {
        if (! is_array($items) || ! array_is_list($items) || count($items) < 1 || count($items) > $max) {
            return false;
        }
        $keys = array_map(fn ($key) => rtrim($key, '?'), array_keys($fields));
        foreach ($items as $item) {
            if (! is_array($item) || array_diff(array_keys($item), $keys)) {
                return false;
            }
            foreach ($fields as $field => $limit) {
                $optional = str_ends_with($field, '?');
                $field = rtrim($field, '?');
                $value = $item[$field] ?? null;
                if ($optional && $value === null) {
                    continue;
                }
                if ($field === 'url' ? ! $this->url($value) : ! $this->text($value, $limit)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function references(mixed $ids, string $model): bool
    {
        if (! is_array($ids) || ! array_is_list($ids) || count($ids) < 1 || count($ids) > 12 || count(array_unique($ids, SORT_REGULAR)) !== count($ids)) {
            $this->referenceMismatch = true;

            return false;
        }
        foreach ($ids as $id) {
            if (! is_int($id) || $id < 1) {
                $this->referenceMismatch = true;

                return false;
            }
        }
        if ($this->locale === null || ! preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/D', $this->locale)) {
            $this->referenceMismatch = true;

            return false;
        }
        $matches = $model::query()->whereKey($ids)->publishedInLocale($this->locale)->count() === count($ids);
        $this->referenceMismatch = ! $matches;

        return $matches;
    }
}
