<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    private mixed $previousFeatures = null;

    private bool $invalidFeatures = false;

    private bool $emptyFeatures = false;

    #[Locked]
    public bool $featuresRejected = false;

    #[Locked]
    public bool $featuresRestored = false;

    public function hydrate(): void
    {
        $this->featuresRestored = false;
    }

    public function updating(string $name, mixed $value): void
    {
        if (($name === 'data.features' && $value === null) || ($name === 'data' && is_array($value) && array_key_exists('features', $value) && $value['features'] === null)) {
            $this->emptyFeatures = true;
        } elseif (($name === 'data.features' && ! is_array($value)) || ($name === 'data' && is_array($value) && array_key_exists('features', $value) && ! is_array($value['features']))) {
            $this->previousFeatures = $this->data['features'] ?? [];
            $this->invalidFeatures = true;
        }
    }

    public function updated(string $name, mixed $value): void
    {
        if ($this->emptyFeatures) {
            $this->data['features'] = [];
            $this->emptyFeatures = false;
        }
        if ($this->invalidFeatures) {
            $this->data['features'] = $this->previousFeatures;
            $this->addError('data.features', 'Lista inválida.');
            $this->featuresRejected = true;
            $this->featuresRestored = true;
            $this->invalidFeatures = false;
        } elseif (($name === 'data.features' || str_starts_with($name, 'data.features.') || ($name === 'data' && is_array($value) && array_key_exists('features', $value))) && ServiceResource::validFeatures($this->data['features'] ?? null)) {
            $this->featuresRejected = false;
        }
    }

    protected function beforeValidate(): void
    {
        if ($this->featuresRejected) {
            throw ValidationException::withMessages(['data.features' => 'Corrija a lista antes de salvar.']);
        }
        ServiceResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ServiceResource::validatedData($data);
    }
}
