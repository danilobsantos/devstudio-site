<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use HandlesTechnologies;

    protected static string $resource = ProjectResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureTechnologiesValid();
        ProjectResource::validateRaw($this->data ?? [], $this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ProjectResource::validatedData($data, $this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
