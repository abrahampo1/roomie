@props(['value', 'label', 'suffix' => '', 'color' => 'text-navy'])

<div>
    <dd class="font-[Fredoka] font-semibold text-2xl sm:text-3xl leading-none {{ $color }} tabular-nums">
        {{ $value }}<span class="text-sm text-navy/35">{{ $suffix }}</span>
    </dd>
    <dt class="text-xs text-navy/45 mt-1.5">{{ $label }}</dt>
</div>
