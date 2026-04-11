<x-layouts.app title="Crear cuenta">
    <div class="max-w-sm mx-auto py-6 sm:py-10">
        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-6">
            Roomie · Crear cuenta
        </p>

        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-3">
            Empieza en <span class="text-copper">30 segundos</span>.
        </h1>
        <p class="text-navy/60 leading-relaxed mb-9">
            Necesitas una cuenta para lanzar el pipeline. Solo email y contraseña — tu API key se pide cuando creas la primera campaña.
        </p>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium mb-2">Nombre</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    required
                    autofocus
                    autocomplete="name"
                    autocapitalize="words"
                    value="{{ old('name') }}"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    placeholder="Cómo quieres que te llamemos"
                >
                @error('name')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium mb-2">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    autocomplete="email"
                    autocapitalize="none"
                    autocorrect="off"
                    inputmode="email"
                    value="{{ old('email') }}"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    placeholder="tu@email.com"
                >
                @error('email')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-2">Contraseña</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    autocomplete="new-password"
                    minlength="8"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    placeholder="Mínimo 8 caracteres"
                >
                @error('password')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-2">Confirmar contraseña</label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    minlength="8"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    placeholder="Repite la contraseña"
                >
            </div>

            <button type="submit" class="w-full bg-navy text-cream px-6 py-3.5 rounded-full font-medium hover:bg-navy-light transition inline-flex items-center justify-center gap-2">
                Crear cuenta
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </button>
        </form>

        <p class="text-sm text-navy/55 mt-8 text-center">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-navy font-medium underline underline-offset-4 decoration-navy/30 hover:decoration-navy py-2 -my-2 inline-block">
                Entra
            </a>
        </p>
    </div>
</x-layouts.app>
