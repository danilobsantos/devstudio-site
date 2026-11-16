<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeValidate(): void
    {
        UserResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return UserResource::validatedData($data);
    }
}
