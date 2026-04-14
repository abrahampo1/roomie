<x-layouts.editor title="Editor — {{ $campaign->name ?? 'Campaña #'.str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}">

@php
    $hasApiKey = $campaign->getRawOriginal('api_key') !== null;
    $hotelName = $campaign->strategy['recommended_hotel']['name'] ?? 'Hotel';
@endphp

<div
    x-data="emailEditor({
        blocks: @js($blocks),
        subjectLine: @js($creative['subject_line'] ?? ''),
        previewText: @js($creative['preview_text'] ?? ''),
        headline: @js($creative['headline'] ?? ''),
        ctaText: @js($creative['cta_text'] ?? 'Reservar ahora'),
        campaignId: @js($campaign->id),
        bankImages: @js($bankImages),
        hotelName: @js($hotelName),
    })"
    class="flex flex-col h-screen"
>
    {{-- ═══ Top bar ═══ --}}
    <header class="flex items-center justify-between gap-4 px-4 lg:px-6 py-3 bg-navy text-cream border-b border-cream/10 shrink-0 z-50">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('campaigns.show', $campaign) }}" class="text-cream/50 hover:text-cream transition text-sm flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Volver
            </a>
            <div class="h-4 w-px bg-cream/15 shrink-0"></div>
            <p class="text-sm text-cream/70 truncate">
                <span class="font-mono text-[11px] text-cream/40 mr-1.5">#{{ str_pad($campaign->id, 3, '0', STR_PAD_LEFT) }}</span>
                {{ $campaign->name ?? $campaign->strategy['campaign_name'] ?? 'Sin título' }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button
                @click="showPreview = !showPreview"
                :class="showPreview ? 'bg-copper text-navy' : 'bg-cream/10 text-cream/70 hover:bg-cream/15'"
                class="px-3.5 py-1.5 rounded-full text-xs font-medium transition"
            >
                <span x-text="showPreview ? 'Editor' : 'Vista previa'">Vista previa</span>
            </button>

            <button
                @click="save()"
                :disabled="saving"
                class="inline-flex items-center gap-1.5 bg-copper text-navy px-4 py-1.5 rounded-full text-xs font-semibold hover:bg-copper-dark transition disabled:opacity-50"
            >
                <template x-if="saving">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </template>
                <template x-if="!saving && !saved">
                    <span>Guardar</span>
                </template>
                <template x-if="saved">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Guardado
                    </span>
                </template>
            </button>
        </div>
    </header>

    {{-- ═══ Main layout ═══ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ─── Left sidebar: Block palette ─── --}}
        <aside class="hidden lg:flex flex-col w-56 bg-white border-r border-navy/10 shrink-0 overflow-y-auto">
            <div class="px-4 pt-5 pb-3">
                <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em]">Bloques</p>
            </div>

            <div class="flex-1 px-3 pb-4 space-y-1">
                <template x-for="ptype in paletteTypes" :key="ptype.type">
                    <button
                        @click="addBlock(ptype.type)"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left hover:bg-sand-light/60 active:bg-sand-light transition group"
                    >
                        <span
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-navy/[0.06] text-navy/50 font-mono text-xs shrink-0 group-hover:bg-copper/15 group-hover:text-copper transition"
                            x-text="ptype.icon"
                        ></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-medium leading-tight" x-text="ptype.label"></span>
                            <span class="block text-[11px] text-navy/40 leading-snug" x-text="ptype.desc"></span>
                        </span>
                    </button>
                </template>
            </div>
        </aside>

        {{-- ─── Center: Canvas or Preview ─── --}}
        <main class="flex-1 overflow-y-auto bg-sand-light/40">

            {{-- Preview mode --}}
            <div x-show="showPreview" x-cloak class="p-4 lg:p-8">
                <div class="max-w-[680px] mx-auto bg-white rounded-2xl shadow-sm border border-navy/10 overflow-hidden">
                    <div class="px-5 sm:px-7 py-4 border-b border-navy/10">
                        <p class="text-xs text-navy/45 mb-1">Asunto</p>
                        <p class="font-[Fredoka] font-semibold text-base leading-tight" x-text="subjectLine"></p>
                        <p class="text-sm text-navy/55 mt-1.5" x-text="previewText"></p>
                    </div>
                    <iframe
                        class="w-full border-0"
                        style="min-height: 600px;"
                        :srcdoc="buildPreviewHtml()"
                    ></iframe>
                </div>
            </div>

            {{-- Editor mode --}}
            <div x-show="!showPreview" class="p-4 lg:p-8">
                <div class="max-w-2xl mx-auto">

                    {{-- Email meta fields --}}
                    <div class="bg-white rounded-2xl border border-navy/10 p-5 mb-6 space-y-4">
                        <div>
                            <label class="block text-xs text-navy/45 mb-1.5">Asunto</label>
                            <input
                                type="text"
                                x-model="subjectLine"
                                class="w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/50 focus:ring-1 focus:ring-navy/15 transition"
                                placeholder="Asunto del email..."
                            >
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-navy/45 mb-1.5">Preview text</label>
                                <input
                                    type="text"
                                    x-model="previewText"
                                    class="w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/50 focus:ring-1 focus:ring-navy/15 transition"
                                    placeholder="Texto de vista previa..."
                                >
                            </div>
                            <div>
                                <label class="block text-xs text-navy/45 mb-1.5">Headline</label>
                                <input
                                    type="text"
                                    x-model="headline"
                                    class="w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/50 focus:ring-1 focus:ring-navy/15 transition"
                                    placeholder="Titular del hero..."
                                >
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-navy/45 mb-1.5">Texto del CTA</label>
                            <input
                                type="text"
                                x-model="ctaText"
                                class="w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/50 focus:ring-1 focus:ring-navy/15 transition sm:max-w-xs"
                                placeholder="Reservar ahora"
                            >
                        </div>
                    </div>

                    {{-- Blocks canvas --}}
                    <div x-ref="canvas" class="space-y-3">
                        <template x-for="(block, index) in blocks" :key="block.id">
                            <div
                                @click="selectBlock(block.id)"
                                :class="selectedBlockId === block.id ? 'ring-2 ring-copper border-copper/30' : 'border-navy/10 hover:border-navy/20'"
                                class="group bg-white rounded-xl border transition-all relative"
                            >
                                {{-- Block toolbar --}}
                                <div class="flex items-center justify-between px-4 py-2 border-b border-navy/5">
                                    <div class="flex items-center gap-2">
                                        <span
                                            data-drag-handle
                                            class="cursor-grab active:cursor-grabbing text-navy/25 hover:text-navy/50 transition hidden lg:block"
                                            title="Arrastrar para mover"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                                        </span>
                                        <span class="font-mono text-[10px] text-navy/35 uppercase tracking-wider" x-text="blockTypeName(block.type)"></span>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{-- Mobile move buttons --}}
                                        <button @click.stop="moveBlock(block.id, -1)" class="lg:hidden p-1 text-navy/30 hover:text-navy/60 transition" title="Subir">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                                        </button>
                                        <button @click.stop="moveBlock(block.id, 1)" class="lg:hidden p-1 text-navy/30 hover:text-navy/60 transition" title="Bajar">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                        </button>
                                        <button @click.stop="duplicateBlock(block.id)" class="p-1 text-navy/30 hover:text-navy/60 transition" title="Duplicar">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75"/></svg>
                                        </button>
                                        <button @click.stop="removeBlock(block.id)" class="p-1 text-navy/30 hover:text-red-500 transition" title="Eliminar">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Block content --}}
                                <div class="px-5 py-4">
                                    {{-- text-lead --}}
                                    <template x-if="block.type === 'text-lead'">
                                        <textarea
                                            x-model="block.content"
                                            rows="3"
                                            class="w-full resize-y border-0 bg-transparent p-0 text-xl leading-relaxed text-navy focus:ring-0 focus:outline-none placeholder:text-navy/25"
                                            style="font-family: Georgia, 'Times New Roman', serif;"
                                            placeholder="Párrafo de apertura destacado..."
                                        ></textarea>
                                    </template>

                                    {{-- text-body --}}
                                    <template x-if="block.type === 'text-body'">
                                        <textarea
                                            x-model="block.content"
                                            rows="3"
                                            class="w-full resize-y border-0 bg-transparent p-0 text-[17px] leading-[1.7] text-navy/70 focus:ring-0 focus:outline-none placeholder:text-navy/25"
                                            style="font-family: Georgia, 'Times New Roman', serif;"
                                            placeholder="Contenido del email..."
                                        ></textarea>
                                    </template>

                                    {{-- pull-quote --}}
                                    <template x-if="block.type === 'pull-quote'">
                                        <div class="border-l-2 border-copper pl-5">
                                            <textarea
                                                x-model="block.content"
                                                rows="2"
                                                class="w-full resize-y border-0 bg-transparent p-0 text-[22px] italic leading-snug text-navy focus:ring-0 focus:outline-none placeholder:text-navy/25"
                                                style="font-family: Georgia, 'Times New Roman', serif;"
                                                placeholder="Una frase memorable..."
                                            ></textarea>
                                        </div>
                                    </template>

                                    {{-- highlight-list --}}
                                    <template x-if="block.type === 'highlight-list'">
                                        <div class="space-y-2">
                                            <template x-for="(item, itemIdx) in block.items" :key="itemIdx">
                                                <div class="flex items-start gap-2">
                                                    <span class="text-copper mt-1 shrink-0 font-mono">&mdash;</span>
                                                    <input
                                                        type="text"
                                                        :value="item"
                                                        @input="updateListItem(block.id, itemIdx, $event.target.value)"
                                                        class="flex-1 border-0 bg-transparent p-0 text-[17px] leading-relaxed text-navy/70 focus:ring-0 focus:outline-none placeholder:text-navy/25"
                                                        style="font-family: Georgia, serif;"
                                                        placeholder="Highlight..."
                                                    >
                                                    <button
                                                        @click.stop="removeListItem(block.id, itemIdx)"
                                                        x-show="block.items.length > 1"
                                                        class="text-navy/20 hover:text-red-400 transition mt-1 shrink-0"
                                                    >
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <button
                                                @click.stop="addListItem(block.id)"
                                                x-show="(block.items || []).length < 5"
                                                class="text-xs text-copper hover:text-copper-dark transition pl-6"
                                            >+ Nuevo item</button>
                                        </div>
                                    </template>

                                    {{-- caption --}}
                                    <template x-if="block.type === 'caption'">
                                        <input
                                            type="text"
                                            x-model="block.content"
                                            class="w-full border-0 bg-transparent p-0 text-[11px] uppercase tracking-[2px] text-navy/40 focus:ring-0 focus:outline-none placeholder:text-navy/20"
                                            style="font-family: 'Courier New', Courier, monospace;"
                                            placeholder="SECCIÓN"
                                        >
                                    </template>

                                    {{-- image --}}
                                    <template x-if="block.type === 'image'">
                                        <div>
                                            <template x-if="block.image_id > 0">
                                                <div>
                                                    <img
                                                        :src="(bankImages.find(i => i.id === block.image_id) || {}).url || ''"
                                                        :alt="block.alt || ''"
                                                        class="rounded-lg max-h-48 object-cover mb-3"
                                                    >
                                                    <input
                                                        type="text"
                                                        x-model="block.alt"
                                                        class="w-full border border-navy/10 rounded-lg bg-white px-3 py-1.5 text-sm text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/40 transition"
                                                        placeholder="Texto alternativo..."
                                                    >
                                                </div>
                                            </template>
                                            <template x-if="!block.image_id || block.image_id === 0">
                                                <div class="space-y-2">
                                                    <p class="text-xs text-navy/40 mb-2">Selecciona una imagen del banco:</p>
                                                    <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto">
                                                        <template x-for="img in bankImages" :key="img.id">
                                                            <button
                                                                @click.stop="block.image_id = img.id; block.alt = img.alt || img.title || ''"
                                                                class="aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-copper transition"
                                                            >
                                                                <img :src="img.url" :alt="img.title || ''" class="w-full h-full object-cover">
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <template x-if="bankImages.length === 0">
                                                        <p class="text-xs text-navy/35 italic">No hay imágenes en el banco.</p>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- signoff --}}
                                    <template x-if="block.type === 'signoff'">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-navy/30 italic" style="font-family: Georgia, serif;">&mdash;</span>
                                            <input
                                                type="text"
                                                x-model="block.content"
                                                class="flex-1 border-0 bg-transparent p-0 text-[15px] italic text-navy/40 focus:ring-0 focus:outline-none placeholder:text-navy/20"
                                                style="font-family: Georgia, 'Times New Roman', serif;"
                                                placeholder="El equipo del hotel..."
                                            >
                                        </div>
                                    </template>

                                    {{-- divider --}}
                                    <template x-if="block.type === 'divider'">
                                        <p class="text-center text-sand text-sm" style="font-family: Georgia, serif;">&mdash; &#10022; &mdash;</p>
                                    </template>

                                    {{-- spacer --}}
                                    <template x-if="block.type === 'spacer'">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-navy/35">Altura:</span>
                                            <select
                                                x-model.number="block.height"
                                                class="rounded-lg border border-navy/15 bg-white px-2 py-1 text-sm text-navy focus:outline-none focus:border-navy/40 transition"
                                            >
                                                <option value="8">8px</option>
                                                <option value="16">16px</option>
                                                <option value="24">24px</option>
                                                <option value="32">32px</option>
                                                <option value="48">48px</option>
                                                <option value="64">64px</option>
                                            </select>
                                        </div>
                                    </template>
                                </div>

                                {{-- Refining indicator --}}
                                <div
                                    x-show="refiningBlockId === block.id"
                                    x-cloak
                                    class="absolute inset-0 bg-white/80 rounded-xl flex items-center justify-center z-10"
                                >
                                    <div class="flex items-center gap-2 text-sm text-copper">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" style="animation: spin 3s linear infinite;"><use href="#roomie-sparkle"/></svg>
                                        IA trabajando...
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Empty state --}}
                    <div x-show="blocks.length === 0" class="text-center py-16">
                        <svg class="w-7 h-7 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        <p class="text-navy/40 text-sm">Arrastra bloques desde el panel izquierdo para empezar.</p>
                    </div>

                    {{-- Mobile: add block button --}}
                    <div class="lg:hidden mt-4">
                        <details class="group">
                            <summary class="cursor-pointer text-sm font-medium text-copper flex items-center gap-1.5 select-none list-none">
                                <span class="inline-block transition-transform group-open:rotate-90">+</span>
                                Agregar bloque
                            </summary>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <template x-for="ptype in paletteTypes" :key="ptype.type">
                                    <button
                                        @click="addBlock(ptype.type)"
                                        class="flex flex-col items-center gap-1 p-3 rounded-xl bg-white border border-navy/10 text-center hover:border-copper/30 transition"
                                    >
                                        <span class="text-lg" x-text="ptype.icon"></span>
                                        <span class="text-[10px] text-navy/55" x-text="ptype.label"></span>
                                    </button>
                                </template>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </main>

        {{-- ─── Right sidebar: AI assistant ─── --}}
        <aside class="hidden lg:flex flex-col w-72 xl:w-80 bg-white border-l border-navy/10 shrink-0 overflow-y-auto">

            {{-- Selected block info --}}
            <div x-show="selectedBlock" x-cloak class="px-5 pt-5 pb-4 border-b border-navy/10">
                <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-3">Bloque seleccionado</p>
                <p class="text-sm font-medium" x-text="selectedBlock ? blockTypeName(selectedBlock.type) : ''"></p>
            </div>

            {{-- AI: Per-block refinement --}}
            @if ($hasApiKey)
                <div x-show="selectedBlock" x-cloak class="px-5 py-5 border-b border-navy/10">
                    <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-3">
                        <svg class="w-2.5 h-2.5 text-copper inline-block mr-1" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        IA por bloque
                    </p>

                    {{-- Quick actions --}}
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        <template x-for="action in (selectedBlock ? quickActions(selectedBlock.type) : [])" :key="action.label">
                            <button
                                @click="refineSelectedBlock(action.prompt)"
                                :disabled="refining"
                                class="px-2.5 py-1 rounded-full text-[11px] border border-navy/12 text-navy/55 hover:bg-navy/[0.03] hover:border-navy/20 transition disabled:opacity-40"
                                x-text="action.label"
                            ></button>
                        </template>
                    </div>

                    {{-- Free-form prompt --}}
                    <div class="space-y-2">
                        <textarea
                            x-model="aiPrompt"
                            rows="3"
                            class="w-full rounded-xl border border-navy/15 bg-white px-3 py-2.5 text-sm text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/50 focus:ring-1 focus:ring-navy/15 transition resize-y"
                            placeholder="Dile a la IA cómo cambiar este bloque..."
                        ></textarea>
                        <button
                            @click="refineSelectedBlock(aiPrompt)"
                            :disabled="refining || !aiPrompt.trim()"
                            class="inline-flex items-center gap-1.5 bg-navy text-cream px-4 py-2 rounded-full text-xs font-medium hover:bg-navy-light transition disabled:opacity-40 w-full justify-center"
                        >
                            <svg class="w-2.5 h-2.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                            <span x-text="refining ? 'Refinando...' : 'Aplicar al bloque'"></span>
                        </button>
                    </div>
                </div>

                {{-- AI: Full email --}}
                <div class="px-5 py-5">
                    <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-3">
                        <svg class="w-2.5 h-2.5 text-copper inline-block mr-1" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                        IA global
                    </p>
                    <p class="text-xs text-navy/45 mb-3 leading-relaxed">Regenera todo el email con instrucciones.</p>

                    <div class="flex flex-wrap gap-1.5 mb-4">
                        <button @click="regenerateAll('Acorta todos los párrafos un 30%.')" :disabled="refining" class="px-2.5 py-1 rounded-full text-[11px] border border-navy/12 text-navy/55 hover:bg-navy/[0.03] transition disabled:opacity-40">Acortar todo</button>
                        <button @click="regenerateAll('Suaviza el tono: menos urgencia, más editorial.')" :disabled="refining" class="px-2.5 py-1 rounded-full text-[11px] border border-navy/12 text-navy/55 hover:bg-navy/[0.03] transition disabled:opacity-40">Suavizar tono</button>
                        <button @click="regenerateAll('Añade más urgencia y persuasión al email.')" :disabled="refining" class="px-2.5 py-1 rounded-full text-[11px] border border-navy/12 text-navy/55 hover:bg-navy/[0.03] transition disabled:opacity-40">Más urgencia</button>
                        <button @click="regenerateAll('Haz el asunto más corto y directo.')" :disabled="refining" class="px-2.5 py-1 rounded-full text-[11px] border border-navy/12 text-navy/55 hover:bg-navy/[0.03] transition disabled:opacity-40">Asunto corto</button>
                    </div>

                    <div class="space-y-2">
                        <textarea
                            x-model="globalAiPrompt"
                            rows="3"
                            class="w-full rounded-xl border border-navy/15 bg-white px-3 py-2.5 text-sm text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/50 focus:ring-1 focus:ring-navy/15 transition resize-y"
                            placeholder="Instrucciones para regenerar todo el email..."
                        ></textarea>
                        <button
                            @click="regenerateAll(globalAiPrompt)"
                            :disabled="refining || !globalAiPrompt.trim()"
                            class="inline-flex items-center gap-1.5 bg-navy text-cream px-4 py-2 rounded-full text-xs font-medium hover:bg-navy-light transition disabled:opacity-40 w-full justify-center"
                        >
                            <svg class="w-2.5 h-2.5 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
                            <span x-text="refining ? 'Regenerando...' : 'Regenerar email'"></span>
                        </button>
                    </div>
                </div>
            @else
                <div class="px-5 py-5">
                    <p class="text-xs text-navy/40 italic leading-relaxed">La clave API de esta campaña se ha borrado. La edición con IA no está disponible, pero puedes editar manualmente.</p>
                </div>
            @endif

            {{-- Refining overlay for entire sidebar --}}
            <div
                x-show="refining && !refiningBlockId"
                x-cloak
                class="px-5 py-4 border-t border-navy/10 bg-copper/5"
            >
                <div class="flex items-center gap-2 text-sm text-copper">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" style="animation: spin 3s linear infinite;"><use href="#roomie-sparkle"/></svg>
                    La IA está trabajando...
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

</x-layouts.editor>
