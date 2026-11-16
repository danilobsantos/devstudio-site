<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\CreateSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use App\Rules\PageSections;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    public static function validateRaw(array $data, ?SiteSetting $record = null): void
    {
        $allowed = ['display_name', 'email', 'phone', 'whatsapp', 'address', 'meta_title', 'meta_description', 'canonical_url', 'social_links', 'institutional_links'];
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            if (! $record || ! in_array($key, ['id', 'singleton_guard', 'logo_light_background', 'logo_dark_background', 'icon', 'og_image', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.display_name' => 'Campo não permitido.']);
            }
        }
    }

    public static function validLinks(mixed $links): bool
    {
        if ($links === null) {
            return true;
        }
        if (! is_array($links) || count($links) > 12) {
            return false;
        }
        foreach ($links as $label => $url) {
            if (! is_string($label) || ! mb_check_encoding($label, 'UTF-8') || trim($label) !== $label || $label === '' || mb_strlen($label) > 80 || preg_match('/[<>\x00-\x1F\x7F]/u', $label) || ! (new PageSections)->url($url)) {
                return false;
            }
        }

        return true;
    }

    public static function validatedData(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        $plain = function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('Texto inválido.');
            }
        };
        $url = function (string $attribute, mixed $value, \Closure $fail) {
            if (! (new PageSections)->url($value)) {
                $fail('URL inválida.');
            }
        };
        $validator = Validator::make($data, [
            'display_name' => ['nullable', 'string', 'max:255', $plain],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[+0-9 ()-]+$/'],
            'whatsapp' => ['nullable', 'string', 'regex:/^[1-9][0-9]{7,14}$/'],
            'address' => ['nullable', 'string', 'max:2000', $plain],
            'meta_title' => ['nullable', 'string', 'max:255', $plain],
            'meta_description' => ['nullable', 'string', 'max:2000', $plain],
            'canonical_url' => ['nullable', 'string', 'max:2048', $url],
            'social_links' => ['nullable', 'array', function (string $attribute, mixed $value, \Closure $fail) {
                if (! self::validLinks($value)) {
                    $fail('Links inválidos.');
                }
            }],
            'institutional_links' => ['nullable', 'array', function (string $attribute, mixed $value, \Closure $fail) {
                if (! self::validLinks($value)) {
                    $fail('Links inválidos.');
                }
            }],
        ]);
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors['data.'.$key] = $messages;
            }
            throw ValidationException::withMessages($errors);
        }

        return $validator->validated();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('display_name')->maxLength(255), TextInput::make('email')->email()->maxLength(255),
            TextInput::make('phone')->maxLength(32), TextInput::make('whatsapp')->maxLength(15),
            Textarea::make('address')->maxLength(2000), TextInput::make('meta_title')->maxLength(255),
            Textarea::make('meta_description')->maxLength(2000), TextInput::make('canonical_url')->maxLength(2048),
            KeyValue::make('social_links')->keyLabel('Rede')->valueLabel('URL'),
            KeyValue::make('institutional_links')->keyLabel('Nome')->valueLabel('URL'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('display_name'), TextColumn::make('email')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSiteSettings::route('/'), 'create' => CreateSiteSetting::route('/create'), 'edit' => EditSiteSetting::route('/{record}/edit')];
    }
}
