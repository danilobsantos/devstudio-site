<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Product;
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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function validateRaw(array $data, ?Product $record = null): void
    {
        $allowed = ['name', 'slug', 'tagline', 'description', 'features', 'audiences', 'cta_label', 'cta_url', 'external_url', 'sort_order', 'is_featured', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description', 'canonical_url'];
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            if (! $record || ! in_array($key, ['id', 'logo_path', 'cover_path', 'gallery', 'og_image', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.name' => 'Campo não permitido.']);
            }
        }
        foreach (['features', 'audiences'] as $field) {
            if (! isset($data[$field])) {
                continue;
            }
            if (! is_array($data[$field])) {
                throw ValidationException::withMessages(['data.'.$field => 'Lista inválida.']);
            }
            foreach ($data[$field] as $item) {
                if (! is_array($item) || array_diff(array_keys($item), ['title', 'text'])) {
                    throw ValidationException::withMessages(['data.'.$field => 'Item inválido.']);
                }
            }
        }
    }

    public static function validItems(mixed $items): bool
    {
        return ServiceResource::validFeatures($items);
    }

    public static function validatedData(array $data, ?Product $record = null): array
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
            'name' => ['required', 'string', 'max:70', $plain],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('products', 'slug')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'locale' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}(?:_[A-Z]{2})?$/'],
            'tagline' => ['nullable', 'string', 'max:255', $plain],
            'description' => ['nullable', 'string', 'max:20000', $plain],
            'features' => ['nullable', 'array', 'list', 'max:12'],
            'features.*' => ['required', 'array:title,text'],
            'features.*.title' => ['required', 'string', 'max:120', $plain],
            'features.*.text' => ['nullable', 'string', 'max:2000', $plain],
            'audiences' => ['nullable', 'array', 'list', 'max:12'],
            'audiences.*' => ['required', 'array:title,text'],
            'audiences.*.title' => ['required', 'string', 'max:120', $plain],
            'audiences.*.text' => ['nullable', 'string', 'max:2000', $plain],
            'cta_label' => ['nullable', 'required_with:cta_url', 'string', 'max:80', $plain],
            'cta_url' => ['nullable', 'required_with:cta_label', 'string', 'max:2048', $url],
            'external_url' => ['nullable', 'string', 'max:2048', $url],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => PublicationDate::RULES,
            'translation_key' => ['nullable', 'uuid', Rule::unique('products', 'translation_key')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
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
            TextInput::make('name')->required()->maxLength(70), TextInput::make('slug')->required()->maxLength(255),
            TextInput::make('locale')->required()->default('pt_BR')->maxLength(16), TextInput::make('tagline')->maxLength(255),
            Textarea::make('description')->maxLength(20000),
            ...array_map(fn ($field) => Repeater::make($field)->defaultItems(0)->maxItems(12)->afterStateUpdated(function ($state, $livewire) use ($field) {
                if (($livewire->rejectedCollections[$field] ?? false) && ! ($livewire->restoredCollections[$field] ?? false) && self::validItems($state)) {
                    $livewire->rejectedCollections = array_diff_key($livewire->rejectedCollections, [$field => true]);
                    $livewire->resetErrorBag('data.'.$field);
                }
            })->schema([TextInput::make('title')->required()->maxLength(120), Textarea::make('text')->maxLength(2000)]), ['features', 'audiences']),
            TextInput::make('cta_label')->maxLength(80), TextInput::make('cta_url')->maxLength(2048), TextInput::make('external_url')->maxLength(2048),
            TextInput::make('sort_order')->integer()->minValue(0)->maxValue(4294967295)->required()->default(0), Toggle::make('is_featured')->default(false),
            Select::make('status')->options(['draft' => 'Rascunho', 'published' => 'Publicado'])->required()->default('draft'),
            TextInput::make('published_at')->type('datetime-local')->formatStateUsing(fn ($state) => PublicationDate::toDisplay($state)), TextInput::make('translation_key')->uuid(),
            TextInput::make('meta_title')->label('Título SEO (máx. 70 caracteres)')->maxLength(70), Textarea::make('meta_description')->label('Descrição SEO (máx. 200 caracteres)')->maxLength(200), TextInput::make('canonical_url')->maxLength(2048),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('slug'), TextColumn::make('locale'), TextColumn::make('status')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListProducts::route('/'), 'create' => CreateProduct::route('/create'), 'edit' => EditProduct::route('/{record}/edit')];
    }
}
