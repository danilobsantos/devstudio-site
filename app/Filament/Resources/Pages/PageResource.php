<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Rules\PageSections;
use App\Support\PublicationDate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function validateRawSections(mixed $sections): void
    {
        if (! is_array($sections)) {
            throw ValidationException::withMessages(['data.sections' => 'Seções inválidas.']);
        }
        $schemas = [
            'hero' => ['title', 'text', 'ctas'], 'services_grid' => ['title', 'service_ids'],
            'products_showcase' => ['title', 'product_ids'], 'projects_showcase' => ['title', 'project_ids'],
            'text_image' => ['title', 'text'], 'features_list' => ['title', 'items'],
            'faq' => ['title', 'items'], 'cta' => ['title', 'text', 'label', 'url'],
        ];
        foreach ($sections as $section) {
            if (! is_array($section) || array_diff(array_keys($section), ['type', 'data']) || ! is_string($section['type'] ?? null) || ! isset($schemas[$section['type']]) || ! is_array($section['data'] ?? null) || array_diff(array_keys($section['data']), $schemas[$section['type']])) {
                throw ValidationException::withMessages(['data.sections' => 'Seção inválida.']);
            }
            foreach (['ctas' => ['label', 'url'], 'items' => $section['type'] === 'faq' ? ['question', 'answer'] : ['title', 'text'], 'service_ids' => ['id'], 'product_ids' => ['id'], 'project_ids' => ['id']] as $field => $keys) {
                $items = $section['data'][$field] ?? null;
                if ($items === null) {
                    continue;
                }
                if (! is_array($items)) {
                    throw ValidationException::withMessages(['data.sections' => 'Itens inválidos.']);
                }
                foreach ($items as $item) {
                    if (in_array($field, ['service_ids', 'product_ids', 'project_ids'], true) && ! is_array($item)) {
                        continue;
                    }
                    if (! is_array($item) || array_diff(array_keys($item), $keys)) {
                        throw ValidationException::withMessages(['data.sections' => 'Itens inválidos.']);
                    }
                }
            }
        }
    }

    public static function validatedData(array $data, ?Page $record = null): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        if (is_string($data['translation_key'] ?? null)) {
            $data['translation_key'] = strtolower($data['translation_key']);
        }
        if (isset($data['sections']) && is_array($data['sections'])) {
            foreach ($data['sections'] as &$section) {
                if (($section['type'] ?? null) === 'hero' && (($section['data']['ctas'] ?? null) === null || $section['data']['ctas'] === [])) {
                    unset($section['data']['ctas']);
                }
                foreach (['service_ids', 'product_ids', 'project_ids'] as $key) {
                    if (isset($section['data'][$key]) && is_array($section['data'][$key])) {
                        foreach ($section['data'][$key] as &$id) {
                            if (is_string($id) && ctype_digit($id) && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
                                $id = (int) $id;
                            }
                        }
                        unset($id);
                    }
                }
            }
            unset($section);
        }
        $rule = new PageSections($data['locale'] ?? null);
        $plain = function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('Texto inválido.');
            }
        };
        $url = function (string $attribute, mixed $value, \Closure $fail) use ($rule) {
            if (! $rule->url($value)) {
                $fail('URL inválida.');
            }
        };

        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:70', $plain],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'locale' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}(?:_[A-Z]{2})?$/'],
            'template' => ['required', Rule::in(['standard', 'landing'])],
            'excerpt' => ['nullable', 'string', 'max:2000', $plain],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => PublicationDate::RULES,
            'translation_key' => ['nullable', 'uuid', Rule::unique('pages', 'translation_key')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'meta_title' => ['nullable', 'string', 'max:70', 'regex:/^[^<>\\x00-\\x1F\\x7F]*$/u'],
            'meta_description' => ['nullable', 'string', 'max:200', 'regex:/^[^<>\\x00-\\x1F\\x7F]*$/u'],
            'canonical_url' => ['nullable', 'string', 'max:2048', $url],
            'sections' => ['present', 'array', $rule],
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

    public static function asFormValidation(ValidationException $exception): ValidationException
    {
        $errors = [];
        foreach ($exception->errors() as $key => $messages) {
            $errors[str_starts_with($key, 'data.') ? $key : 'data.'.$key] = $messages;
        }

        return ValidationException::withMessages($errors);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(70),
                TextInput::make('slug')->required()->maxLength(255),
                TextInput::make('locale')->required()->default('pt_BR')->maxLength(16),
                Select::make('template')->options(['standard' => 'Padrão', 'landing' => 'Landing'])->required(),
                Textarea::make('excerpt')->maxLength(2000),
                Select::make('status')->options(['draft' => 'Rascunho', 'published' => 'Publicado'])->required()->default('draft'),
                TextInput::make('published_at')->type('datetime-local')->formatStateUsing(fn ($state) => PublicationDate::toDisplay($state)),
                TextInput::make('translation_key')->uuid(),
                TextInput::make('meta_title')->label('Título SEO (máx. 70 caracteres)')->maxLength(70),
                Textarea::make('meta_description')->label('Descrição SEO (máx. 200 caracteres)')->maxLength(200),
                TextInput::make('canonical_url')->maxLength(2048),
                Builder::make('sections')->maxItems(20)->blocks([
                    Builder\Block::make('hero')->schema([
                        TextInput::make('title')->required()->maxLength(120),
                        Textarea::make('text')->maxLength(2000),
                        Repeater::make('ctas')->maxItems(2)->schema([TextInput::make('label')->required()->maxLength(80), TextInput::make('url')->required()->maxLength(2048)]),
                    ]),
                    ...array_map(fn (string $type, string $field) => Builder\Block::make($type)->schema([
                        TextInput::make('title')->required()->maxLength(120),
                        Repeater::make($field)->simple(TextInput::make('id')->integer()->minValue(1)->required())->minItems(1)->maxItems(12),
                    ]), ['services_grid', 'products_showcase', 'projects_showcase'], ['service_ids', 'product_ids', 'project_ids']),
                    Builder\Block::make('text_image')->schema([TextInput::make('title')->required()->maxLength(120), Textarea::make('text')->required()->maxLength(2000)]),
                    Builder\Block::make('features_list')->schema([TextInput::make('title')->required()->maxLength(120), Repeater::make('items')->minItems(1)->maxItems(12)->schema([TextInput::make('title')->required()->maxLength(120), Textarea::make('text')->maxLength(2000)])]),
                    Builder\Block::make('faq')->schema([TextInput::make('title')->required()->maxLength(120), Repeater::make('items')->minItems(1)->maxItems(12)->schema([TextInput::make('question')->required()->maxLength(200), Textarea::make('answer')->required()->maxLength(2000)])]),
                    Builder\Block::make('cta')->schema([TextInput::make('title')->required()->maxLength(120), Textarea::make('text')->maxLength(2000), TextInput::make('label')->required()->maxLength(80), TextInput::make('url')->required()->maxLength(2048)]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('locale'),
                TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
