<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait HandlesTags
{
    #[Locked]
    public bool $tagsRejected = false;

    #[Locked]
    public bool $tagsRestored = false;

    private mixed $previousTags = null;

    private bool $restoreTags = false;

    public function hydrate(): void
    {
        $this->tagsRestored = false;
    }

    public function updating(string $name, mixed $value): void
    {
        if ($name === 'data.tags') {
            $incoming = $value;
        } elseif ($name === 'data' && is_array($value) && array_key_exists('tags', $value)) {
            $incoming = $value['tags'];
        } else {
            return;
        }
        if (! is_array($incoming)) {
            $this->previousTags = $incoming === null ? null : ($this->data['tags'] ?? []);
            $this->restoreTags = true;
        }
    }

    public function updated(string $name, mixed $value): void
    {
        if ($this->restoreTags) {
            $this->data['tags'] = $this->previousTags ?? [];
            if ($this->previousTags !== null) {
                $this->tagsRejected = true;
                $this->tagsRestored = true;
                $this->addError('data.tags', 'Lista inválida.');
            } elseif (ServiceResource::validFeatures($this->data['tags'])) {
                $this->tagsRejected = false;
            }
            $this->restoreTags = false;
        } elseif (($name === 'data.tags' || str_starts_with($name, 'data.tags.') || ($name === 'data' && is_array($value) && array_key_exists('tags', $value))) && ServiceResource::validFeatures($this->data['tags'] ?? null)) {
            $this->tagsRejected = false;
        }
    }

    protected function ensureTagsValid(): void
    {
        if ($this->tagsRejected) {
            throw ValidationException::withMessages(['data.tags' => 'Corrija a lista antes de salvar.']);
        }
    }
}
