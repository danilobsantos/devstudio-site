<?php

namespace App\Http\Requests;

use App\Models\Service;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $trimOrNull = function (?string $value): ?string {
            if ($value === null) {
                return null;
            }
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        };

        $this->merge([
            'name' => $trimOrNull($this->input('name')),
            'company' => $trimOrNull($this->input('company')),
            'email' => $trimOrNull($this->input('email')),
            'phone' => $trimOrNull($this->input('phone')),
            'solution_type' => $trimOrNull($this->input('solution_type')),
            'service_id' => $this->input('solution_type') === 'service' ? $this->input('service_id') : null,
            'message' => $trimOrNull($this->input('message')),
            'source_url' => $trimOrNull($this->input('source_url')),
            'utm_source' => $trimOrNull($this->input('utm_source')),
            'utm_medium' => $trimOrNull($this->input('utm_medium')),
            'utm_campaign' => $trimOrNull($this->input('utm_campaign')),
        ]);
    }

    public function rules(): array
    {
        $plain = function (string $attribute, mixed $value, Closure $fail) {
            if (! is_string($value) || ! mb_check_encoding($value, 'UTF-8') || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value)) {
                $fail('O campo contém caracteres inválidos.');
            }
        };

        return [
            'name' => ['required', 'string', 'max:120', $plain],
            'company' => ['nullable', 'string', 'max:120', $plain],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\s()\-]+$/'],
            'solution_type' => ['required', 'string', Rule::in(['service', 'other'])],
            'service_id' => [
                Rule::requiredIf($this->input('solution_type') === 'service'),
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('status', 'published')->where('locale', 'pt_BR');
                }),
            ],
            'message' => ['required', 'string', 'max:2000', $plain],
            'privacy_accepted' => ['required', 'accepted'],
            'hp_verification' => ['nullable', 'max:0'],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'utm_medium' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
            'utm_campaign' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-\.]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Por favor, informe seu nome.',
            'name.max' => 'O nome não pode exceder 120 caracteres.',
            'email.required' => 'Por favor, informe um endereço de e-mail.',
            'email.email' => 'Por favor, informe um e-mail válido.',
            'phone.regex' => 'Por favor, informe um número de telefone válido.',
            'solution_type.required' => 'Selecione o tipo de solução de seu interesse.',
            'solution_type.in' => 'Opção de solução inválida.',
            'service_id.required_if' => 'Por favor, selecione qual serviço deseja contratar.',
            'service_id.exists' => 'O serviço selecionado não está disponível.',
            'message.required' => 'Descreva brevemente sua necessidade ou projeto.',
            'message.max' => 'A mensagem não pode exceder 2000 caracteres.',
            'privacy_accepted.accepted' => 'É necessário concordar com a Política de Privacidade para continuar.',
            'hp_verification.max' => 'Requisição inválida.',
        ];
    }
}
