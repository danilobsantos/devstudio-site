@props(['label'])

<nav class="site-navigation" aria-label="{{ $label }}">
    <ul>
        <li><a href="{{ route('services.index') }}" @if (request()->routeIs('services.index')) aria-current="page" @endif>Soluções</a></li>
        <li><a href="{{ route('products.index') }}" @if (request()->routeIs('products.index')) aria-current="page" @endif>Produtos</a></li>
        <li><a href="{{ route('projects.index') }}" @if (request()->routeIs('projects.index')) aria-current="page" @endif>Projetos</a></li>
        <li><a href="{{ route('pages.about') }}" @if (request()->routeIs('pages.about')) aria-current="page" @endif>Sobre</a></li>
        <li><a href="{{ route('posts.index') }}" @if (request()->routeIs('posts.index')) aria-current="page" @endif>Insights</a></li>
        <li><a href="{{ route('contact.show') }}" @if (request()->routeIs('contact.show')) aria-current="page" @endif>Contato</a></li>
    </ul>
</nav>
