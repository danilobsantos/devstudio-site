<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function beforeValidate(): void
    {
        CategoryResource::validateRaw($this->data ?? [], $this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CategoryResource::validatedData($data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->using(fn (Category $record) => CategoryResource::deleteRecord($record))->failureNotificationTitle('Categoria vinculada a conteúdo não pode ser excluída.'),
        ];
    }
}
