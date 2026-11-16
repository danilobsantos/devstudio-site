@props(['items'])

<nav class="ui-breadcrumbs" aria-label="Trilha de navegação">
    <ol>
        @foreach ($items as $item)
            <li>
                @if (! empty($item['url']) && empty($item['current']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span @if (! empty($item['current'])) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
