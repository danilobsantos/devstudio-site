<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait HandlesTechnologies
{
    #[Locked]
    public bool $technologiesRejected = false;

    #[Locked]
    public bool $technologiesRestored = false;

    private mixed $previousTechnologies = null;

    private bool $restoreTechnologies = false;

    public function hydrate(): void
    {
        $this->technologiesRestored = false;
    }

    public function updating(string $name, mixed $value): void
    {
        if ($name === 'data.technologies') {
            $incoming = $value;
        } elseif ($name === 'data' && is_array($value) && array_key_exists('technologies', $value)) {
            $incoming = $value['technologies'];
        } else {
            return;
        }
        if (! is_array($incoming)) {
            $this->previousTechnologies = $incoming === null ? null : ($this->data['technologies'] ?? []);
            $this->restoreTechnologies = true;
        }
    }

    public function updated(string $name, mixed $value): void
    {
        if ($this->restoreTechnologies) {
            $this->data['technologies'] = $this->previousTechnologies ?? [];
            if ($this->previousTechnologies !== null) {
                $this->technologiesRejected = true;
                $this->technologiesRestored = true;
                $this->addError('data.technologies', 'Lista inválida.');
            } elseif (ServiceResource::validFeatures($this->data['technologies'])) {
                $this->technologiesRejected = false;
            }
            $this->restoreTechnologies = false;
        } elseif (($name === 'data.technologies' || str_starts_with($name, 'data.technologies.') || ($name === 'data' && is_array($value) && array_key_exists('technologies', $value))) && ServiceResource::validFeatures($this->data['technologies'] ?? null)) {
            $this->technologiesRejected = false;
        }
    }

    protected function ensureTechnologiesValid(): void
    {
        if ($this->technologiesRejected) {
            throw ValidationException::withMessages(['data.technologies' => 'Corrija a lista antes de salvar.']);
        }
    }
}
