<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeValidate(): void
    {
        UserResource::validateRaw($this->data ?? [], $this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return UserResource::validatedData($data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
