@extends('layouts.public')

@section('content')
<main id="main-content">
    <div class="listing-hero">
        <x-breadcrumbs :items="[['label' => 'Início', 'url' => url('/')], ['label' => $heading, 'current' => true]]" />
        <h1 class="listing-title">{{ $heading }}</h1>
        @php
            $subtitles = [
                'Soluções' => 'Engenharia de software especializada, sistemas inteligentes e automação sob medida para transformar sua empresa.',
                'Produtos' => 'Plataformas SaaS e ferramentas corporativas prontas para acelerar a gestão e os resultados operacionais do seu negócio.',
                'Insights' => 'Artigos técnicos, tendências de mercado, metodologias ágeis e arquitetura de software compartilhados por nossos engenheiros.',
                'Projetos' => 'Portfólio de cases e entregas de software de alto impacto construídas pela DevStudio.',
            ];
        @endphp
        @if (isset($subtitles[$heading]))
            <p class="listing-lead">{{ $subtitles[$heading] }}</p>
        @endif
    </div>

    @forelse ($items as $item)
        @if ($loop->first)<div class="public-listing-grid">@endif
        <x-card class="listing-card">
            @php($coverImage = $item->cover_path ?: $item->cover_image)
            @if ($coverImage)
                <div class="listing-card-media">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($coverImage) }}" alt="" width="1200" height="750" loading="lazy" decoding="async">
                </div>
            @endif
            <h2><a href="{{ route($detailRoute, ['slug' => $item->slug]) }}">{{ $item->{$titleField} }}</a></h2>
            @if ($item->{$summaryField})<p class="listing-card-desc">{{ $item->{$summaryField} }}</p>@endif
            <div class="listing-card-action">
                <span class="listing-action-text">Ver detalhes</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </x-card>
        @if ($loop->last)</div>@endif
    @empty
        <div class="listing-empty-box">
            <p>Nenhum conteúdo disponível no momento.</p>
        </div>
    @endforelse

    <div class="listing-pagination">
        {{ $items->links() }}
    </div>
</main>
@endsection
