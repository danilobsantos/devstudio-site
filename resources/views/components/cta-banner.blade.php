@props(['title', 'text' => null, 'actionUrl', 'actionLabel'])

<div {{ $attributes->class(['ui-cta-banner']) }}>
    <div class="ui-cta-banner__inner">
        <span class="ui-cta-banner__kicker">Próximo Passo</span>
        <h2>{{ $title }}</h2>
        @if ($text)<p class="ui-cta-banner__text">{{ $text }}</p>@endif
        <div class="ui-cta-banner__actions">
            <x-button :href="$actionUrl" class="ui-cta-banner__btn">{{ $actionLabel }}</x-button>
        </div>
    </div>
</div>
