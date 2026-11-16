<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Category;
use App\Models\Project;
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

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    public static function validateRaw(array $data, ?Project $record = null): void
    {
        $allowed = ['title', 'slug', 'client_name', 'category_id', 'summary', 'challenge', 'solution', 'results', 'technologies', 'external_url', 'is_featured', 'status', 'published_at', 'locale', 'translation_key', 'meta_title', 'meta_description', 'canonical_url'];
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            if (! $record || ! in_array($key, ['id', 'cover_image', 'gallery', 'og_image', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.title' => 'Campo não permitido.']);
            }
        }
        if (isset($data['technologies'])) {
            if (! is_array($data['technologies'])) {
                throw ValidationException::withMessages(['data.technologies' => 'Lista inválida.']);
            }
            foreach ($data['technologies'] as $item) {
                if (! is_array($item) || array_diff(array_keys($item), ['title', 'text'])) {
                    throw ValidationException::withMessages(['data.technologies' => 'Item inválido.']);
                }
            }
        }
    }

    public static function validatedData(array $data, ?Project $record = null): array
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
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('projects', 'slug')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
            'locale' => ['required', 'string', 'max:16', 'regex:/^[a-z]{2}(?:_[A-Z]{2})?$/'],
            'client_name' => ['nullable', 'string', 'max:255', $plain],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'project')],
            'summary' => ['nullable', 'string', 'max:2000', $plain],
            'challenge' => ['nullable', 'string', 'max:20000', $plain],
            'solution' => ['nullable', 'string', 'max:20000', $plain],
            'results' => ['nullable', 'string', 'max:20000', $plain],
            'technologies' => ['nullable', 'array', 'list', 'max:12'],
            'technologies.*' => ['required', 'array:title,text'],
            'technologies.*.title' => ['required', 'string', 'max:120', $plain],
            'technologies.*.text' => ['nullable', 'string', 'max:2000', $plain],
            'external_url' => ['nullable', 'string', 'max:2048', $url],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => PublicationDate::RULES,
            'translation_key' => ['nullable', 'uuid', Rule::unique('projects', 'translation_key')->where('locale', $data['locale'] ?? null)->ignore($record?->id)],
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
            TextInput::make('title')->required()->maxLength(70), TextInput::make('slug')->required()->maxLength(255),
            TextInput::make('locale')->required()->default('pt_BR')->maxLength(16), TextInput::make('client_name')->maxLength(255),
            Select::make('category_id')->options(fn () => Category::query()->where('type', 'project')->pluck('name', 'id')->all()),
            Textarea::make('summary')->maxLength(2000), Textarea::make('challenge')->maxLength(20000),
            Textarea::make('solution')->maxLength(20000), Textarea::make('results')->maxLength(20000),
            Repeater::make('technologies')->defaultItems(0)->maxItems(12)->afterStateUpdated(function ($state, $livewire) {
                if ($livewire->technologiesRejected && ! $livewire->technologiesRestored && ServiceResource::validFeatures($state)) {
                    $livewire->technologiesRejected = false;
                    $livewire->resetErrorBag('data.technologies');
                }
            })->schema([TextInput::make('title')->required()->maxLength(120), Textarea::make('text')->maxLength(2000)]),
            TextInput::make('external_url')->maxLength(2048), Toggle::make('is_featured')->default(false),
            Select::make('status')->options(['draft' => 'Rascunho', 'published' => 'Publicado'])->required()->default('draft'),
            TextInput::make('published_at')->type('datetime-local')->formatStateUsing(fn ($state) => PublicationDate::toDisplay($state)), TextInput::make('translation_key')->uuid(),
            TextInput::make('meta_title')->label('Título SEO (máx. 70 caracteres)')->maxLength(70), Textarea::make('meta_description')->label('Descrição SEO (máx. 200 caracteres)')->maxLength(200), TextInput::make('canonical_url')->maxLength(2048),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('title')->searchable(), TextColumn::make('slug'), TextColumn::make('locale'), TextColumn::make('status')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListProjects::route('/'), 'create' => CreateProject::route('/create'), 'edit' => EditProject::route('/{record}/edit')];
    }
}
