<x-layouts.dashboard title="Banco de imágenes" active="image-bank">
    <header class="flex items-end justify-between pb-7 sm:pb-8 mb-9 sm:mb-10 border-b border-navy/10 gap-4 flex-wrap">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-navy/40 mb-2">Ajustes</p>
            <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight">Banco de imágenes</h1>
            <p class="text-sm text-navy/55 mt-2">
                Sube imágenes que la IA puede usar al generar emails.
                <span class="font-mono text-navy/40">{{ $images->total() }} / {{ config('services.roomie.bank_images_max_per_user', 100) }}</span>
            </p>
        </div>
    </header>

    {{-- Upload form --}}
    <section class="mb-10 sm:mb-12 rounded-2xl border border-navy/15 bg-white p-5 sm:p-6">
        <p class="font-mono text-[10px] text-navy/40 uppercase tracking-[0.18em] mb-4">Subir imágenes</p>

        <form method="POST" action="{{ route('settings.image-bank.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="title" class="block text-xs text-navy/55 mb-1">Título</label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title') }}"
                           placeholder="Terraza con vistas al mar"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                           maxlength="120">
                    @error('title')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="alt_text" class="block text-xs text-navy/55 mb-1">Texto alternativo</label>
                    <input type="text" name="alt_text" id="alt_text"
                           value="{{ old('alt_text') }}"
                           placeholder="Describe la imagen para accesibilidad"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                           maxlength="255">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="category" class="block text-xs text-navy/55 mb-1">Categoría</label>
                    <input type="text" name="category" id="category"
                           value="{{ old('category') }}"
                           placeholder="habitación, restaurante, exterior, spa..."
                           list="category-suggestions"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                           maxlength="60">
                    @if ($categories->isNotEmpty())
                        <datalist id="category-suggestions">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    @endif
                </div>
                <div>
                    <label for="tags" class="block text-xs text-navy/55 mb-1">Etiquetas</label>
                    <input type="text" name="tags" id="tags"
                           value="{{ old('tags') }}"
                           placeholder="verano, piscina, terraza (separadas por coma)"
                           class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition placeholder:text-navy/30"
                           maxlength="255">
                </div>
            </div>

            <div class="mb-4">
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" required
                       class="block w-full text-sm text-navy/55 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-navy/5 file:text-navy hover:file:bg-navy/10 file:transition file:cursor-pointer">
                <p class="mt-1 text-xs text-navy/40">JPG, PNG o WebP. Máx 5 MB por imagen, hasta 5 a la vez.</p>
                @error('images')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="bg-navy text-cream px-5 py-2.5 rounded-full text-sm font-medium hover:bg-navy-light transition">
                Subir
            </button>
        </form>
    </section>

    {{-- Category filter --}}
    @if ($categories->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('settings.image-bank.index') }}"
               class="text-xs px-3 py-1.5 rounded-full border transition
                   {{ ! request('category') ? 'border-navy bg-navy text-cream' : 'border-navy/20 text-navy/55 hover:border-navy/40' }}">
                Todas
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('settings.image-bank.index', ['category' => $cat]) }}"
                   class="text-xs px-3 py-1.5 rounded-full border transition
                       {{ request('category') === $cat ? 'border-navy bg-navy text-cream' : 'border-navy/20 text-navy/55 hover:border-navy/40' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Image grid --}}
    @if ($images->isEmpty())
        <div class="py-16 text-center border border-dashed border-navy/15 rounded-2xl">
            <svg class="w-6 h-6 text-navy/15 mx-auto mb-4" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            <p class="text-navy/55 text-sm">No hay imágenes en el banco todavía.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
            @foreach ($images as $image)
                <div class="group relative rounded-xl border border-navy/15 bg-white overflow-hidden">
                    <div class="aspect-square bg-navy/5">
                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $image->title }}"
                             class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="p-3">
                        <p class="text-xs font-medium text-navy truncate">{{ $image->title }}</p>
                        @if ($image->category)
                            <p class="text-[10px] font-mono text-navy/40 mt-0.5">{{ $image->category }}</p>
                        @endif
                        @if ($image->width && $image->height)
                            <p class="text-[10px] font-mono text-navy/30 mt-0.5">{{ $image->width }}&times;{{ $image->height }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('settings.image-bank.destroy', $image) }}"
                          class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('¿Eliminar esta imagen?')"
                                class="w-7 h-7 rounded-full bg-white/90 border border-navy/20 flex items-center justify-center text-navy/50 hover:text-red-700 hover:border-red-300 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        @if ($images->hasPages())
            <div class="mt-6 text-xs text-navy/45 font-mono">
                {{ $images->withQueryString()->links() }}
            </div>
        @endif
    @endif
</x-layouts.dashboard>
