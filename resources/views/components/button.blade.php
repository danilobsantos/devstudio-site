@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'disabled' => false])

@php
    if (! in_array($variant, ['primary', 'secondary', 'link'], true)) {
        throw new InvalidArgumentException('Unsupported button variant.');
    }
@endphp

@if ($href !== null)
    <a {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }} href="{{ $href }}">{{ $slot }}</a>
@else
    <button {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }} type="{{ $type }}" @disabled($disabled)>{{ $slot }}</button>
@endif
