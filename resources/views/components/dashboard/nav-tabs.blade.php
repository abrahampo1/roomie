@props(['active' => 'index'])

@php
    $tabs = [
        'index' => ['route' => 'dashboard.index', 'label' => 'Panel'],
        'analytics' => ['route' => 'dashboard.analytics', 'label' => 'Analíticas'],
        'send-history' => ['route' => 'dashboard.send-history', 'label' => 'Historial'],
        'email-previews' => ['route' => 'dashboard.email-previews', 'label' => 'Emails'],
    ];
@endphp

<nav class="flex gap-1 border-b border-navy/10 mb-8 sm:mb-10 -mx-1 overflow-x-auto">
    @foreach ($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}"
           class="px-3 py-2.5 text-sm whitespace-nowrap transition border-b-2 -mb-px
               {{ $active === $key
                   ? 'border-copper text-navy font-medium'
                   : 'border-transparent text-navy/55 hover:text-navy hover:border-navy/20' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
