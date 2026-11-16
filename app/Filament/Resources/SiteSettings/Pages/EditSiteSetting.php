<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    use HandlesSettingLinks;

    protected static string $resource = SiteSettingResource::class;

    protected function beforeValidate(): void
    {
        $this->ensureLinksValid();
        SiteSettingResource::validateRaw($this->data ?? [], $this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return SiteSettingResource::validatedData($data);
    }

    protected function afterSave(): void
    {
        if (array_key_exists('updated_at', $this->data ?? [])) {
            $this->data['updated_at'] = $this->record->attributesToArray()['updated_at'] ?? null;
        }
    }
}
