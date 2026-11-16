<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HandlesProductCollections;

    protected static string $resource = ProductResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureCollectionsValid();
        ProductResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ProductResource::validatedData($data);
    }
}
