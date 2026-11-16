<?php

namespace App\Filament\Resources\Users;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Usuários';

    public static function validateRaw(array $data, ?User $record = null): void
    {
        $allowed = ['name', 'email', 'role', 'password'];
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            if (! $record || ! in_array($key, ['id', 'email_verified_at', 'created_at', 'updated_at'], true) || $data[$key] !== ($record->attributesToArray()[$key] ?? null)) {
                throw ValidationException::withMessages(['data.name' => 'Campo não permitido.']);
            }
        }
    }

    public static function validatedData(array $data, ?User $record = null): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($record?->id)],
            'role' => ['required', Rule::in([UserRole::Admin->value, UserRole::Editor->value])],
            'password' => [$record ? 'nullable' : 'required', 'string', 'min:8'],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->messages() as $key => $messages) {
                $errors['data.'.$key] = $messages;
            }
            throw ValidationException::withMessages($errors);
        }

        $validated = $validator->validated();
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        return $validated;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nome')->required()->maxLength(255),
            TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255),
            Select::make('role')->label('Papel')->options([
                UserRole::Admin->value => 'Administrador',
                UserRole::Editor->value => 'Editor',
            ])->required(),
            TextInput::make('password')->label('Senha')->password()->minLength(8)->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)->dehydrated(fn ($state) => filled($state))->required(fn (string $operation): bool => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nome')->searchable(),
            TextColumn::make('email')->label('E-mail')->searchable(),
            TextColumn::make('role')->label('Papel')->badge()->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                'admin' => 'Administrador',
                'editor' => 'Editor',
                default => (string) $state,
            }),
            TextColumn::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i'),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
