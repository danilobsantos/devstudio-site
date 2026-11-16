<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function beforeValidate(): void
    {
        CategoryResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CategoryResource::validatedData($data);
    }
}
