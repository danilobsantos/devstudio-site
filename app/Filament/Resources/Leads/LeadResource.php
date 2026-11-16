<?php

namespace App\Filament\Resources\Leads;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Models\Lead;
use Filament\Actions\EditAction;
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

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    public static function validateRaw(array $data, Lead $record): void
    {
        foreach (array_diff(array_keys($data), ['status', 'internal_notes']) as $key) {
            if (! array_key_exists($key, $record->attributesToArray()) || $data[$key] !== $record->attributesToArray()[$key]) {
                throw ValidationException::withMessages(['data.status' => 'Campo não permitido.']);
            }
        }
    }

    public static function validatedData(array $data): array
    {
        if (isset($data['internal_notes']) && is_string($data['internal_notes'])) {
            $data['internal_notes'] = trim($data['internal_notes']);
        }
        $plain = function (string $attribute, mixed $value, \Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('Texto inválido.');
            }
        };
        $validator = Validator::make($data, [
            'status' => ['required', Rule::in(array_column(LeadStatus::cases(), 'value'))],
            'internal_notes' => ['nullable', 'string', 'max:10000', $plain],
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
            TextInput::make('name')->disabled()->dehydrated(false),
            TextInput::make('company')->disabled()->dehydrated(false),
            TextInput::make('email')->disabled()->dehydrated(false),
            TextInput::make('phone')->disabled()->dehydrated(false),
            TextInput::make('solution_type')->disabled()->dehydrated(false),
            TextInput::make('service_id')->disabled()->dehydrated(false),
            Textarea::make('message')->disabled()->dehydrated(false),
            Textarea::make('source_url')->disabled()->dehydrated(false),
            TextInput::make('utm_source')->disabled()->dehydrated(false),
            TextInput::make('utm_medium')->disabled()->dehydrated(false),
            TextInput::make('utm_campaign')->disabled()->dehydrated(false),
            TextInput::make('privacy_accepted_at')->disabled()->dehydrated(false),
            Select::make('status')->options(['new' => 'Novo', 'in_progress' => 'Em andamento', 'qualified' => 'Qualificado', 'converted' => 'Convertido', 'discarded' => 'Descartado'])->required(),
            Textarea::make('internal_notes')->maxLength(10000),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('email'), TextColumn::make('status'), TextColumn::make('created_at')->dateTime()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListLeads::route('/'), 'edit' => EditLead::route('/{record}/edit')];
    }
}
