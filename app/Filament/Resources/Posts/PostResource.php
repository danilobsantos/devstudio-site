<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Category;
use App\Models\Post;
use App\Support\PublicationDate;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    public static function validateRaw(array $data, ?Post $record = null): void
    {
        $allowed = ['title', 'slug', 'excerpt', 'body', 'category_id', 'tags', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description'];
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            if (! $record || ! in_array($key, ['id', 'author_id', 'cover_image', 'og_image', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.title' => 'Campo não permitido.']);
            }
        }
        if (isset($data['tags'])) {
            if (! is_array($data['tags'])) {
                throw ValidationException::withMessages(['data.tags' => 'Lista inválida.']);
            }
            foreach ($data['tags'] as $tag) {
                if (! is_array($tag) || array_diff(array_keys($tag), ['title', 'text'])) {
                    throw ValidationException::withMessages(['data.tags' => 'Item inválido.']);
                }
            }
        }
    }

    public static function validatedData(array $data, ?Post $record = null): array
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
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:70', $plain],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('posts', 'slug')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'locale' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}(?:_[A-Z]{2})?$/'],
            'excerpt' => ['nullable', 'string', 'max:2000', $plain], 'body' => ['nullable', 'string', 'max:20000', $plain],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'post')],
            'tags' => ['nullable', 'array', 'list', 'max:12'], 'tags.*' => ['required', 'array:title,text'],
            'tags.*.title' => ['required', 'string', 'max:120', $plain], 'tags.*.text' => ['nullable', 'string', 'max:2000', $plain],
            'status' => ['required', Rule::in(['draft', 'published'])], 'published_at' => PublicationDate::RULES,
            'translation_key' => ['nullable', 'uuid', Rule::unique('posts', 'translation_key')->where('locale', $data['locale'] ?? null)->ignore($record?->id)], 'meta_title' => ['nullable', 'string', 'max:70', 'regex:/^[^<>\\x00-\\x1F\\x7F]*$/u'],
            'meta_description' => ['nullable', 'string', 'max:200', 'regex:/^[^<>\\x00-\\x1F\\x7F]*$/u'],
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
            TextInput::make('title')->required()->maxLength(70), TextInput::make('slug')->required()->maxLength(255),
            TextInput::make('locale')->required()->default('pt_BR')->maxLength(16),
            Select::make('category_id')->options(fn () => Category::query()->where('type', 'post')->pluck('name', 'id')->all()),
            Textarea::make('excerpt')->maxLength(2000), Textarea::make('body')->maxLength(20000),
            Repeater::make('tags')->defaultItems(0)->maxItems(12)->afterStateUpdated(function ($state, $livewire) {
                if ($livewire->tagsRejected && ! $livewire->tagsRestored && ServiceResource::validFeatures($state)) {
                    $livewire->tagsRejected = false;
                    $livewire->resetErrorBag('data.tags');
                }
            })->schema([TextInput::make('title')->required()->maxLength(120), Textarea::make('text')->maxLength(2000)]),
            Select::make('status')->options(['draft' => 'Rascunho', 'published' => 'Publicado'])->required()->default('draft'),
            TextInput::make('published_at')->type('datetime-local')->formatStateUsing(fn ($state) => PublicationDate::toDisplay($state)), TextInput::make('translation_key')->uuid(),
            TextInput::make('meta_title')->label('Título SEO (máx. 70 caracteres)')->maxLength(70), Textarea::make('meta_description')->label('Descrição SEO (máx. 200 caracteres)')->maxLength(200),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable(), TextColumn::make('slug'), TextColumn::make('locale'), TextColumn::make('status')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPosts::route('/'), 'create' => CreatePost::route('/create'), 'edit' => EditPost::route('/{record}/edit')];
    }
}
