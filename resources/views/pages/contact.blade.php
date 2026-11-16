@extends('layouts.public')

@section('content')
<main id="main-content">
    <x-breadcrumbs :items="[['label' => 'Início', 'url' => url('/')], ['label' => 'Contato', 'current' => true]]" />

    <header style="margin-bottom: 2rem;">
        <h1 style="font-size: clamp(1.75rem, 3vw, 2.5rem); margin-bottom: 0.5rem;">Fale com a DevStudio</h1>
        <p style="color: var(--theme-text); opacity: 0.85; max-width: 45rem; font-size: 1.125rem;">
            Conte-nos sobre o seu desafio ou projeto. Desenvolvemos soluções sob medida para acelerar e digitalizar sua operação.
        </p>
    </header>

    @if (session('status') === 'success')
        <div class="ui-card" role="alert" style="border-left: 4px solid #10B981; margin-bottom: 2rem; background: color-mix(in srgb, #10B981 8%, var(--theme-background));">
            <h2 style="font-size: 1.25rem; color: #10B981; margin-top: 0; margin-bottom: 0.5rem;">Mensagem enviada com sucesso!</h2>
            <p style="margin: 0;">Recebemos suas informações e entraremos em contato o mais breve possível.</p>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr)); gap: 2rem; align-items: start;">
        {{-- Formulário Comercial --}}
        <div class="ui-card">
            <form action="{{ route('contact.submit') }}" method="POST" novalidate>
                @csrf

                {{-- Honeypot antispam invisível --}}
                <div style="display: none !important;" aria-hidden="true">
                    <label for="hp_verification">Não preencha este campo</label>
                    <input type="text" id="hp_verification" name="hp_verification" tabindex="-1" autocomplete="off">
                </div>

                {{-- Tracking oculto --}}
                <input type="hidden" name="source_url" value="{{ old('source_url', request()->headers->get('referer') ?? url()->current()) }}">
                <input type="hidden" name="utm_source" value="{{ old('utm_source', request()->query('utm_source')) }}">
                <input type="hidden" name="utm_medium" value="{{ old('utm_medium', request()->query('utm_medium')) }}">
                <input type="hidden" name="utm_campaign" value="{{ old('utm_campaign', request()->query('utm_campaign')) }}">

                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    {{-- Nome --}}
                    <div>
                        <label for="name" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Nome completo <span aria-hidden="true" style="color: var(--color-brand-500);">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit;"
                               @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                        @error('name')
                            <p id="name-error" style="color: var(--color-brand-500); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Empresa e E-mail --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 1rem;">
                        <div>
                            <label for="company" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Empresa <span style="font-size: 0.8rem; font-weight: normal; opacity: 0.7;">(opcional)</span></label>
                            <input type="text" id="company" name="company" value="{{ old('company') }}"
                                   style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit;">
                        </div>
                        <div>
                            <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">E-mail corporativo <span aria-hidden="true" style="color: var(--color-brand-500);">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit;"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            @error('email')
                                <p id="email-error" style="color: var(--color-brand-500); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Telefone e Tipo de Solução --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 1rem;">
                        <div>
                            <label for="phone" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">WhatsApp / Telefone</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="(11) 99999-9999"
                                   style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit;"
                                   @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                            @error('phone')
                                <p id="phone-error" style="color: var(--color-brand-500); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="solution_type" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Interesse principal <span aria-hidden="true" style="color: var(--color-brand-500);">*</span></label>
                            <select id="solution_type" name="solution_type" required
                                    style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit;"
                                    onchange="document.getElementById('service_select_group').style.display = this.value === 'service' ? 'block' : 'none';">
                                <option value="service" {{ old('solution_type', $selectedServiceId ? 'service' : 'service') === 'service' ? 'selected' : '' }}>Serviço / Solução de Tecnologia</option>
                                <option value="other" {{ old('solution_type') === 'other' ? 'selected' : '' }}>Outro / Projeto Personalizado</option>
                            </select>
                        </div>
                    </div>

                    {{-- Seleção de Serviço (se solution_type for service) --}}
                    <div id="service_select_group" style="display: {{ old('solution_type', 'service') === 'service' ? 'block' : 'none' }};">
                        <label for="service_id" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Qual solução melhor atende sua empresa?</label>
                        <select id="service_id" name="service_id"
                                style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit;">
                            <option value="">Selecione uma solução...</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" {{ (string) old('service_id', $selectedServiceId) === (string) $service->id ? 'selected' : '' }}>
                                    {{ $service->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <p style="color: var(--color-brand-500); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mensagem --}}
                    <div>
                        <label for="message" style="display: block; font-weight: 600; margin-bottom: 0.35rem;">Como podemos ajudar? <span aria-hidden="true" style="color: var(--color-brand-500);">*</span></label>
                        <textarea id="message" name="message" rows="4" required placeholder="Descreva brevemente sua necessidade ou o que deseja desenvolver..."
                                  style="width: 100%; box-sizing: border-box; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: 0.375rem; background: var(--theme-background); color: inherit; font: inherit; resize: vertical;"
                                  @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')
                            <p id="message-error" style="color: var(--color-brand-500); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Consentimento de Privacidade --}}
                    <div style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 0.25rem;">
                        <input type="checkbox" id="privacy_accepted" name="privacy_accepted" value="1" {{ old('privacy_accepted') ? 'checked' : '' }} required
                               style="margin-top: 0.25rem; accent-color: var(--color-brand-600);"
                               @error('privacy_accepted') aria-invalid="true" aria-describedby="privacy-error" @enderror>
                        <label for="privacy_accepted" style="font-size: 0.875rem; line-height: 1.4;">
                            Concordo com o tratamento de meus dados para fins de contato comercial, conforme a Política de Privacidade da DevStudio. <span aria-hidden="true" style="color: var(--color-brand-500);">*</span>
                        </label>
                    </div>
                    @error('privacy_accepted')
                        <p id="privacy-error" style="color: var(--color-brand-500); font-size: 0.875rem; margin-top: -0.5rem;">{{ $message }}</p>
                    @enderror

                    <div>
                        <button type="submit" class="ui-button ui-button--primary" style="width: 100%; cursor: pointer;">
                            Enviar mensagem
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Painel Lateral: Contato Direto e WhatsApp --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @if ($whatsAppUrl)
                <div class="ui-cta-banner" style="background: color-mix(in srgb, #25D366 10%, var(--theme-background)); border-color: color-mix(in srgb, #25D366 30%, var(--color-border));">
                    <h2 style="font-size: 1.25rem; margin-top: 0; margin-bottom: 0.5rem;">Prefere atendimento ágil?</h2>
                    <p style="margin-bottom: 1.25rem; font-size: 0.95rem;">Fale diretamente com um especialista comercial pelo WhatsApp da DevStudio.</p>
                    <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="ui-button"
                       style="background: #25D366; color: #FFFFFF; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <span>Conversar no WhatsApp</span>
                    </a>
                </div>
            @endif

            <div class="ui-card">
                <h2 style="font-size: 1.25rem; margin-top: 0; margin-bottom: 1rem;">Canais de Atendimento</h2>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.95rem;">
                    @if ($settings?->email)
                        <li>
                            <strong>E-mail:</strong><br>
                            <a href="mailto:{{ $settings->email }}" style="color: var(--theme-link);">{{ $settings->email }}</a>
                        </li>
                    @endif
                    @if ($settings?->phone)
                        <li>
                            <strong>Telefone:</strong><br>
                            <a href="tel:{{ preg_replace('/\D/', '', $settings->phone) }}" style="color: var(--theme-link);">{{ $settings->phone }}</a>
                        </li>
                    @endif
                    @if ($settings?->address)
                        <li>
                            <strong>Localização:</strong><br>
                            <span>{{ $settings->address }}</span>
                        </li>
                    @endif
                    <li>
                        <strong>Horário de atendimento:</strong><br>
                        <span>Segunda a Sexta, das 09h às 18h</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</main>
@endsection
