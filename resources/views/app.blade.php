{{-- resources/views/app.blade.php --}}
{{-- C'est la SEULE vue Blade du projet. Inertia injecte tout le reste via @inertia --}}
<!DOCTYPE html>
<html
    lang="fr"
    class="{{ auth()->user()?->theme === 'dark' ? 'dark' : 'light' }}"
    style="color-scheme: {{ auth()->user()?->theme === 'dark' ? 'dark' : 'light' }}"
>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- Titre dynamique géré par Inertia (défini dans chaque page Vue) --}}
    <title inertia>{{ config('app.name', 'Ya Consulting') }}</title>

    {{-- Fonts : Syne (display) + DM Sans (body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />

    {{-- Material Symbols (icônes) --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    {{-- Inertia Head (pour <Head> dans les composants Vue) --}}
    @inertiaHead

    {{-- Vite Assets (CSS + JS compilés) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    {{-- Point d'entrée Inertia — Vue prend le relais ici --}}
    @inertia
</body>
</html>