<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Legacy controller</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-5xl px-6 py-12">
        <a class="text-sm font-semibold text-sky-300" href="{{ url('/') }}">&larr; OV500 Laravel portal</a>

        <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/70 p-8 shadow-2xl shadow-slate-950/40">
            <p class="text-sm uppercase tracking-[0.35em] text-sky-300">Standalone Laravel route</p>
            <h1 class="mt-4 text-3xl font-semibold tracking-tight sm:text-5xl">
                {{ $definition['label'] ?? ucfirst($controller) }} controller
            </h1>

            <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-300">
                Laravel handled <code class="rounded bg-slate-800 px-2 py-1 text-sky-200">/{{ $requestedPath }}</code>
                directly, without booting the legacy CodeIgniter controller from
                <code class="rounded bg-slate-800 px-2 py-1 text-sky-200">portal/application/controllers</code>.
            </p>

            @if ($definition === null)
                <div class="mt-6 rounded-xl border border-amber-400/30 bg-amber-400/10 p-4 text-amber-100">
                    This legacy controller is not registered in the Laravel compatibility map yet.
                </div>
            @else
                <div class="mt-6 rounded-xl border border-slate-700 bg-slate-950/60 p-4 text-slate-300">
                    <dl class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Controller</dt>
                            <dd class="mt-1 font-mono text-sky-200">{{ $controller }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Action</dt>
                            <dd class="mt-1 font-mono text-sky-200">{{ $action ?? 'index' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Status</dt>
                            <dd class="mt-1 font-mono text-sky-200">{{ $definition['status'] }}</dd>
                        </div>
                    </dl>

                    @if ($parameters)
                        <p class="mt-4 text-sm text-slate-400">
                            Extra path parameters: <code class="rounded bg-slate-800 px-2 py-1 text-sky-200">{{ $parameters }}</code>
                        </p>
                    @endif
                </div>
            @endif
        </section>

        <section class="mt-8">
            <h2 class="text-xl font-semibold">Registered legacy controller redirects</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($controllers as $name => $item)
                    <a class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 transition hover:border-sky-400 hover:bg-slate-900" href="{{ url($name) }}">
                        <span class="block font-semibold text-slate-100">{{ $item['label'] }}</span>
                        <span class="mt-1 block text-sm text-slate-400">/{{ $name }}</span>
                        <span class="mt-3 inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $item['status'] === 'redirect' ? 'bg-emerald-400/10 text-emerald-300' : 'bg-amber-400/10 text-amber-300' }}">
                            {{ $item['status'] === 'redirect' ? 'Integrated' : 'Migration pending' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>
