<x-layouts.dashboard title="Campaña" active="campaigns">
    <a href="{{ route('campaigns.index') }}" class="text-xs text-navy/45 hover:text-navy transition py-2 -my-2 inline-block">
        ← Campañas
    </a>

    <livewire:campaign-show :campaign="$campaign" />
</x-layouts.dashboard>
