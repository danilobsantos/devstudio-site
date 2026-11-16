<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function beforeValidate(): void
    {
        PageResource::validateRawSections($this->data['sections'] ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PageResource::validatedData($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (ValidationException $exception) {
            throw PageResource::asFormValidation($exception);
        }
    }
}
