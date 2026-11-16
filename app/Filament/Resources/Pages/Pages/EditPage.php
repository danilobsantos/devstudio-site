<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function beforeValidate(): void
    {
        PageResource::validateRawSections($this->data['sections'] ?? []);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageResource::validatedData($data, $this->record);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (ValidationException $exception) {
            throw PageResource::asFormValidation($exception);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
