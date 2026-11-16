<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Rules\PageSections;
use App\Support\PublicationDate;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    private static function featureRules(): array
    {
        $plain = function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || trim($value) === '' || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('Texto inválido.');
            }
        };

        return [
            'features' => ['nullable', 'array', 'list', 'max:12'],
            'features.*' => ['required', 'array:title,text'],
            'features.*.title' => ['required', 'string', 'max:120', $plain],
            'features.*.text' => ['nullable', 'string', 'max:2000', $plain],
        ];
    }

    public static function validFeatures(mixed $features): bool
    {
        if ($features === null) {
            return true;
        }
        if (! is_array($features)) {
            return false;
        }

        return Validator::make(['features' => array_values($features)], self::featureRules())->passes();
    }

    public static function validateRaw(array $data, ?Service $record = null): void
    {
        $allowed = ['title', 'slug', 'excerpt', 'body', 'features', 'sort_order', 'is_featured', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description', 'canonical_url'];
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            if (! $record || ! in_array($key, ['id', 'icon', 'cover_image', 'og_image', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.title' => 'Campo não permitido.']);
            }
        }
        if (isset($data['features'])) {
            if (! is_array($data['features'])) {
                throw ValidationException::withMessages(['data.features' => 'Lista inválida.']);
            }
            foreach ($data['features'] as $feature) {
                if (! is_array($feature) || array_diff(array_keys($feature), ['title', 'text'])) {
                    throw ValidationException::withMessages(['data.features' => 'Item inválido.']);
                }
            }
        }
    }

    public static function validatedData(array $data, ?Service $record = null): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        if (is_string($data['translation_key'] ?? null)) {
            $data['translation_key'] = strtolower($data['translation_key']);
        }
        $plain = function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || trim($value) === '' || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('Texto inválido.');
            }
        };
        $url = function (string $attribute, mixed $value, \Closure $fail) {
            if (! (new PageSections)->url($value)) {
                $fail('URL inválida.');
            }
        };
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:70', $plain],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services', 'slug')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'locale' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}(?:_[A-Z]{2})?$/'],
            'excerpt' => ['nullable', 'string', 'max:2000', $plain],
            'body' => ['nullable', 'string', 'max:20000', $plain],
            ...self::featureRules(),
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => PublicationDate::RULES,
            'translation_key' => ['nullable', 'uuid', Rule::unique('services', 'translation_key')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'meta_title' => ['nullable', 'string', 'max:70', 'regex:/^[^<>\\x00-\\x1F\\x7F]*$/u'],
            'meta_description' => ['nullable', 'string', 'max:200', 'regex:/^[^<>\\x00-\\x1F\\x7F]*$/u'],
            'canonical_url' => ['nullable', 'string', 'max:2048', $url],
        ]);
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors['data.'.$key] = $messages;
            }
            throw ValidationException::withMessages($errors);
        }

        return PublicationDate::normalize($validator->validated());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(70),
            TextInput::make('slug')->required()->maxLength(255),
            TextInput::make('locale')->required()->default('pt_BR')->maxLength(16),
            Textarea::make('excerpt')->maxLength(2000),
            Textarea::make('body')->maxLength(20000),
            Repeater::make('features')->defaultItems(0)->maxItems(12)->afterStateUpdated(function ($state, $livewire) {
                if ($livewire->featuresRejected && ! $livewire->featuresRestored && self::validFeatures($state)) {
                    $livewire->featuresRejected = false;
                    $livewire->resetErrorBag('data.features');
                }
            })->schema([
                TextInput::make('title')->required()->maxLength(120),
                Textarea::make('text')->maxLength(2000),
            ]),
            TextInput::make('sort_order')->integer()->minValue(0)->maxValue(4294967295)->required()->default(0),
            Toggle::make('is_featured')->default(false),
            Select::make('status')->options(['draft' => 'Rascunho', 'published' => 'Publicado'])->required()->default('draft'),
            TextInput::make('published_at')->type('datetime-local')->formatStateUsing(fn ($state) => PublicationDate::toDisplay($state)),
            TextInput::make('translation_key')->uuid(),
            TextInput::make('meta_title')->label('Título SEO (máx. 70 caracteres)')->maxLength(70),
            Textarea::make('meta_description')->label('Descrição SEO (máx. 200 caracteres)')->maxLength(200),
            TextInput::make('canonical_url')->maxLength(2048),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable(),
            TextColumn::make('slug'),
            TextColumn::make('locale'),
            TextColumn::make('status'),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListServices::route('/'), 'create' => CreateService::route('/create'), 'edit' => EditService::route('/{record}/edit')];
    }
}
