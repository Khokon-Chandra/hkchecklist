<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HK Checklist') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700&display=swap" rel="stylesheet" />

    <!-- App assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <!-- Top bar -->
    <header class="relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-8 w-auto text-gray-900 dark:text-gray-100 fill-current" />
                    <span class="hidden sm:block font-semibold">{{ config('app.name', 'HK Checklist') }}</span>
                </a>

                <div class="flex items-center gap-3">
                    <!-- Theme toggle -->
                    <button
                        @click="$store.theme?.toggle ? $store.theme.toggle() : (document.documentElement.classList.toggle('dark'))"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="Toggle theme" aria-label="Toggle theme">
                        <!-- simple icon -->
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path
                                d="M12 4a1 1 0 0 1 1 1v1h-2V5a1 1 0 0 1 1-1Zm0 14a1 1 0 0 1 1 1v1h-2v-1a1 1 0 0 1 1-1Zm8-6a1 1 0 0 1 1 1h1v-2h-1a1 1 0 0 1-1 1ZM3 13a1 1 0 0 1-1-1H1v2h1a1 1 0 0 1 1-1Zm13.95 6.536.707.707-1.414 1.414-.707-.707 1.414-1.414ZM6.05 3.05l.707.707L5.343 5.17l-.707-.707L6.05 3.05Zm13.607-.707.707.707-1.414 1.414-.707-.707 1.414-1.414ZM4.343 18.828l.707.707L3.636 20.95l-.707-.707 1.414-1.414Z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="hidden sm:inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Go to Dashboard
                        </a>
                    @endauth

                    @guest
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                                Log in
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="hidden sm:inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Get Started
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="h-[18rem] bg-gradient-to-br from-indigo-600 to-indigo-500 dark:from-indigo-700 dark:to-indigo-600">
            </div>
            <svg class="w-full h-[6rem] text-indigo-600/20 dark:text-indigo-500/10" viewBox="0 0 1440 320"
                preserveAspectRatio="none">
                <path fill="currentColor"
                    d="M0,64L80,58.7C160,53,320,43,480,69.3C640,96,800,160,960,181.3C1120,203,1280,181,1360,170.7L1440,160L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z">
                </path>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 sm:pt-16">
            <div class="grid lg:grid-cols-2 items-center gap-8">
                <div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-white/70 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 ring-1 ring-white/60 dark:ring-indigo-800">
                        Airbnb Housekeeping Checklist
                    </span>
                    <h1 class="mt-4 text-3xl sm:text-4xl font-semibold text-white">
                        Accountability that’s easy for owners,<br class="hidden sm:block" /> fast for housekeepers.
                    </h1>
                    <p class="mt-4 text-white/90">
                        Assign properties, complete room checklists, upload timestamped photos, and stay on schedule
                        with an integrated calendar.
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-gray-100">
                                Open Dashboard
                            </a>
                            <a href="{{ route('calendar.index') }}"
                                class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800">
                                View Calendar
                            </a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-gray-100">
                                    Create an account
                                </a>
                            @endif
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-800">
                                    Log in
                                </a>
                            @endif
                        @endauth
                    </div>
                    <div class="mt-6 flex items-center gap-6 text-white/80 text-sm">
                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            GPS Gate</div>
                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            8+ Photos / Room</div>
                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Room & Inventory</div>
                    </div>
                </div>

                <div class="relative">
                    <div
                        class="rounded-xl border border-white/20 bg-white/80 backdrop-blur shadow-xl overflow-hidden dark:bg-gray-900/70 dark:border-gray-700">
                        <div class="px-5 py-4 border-b border-gray-200/70 dark:border-gray-700 flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-red-400"></div>
                            <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                            <div class="h-3 w-3 rounded-full bg-green-400"></div>
                            <div class="ms-auto text-xs text-gray-500 dark:text-gray-400">Preview</div>
                        </div>
                        <div class="p-5 grid grid-cols-2 gap-4">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="text-sm font-medium mb-1">Properties</div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Manage beds/baths, geo radius,
                                    rooms.</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="text-sm font-medium mb-1">Rooms & Tasks</div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Room-by-room tasks, inventory
                                    checks.</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="text-sm font-medium mb-1">Checklist Wizard</div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Rooms → Inventory → Photos.</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <div class="text-sm font-medium mb-1">Calendar</div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">See scheduled dates at a glance.</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -z-10 -left-10 -bottom-10 h-40 w-40 rounded-full bg-indigo-400/30 blur-3xl">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h3 class="text-lg font-semibold mb-6">Why teams use {{ config('app.name', 'HK Checklist') }}</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $features = [
                        [
                            'title' => 'Assign & track',
                            'desc' => 'Owners assign housekeepers by property and date, see status live.',
                        ],
                        [
                            'title' => 'GPS start gate',
                            'desc' => 'Start requires on-site confirmation within property radius.',
                        ],
                        ['title' => 'Room checklists', 'desc' => 'Clear tasks, notes, and completion marks per room.'],
                        [
                            'title' => 'Inventory checks',
                            'desc' => 'Separate inventory step ensures essentials are replenished.',
                        ],
                        [
                            'title' => 'Photo evidence',
                            'desc' => '8+ photos per room with timestamp overlay for accountability.',
                        ],
                        [
                            'title' => 'Calendar view',
                            'desc' => 'Monthly schedule for HKs, owner-scoped, or system-wide for admin.',
                        ],
                    ];
                @endphp

                @foreach ($features as $f)
                    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5">
                        <div
                            class="mb-2 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600/10 text-indigo-600 dark:text-indigo-300">
                            ★</div>
                        <div class="font-medium">{{ $f['title'] }}</div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Steps -->
    <section class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold">How it works</h3>
                <ol class="mt-4 grid sm:grid-cols-3 gap-6 text-sm">
                    <li class="rounded-lg p-4 bg-gray-50 dark:bg-gray-900/40">
                        <div class="font-medium">1. Set up</div>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">Create properties, rooms, and default tasks.
                        </p>
                    </li>
                    <li class="rounded-lg p-4 bg-gray-50 dark:bg-gray-900/40">
                        <div class="font-medium">2. Assign</div>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">Schedule a housekeeper via Manage Sessions.
                        </p>
                    </li>
                    <li class="rounded-lg p-4 bg-gray-50 dark:bg-gray-900/40">
                        <div class="font-medium">3. Complete</div>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">HK confirms GPS, completes rooms, uploads
                            photos, submits.</p>
                    </li>
                </ol>
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ route('manage.sessions.index') }}"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Manage Sessions
                        </a>
                        <a href="{{ route('calendar.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
                            View Calendar
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Get Started
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">
                                Log in
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-sm text-gray-600 dark:text-gray-300">
            <div class="flex items-center justify-between">
                <p>© {{ date('Y') }} {{ config('app.name', 'HK Checklist') }}. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('calendar.index') }}" class="hover:underline">Calendar</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="hover:underline">Login</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hover:underline">Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
