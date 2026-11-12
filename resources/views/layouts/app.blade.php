<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inul Wijaya Frame')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        button, a, input, select, textarea, label {
            touch-action: manipulation;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    @if (session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4500)"
             x-transition
             class="no-print pointer-events-none fixed inset-x-4 top-4 sm:left-auto sm:right-4 z-50 flex justify-end">
            <div class="pointer-events-auto flex items-center gap-3 rounded-xl bg-gradient-to-r from-stone-900 to-amber-900 px-4 py-3 text-sm font-medium text-white shadow-lg shadow-stone-900/20 border border-amber-500/30">
                <svg class="h-5 w-5 shrink-0 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="ml-2 p-1 text-amber-200 hover:text-white cursor-pointer">&times;</button>
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div x-data="{ show: true }"
             x-show="show"
             x-transition
             class="no-print pointer-events-none fixed inset-x-4 top-4 sm:left-auto sm:right-4 z-50 flex justify-end">
            <div class="pointer-events-auto max-w-md w-full sm:w-auto rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-lg">
                <div class="flex items-start justify-between gap-3">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" @click="show = false" class="p-1 text-rose-500 hover:text-rose-700 font-bold cursor-pointer">&times;</button>
                </div>
            </div>
        </div>
    @endif

    @yield('content')
</body>
</html>
