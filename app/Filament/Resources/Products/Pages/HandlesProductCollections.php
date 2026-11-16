<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait HandlesProductCollections
{
    #[Locked]
    public array $rejectedCollections = [];

    #[Locked]
    public array $restoredCollections = [];

    private array $previousCollections = [];

    public function hydrate(): void
    {
        $this->restoredCollections = [];
    }

    public function updating(string $name, mixed $value): void
    {
        foreach (['features', 'audiences'] as $field) {
            if ($name === 'data.'.$field) {
                $incoming = $value;
            } elseif ($name === 'data' && is_array($value) && array_key_exists($field, $value)) {
                $incoming = $value[$field];
            } else {
                continue;
            }
            if (! is_array($incoming)) {
                $this->previousCollections[$field] = $incoming === null ? null : ($this->data[$field] ?? []);
            }
        }
    }

    public function updated(string $name, mixed $value): void
    {
        foreach (['features', 'audiences'] as $field) {
            if (array_key_exists($field, $this->previousCollections)) {
                $previous = $this->previousCollections[$field];
                $this->data[$field] = $previous ?? [];
                if ($previous !== null) {
                    $this->rejectedCollections[$field] = true;
                    $this->restoredCollections[$field] = true;
                    $this->addError('data.'.$field, 'Lista inválida.');
                } elseif (ProductResource::validItems($this->data[$field])) {
                    unset($this->rejectedCollections[$field]);
                }
                unset($this->previousCollections[$field]);
            } elseif (($name === 'data.'.$field || str_starts_with($name, 'data.'.$field.'.') || ($name === 'data' && is_array($value) && array_key_exists($field, $value))) && ProductResource::validItems($this->data[$field] ?? null)) {
                unset($this->rejectedCollections[$field]);
            }
        }
    }

    protected function ensureCollectionsValid(): void
    {
        foreach (['features', 'audiences'] as $field) {
            if ($this->rejectedCollections[$field] ?? false) {
                throw ValidationException::withMessages(['data.'.$field => 'Corrija a lista antes de salvar.']);
            }
        }
    }
}
