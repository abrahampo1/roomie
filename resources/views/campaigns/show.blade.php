<x-layouts.app title="Campaña">
    <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
        ← Campañas
    </a>

    <livewire:campaign-show :campaign="$campaign" />
</x-layouts.app>
