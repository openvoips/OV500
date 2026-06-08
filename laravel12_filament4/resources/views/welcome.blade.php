<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-6 py-16">
        <p class="text-sm uppercase tracking-[0.35em] text-sky-300">OV500</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-6xl">Laravel 12 + Filament 4 portal</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
            This rebuild scaffold connects Laravel models and Filament resources to the existing switch,
            Kamailio, and CDR databases while the CodeIgniter modules are migrated incrementally.
        </p>
        <div class="mt-10 flex gap-4">
            <a class="rounded-lg bg-sky-500 px-5 py-3 font-semibold text-slate-950" href="{{ url('/admin') }}">Open admin panel</a>
            <a class="rounded-lg border border-slate-700 px-5 py-3 font-semibold text-white" href="https://filamentphp.com/docs/4.x" rel="noreferrer">Filament 4 docs</a>
        </div>
    </main>
</body>
</html>
