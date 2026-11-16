<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesProductCollections;

    protected static string $resource = ProductResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureCollectionsValid();
        ProductResource::validateRaw($this->data ?? [], $this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ProductResource::validatedData($data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
