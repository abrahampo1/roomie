<x-layouts.app title="Marca">
    <header class="flex items-end justify-between pb-7 sm:pb-8 mb-9 sm:mb-10 border-b border-navy/15 gap-4 flex-wrap">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-2">Ajustes</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Identidad de marca</h1>
            <p class="text-sm text-navy/55 mt-2">Define tu marca para que la IA genere campañas alineadas con tu identidad.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('settings.brand.update') }}" enctype="multipart/form-data" class="space-y-10">
        @csrf

        {{-- Brand name --}}
        <div>
            <label for="brand_name" class="block text-sm font-medium mb-2">Nombre de la marca</label>
            <input type="text" name="brand_name" id="brand_name"
                   value="{{ old('brand_name', $brand->brand_name) }}"
                   placeholder="Eurostars Hotel Company"
                   class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                   autocapitalize="none" autocorrect="off" maxlength="120">
            @error('brand_name')
                <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- Logo --}}
        <div>
            <label class="block text-sm font-medium mb-2">Logo</label>
            @if ($brand->logo_path)
                <div class="flex items-center gap-4 mb-3">
                    <img src="{{ $brand->logoUrl() }}" alt="Logo actual" class="h-12 w-auto rounded-lg border border-navy/10">
                    <label class="flex items-center gap-2 text-xs text-navy/55">
                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-navy/30">
                        Eliminar logo actual
                    </label>
                </div>
            @endif
            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-navy/55 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-navy/5 file:text-navy hover:file:bg-navy/10 file:transition file:cursor-pointer">
            <p class="mt-1 text-xs text-navy/40">JPG, PNG o WebP. Máximo 2 MB.</p>
            @error('logo')
                <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- Colors --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="primary_color" class="block text-sm font-medium mb-2">Color primario</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="primary_color" id="primary_color"
                           value="{{ old('primary_color', $brand->primary_color ?? '#1a1a2e') }}"
                           class="w-10 h-10 rounded-lg border border-navy/20 cursor-pointer p-0.5">
                    <input type="text" readonly id="primary_color_hex"
                           value="{{ old('primary_color', $brand->primary_color ?? '#1a1a2e') }}"
                           class="font-mono text-sm text-navy/60 bg-transparent border-0 p-0 w-20">
                </div>
                @error('primary_color')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="secondary_color" class="block text-sm font-medium mb-2">Color secundario</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="secondary_color" id="secondary_color"
                           value="{{ old('secondary_color', $brand->secondary_color ?? '#c8956c') }}"
                           class="w-10 h-10 rounded-lg border border-navy/20 cursor-pointer p-0.5">
                    <input type="text" readonly id="secondary_color_hex"
                           value="{{ old('secondary_color', $brand->secondary_color ?? '#c8956c') }}"
                           class="font-mono text-sm text-navy/60 bg-transparent border-0 p-0 w-20">
                </div>
                @error('secondary_color')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Voice / Tone --}}
        <div>
            <label for="voice_description" class="block text-sm font-medium mb-2">Voz de marca</label>
            <textarea name="voice_description" id="voice_description" rows="4"
                      placeholder="Describe el tono y personalidad de tu marca. Ej: Elegante y cercano, con un toque mediterráneo. Evita lo formal corporativo."
                      class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30 resize-y"
                      maxlength="2000">{{ old('voice_description', $brand->voice_description) }}</textarea>
            <p class="mt-1 text-xs text-navy/40">La IA usará esta descripción para adaptar el tono del copy. Máx 2000 caracteres.</p>
            @error('voice_description')
                <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- Contact info --}}
        <div>
            <p class="text-sm font-medium mb-4">Información de contacto</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="contact_email" class="block text-xs text-navy/55 mb-1">Email</label>
                    <input type="email" name="contact_email" id="contact_email"
                           value="{{ old('contact_email', $brand->contact_email) }}"
                           placeholder="info@hotel.com"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                           autocapitalize="none" autocorrect="off">
                    @error('contact_email')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="contact_phone" class="block text-xs text-navy/55 mb-1">Teléfono</label>
                    <input type="text" name="contact_phone" id="contact_phone"
                           value="{{ old('contact_phone', $brand->contact_phone) }}"
                           placeholder="+34 900 000 000"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30">
                    @error('contact_phone')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="contact_website" class="block text-xs text-navy/55 mb-1">Web</label>
                    <input type="url" name="contact_website" id="contact_website"
                           value="{{ old('contact_website', $brand->contact_website) }}"
                           placeholder="https://www.hotel.com"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                           autocapitalize="none" autocorrect="off" spellcheck="false">
                    @error('contact_website')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Social links --}}
        <div>
            <p class="text-sm font-medium mb-4">Redes sociales</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $socials = ['instagram' => 'Instagram', 'twitter' => 'X (Twitter)', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn'];
                    $currentLinks = old('social_links', $brand->social_links ?? []);
                @endphp
                @foreach ($socials as $key => $label)
                    <div>
                        <label for="social_{{ $key }}" class="block text-xs text-navy/55 mb-1">{{ $label }}</label>
                        <input type="url" name="social_links[{{ $key }}]" id="social_{{ $key }}"
                               value="{{ $currentLinks[$key] ?? '' }}"
                               placeholder="https://{{ $key }}.com/..."
                               class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                               autocapitalize="none" autocorrect="off" spellcheck="false">
                    </div>
                @endforeach
            </div>
            @error('social_links.*')
                <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-4 pt-4 border-t border-navy/10">
            <button type="submit"
                    class="bg-navy text-cream px-6 py-3 rounded-full text-sm font-medium hover:bg-navy-light transition">
                Guardar marca
            </button>
            <a href="{{ route('dashboard.index') }}" class="text-sm text-navy/55 hover:text-navy transition">Cancelar</a>
        </div>
    </form>

    @push('scripts')
    <script>
        document.querySelectorAll('input[type=color]').forEach(picker => {
            const hexDisplay = document.getElementById(picker.id + '_hex');
            if (hexDisplay) {
                picker.addEventListener('input', () => hexDisplay.value = picker.value);
            }
        });
    </script>
    @endpush
</x-layouts.app>
