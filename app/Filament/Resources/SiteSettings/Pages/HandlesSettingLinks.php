<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait HandlesSettingLinks
{
    #[Locked]
    public array $rejectedLinks = [];

    private array $previousLinks = [];

    public function updating(string $name, mixed $value): void
    {
        foreach (['social_links', 'institutional_links'] as $field) {
            if ($name === 'data.'.$field) {
                $incoming = $value;
            } elseif ($name === 'data' && is_array($value) && array_key_exists($field, $value)) {
                $incoming = $value[$field];
            } else {
                continue;
            }
            if ($incoming !== null && ! is_array($incoming)) {
                $this->previousLinks[$field] = $this->data[$field] ?? [];
            }
        }
    }

    public function updated(string $name, mixed $value): void
    {
        foreach (['social_links', 'institutional_links'] as $field) {
            if (array_key_exists($field, $this->previousLinks)) {
                $this->data[$field] = $this->previousLinks[$field];
                $this->rejectedLinks[$field] = true;
                $this->addError('data.'.$field, 'Lista inválida.');
                unset($this->previousLinks[$field]);
            } elseif (($name === 'data.'.$field || str_starts_with($name, 'data.'.$field.'.') || ($name === 'data' && is_array($value) && array_key_exists($field, $value))) && SiteSettingResource::validLinks($this->data[$field] ?? null)) {
                unset($this->rejectedLinks[$field]);
            }
        }
    }

    protected function ensureLinksValid(): void
    {
        foreach (['social_links', 'institutional_links'] as $field) {
            if ($this->rejectedLinks[$field] ?? false) {
                throw ValidationException::withMessages(['data.'.$field => 'Corrija a lista antes de salvar.']);
            }
        }
    }
}
