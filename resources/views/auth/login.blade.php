<x-layouts.app title="Entrar">
    <div class="max-w-sm mx-auto py-6 sm:py-10">
        <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-navy/45 mb-6">
            Roomie · Iniciar sesión
        </p>

        <h1 class="font-[Fredoka] font-semibold text-3xl sm:text-4xl tracking-tight leading-[1.05] mb-3">
            Bienvenido <span class="text-copper">de vuelta</span>.
        </h1>
        <p class="text-navy/60 leading-relaxed mb-9">
            Entra para ver tus campañas o lanzar una nueva.
        </p>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium mb-2">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    autofocus
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
                    autocomplete="current-password"
                    class="w-full rounded-xl border border-navy/20 bg-white px-4 py-3 text-base text-navy placeholder:text-navy/30 focus:outline-none focus:border-navy/60 focus:ring-1 focus:ring-navy/20 transition"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-navy/60 cursor-pointer select-none">
                <input type="checkbox" name="remember" value="1" class="rounded border-navy/30 text-navy focus:ring-navy/20">
                Recordarme en este dispositivo
            </label>

            <button type="submit" class="w-full bg-navy text-cream px-6 py-3.5 rounded-full font-medium hover:bg-navy-light transition inline-flex items-center justify-center gap-2">
                Entrar
                <svg class="w-3 h-3 text-copper" viewBox="0 0 24 24"><use href="#roomie-sparkle"/></svg>
            </button>
        </form>

        <p class="text-sm text-navy/55 mt-8 text-center">
            ¿Aún no tienes cuenta?
            <a href="{{ route('register') }}" class="text-navy font-medium underline underline-offset-4 decoration-navy/30 hover:decoration-navy py-2 -my-2 inline-block">
                Crea una
            </a>
        </p>
    </div>
</x-layouts.app>
