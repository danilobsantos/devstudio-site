<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class CreateSiteSetting extends CreateRecord
{
    use HandlesSettingLinks;

    protected static string $resource = SiteSettingResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureLinksValid();
        SiteSettingResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SiteSettingResource::validatedData($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (UniqueConstraintViolationException $exception) {
            if (! SiteSetting::query()->exists()) {
                throw $exception;
            }
            throw ValidationException::withMessages(['data.display_name' => 'Já existe uma configuração global.']);
        }
    }
}
