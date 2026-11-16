<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use HandlesTechnologies;

    protected static string $resource = ProjectResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureTechnologiesValid();
        ProjectResource::validateRaw($this->data ?? []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ProjectResource::validatedData($data);
    }
}
