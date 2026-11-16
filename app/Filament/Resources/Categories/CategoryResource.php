<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function validateRaw(array $data, ?Category $record = null): void
    {
        foreach (array_diff(array_keys($data), ['name', 'slug', 'description', 'type']) as $key) {
            if (! $record || ! in_array($key, ['id', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.name' => 'Campo não permitido.']);
            }
        }
        if ($record && array_key_exists('type', $data) && $data['type'] !== $record->type) {
            throw ValidationException::withMessages(['data.type' => 'O tipo da categoria não pode ser alterado.']);
        }
    }

    public static function validatedData(array $data, ?Category $record = null): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        $plain = function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || trim($value) === '' || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('Texto inválido.');
            }
        };
        if ($record) {
            $data['type'] = $record->type;
        }
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', $plain],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories', 'slug')->ignore($record?->id)],
            'description' => ['nullable', 'string', 'max:2000', $plain],
            'type' => ['required', Rule::in(['post', 'project'])],
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

    public static function deleteRecord(Category $record): bool
    {
        try {
            return (bool) $record->delete();
        } catch (QueryException $exception) {
            $code = $exception->errorInfo[1] ?? null;
            if ($code === 1451 || ($code === 19 && str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed'))) {
                return false;
            }
            throw $exception;
        }
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(255),
            Textarea::make('description')->maxLength(2000),
            Select::make('type')->options(['post' => 'Artigo', 'project' => 'Projeto'])->required()->disabledOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('slug'), TextColumn::make('type')])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCategories::route('/'), 'create' => CreateCategory::route('/create'), 'edit' => EditCategory::route('/{record}/edit')];
    }
}
