<!DOCTYPE html>
<html lang="en" x-data x-init="$store.theme.init()">
@php
    $companyLogo = \App\Models\ApplicationSetting::where('key', 'logo')->value('value');
    $companyName = \App\Models\ApplicationSetting::where('key', 'company_name')->value('value');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ $companyName ?: config('app.name', 'Aronium Lite') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .theme-toggle:hover .sun-icon { display: none; }
        .theme-toggle:hover .moon-icon { display: block; }
        .dark .theme-toggle .moon-icon { display: none; }
        .dark .theme-toggle .sun-icon { display: block; }
        .dark .theme-toggle:hover .sun-icon { display: none; }
        .dark .theme-toggle:hover .moon-icon { display: block; }
        .sun-icon { display: none; }
        .moon-icon { display: block; }
        .dark .sun-icon { display: block; }
        .dark .moon-icon { display: none; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center px-4 transition-colors">

<div class="w-full max-w-sm" x-data="loginForm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700">

        <div class="flex flex-col items-center mb-8">
            @if($companyLogo)
                <img src="{{ $companyLogo }}" class="h-16 max-w-[200px] object-contain mb-4" alt="Logo">
            @else
                <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center mb-4 ring-2 ring-white/20 dark:ring-white/10">
                    <span class="text-white text-xl font-bold">{{ Str::substr($companyName ?: config('app.name', 'AL'), 0, 1) }}</span>
                    <span class="text-white text-xl font-bold">{{ Str::substr($companyName ?: config('app.name', 'AL'), 0, 1) }}</span>
                </div>
            @endif
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $companyName ?: config('app.name', 'Aronium Lite') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sign in to your account</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email address</label>
                <input
                    id="email"
                    type="email"
                    x-model="email"
                    required
                    autocomplete="email"
                    autofocus
                    placeholder="you@example.com"
                    class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        x-model="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-2.5 pr-10 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                    >
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
            </div>

            <div x-show="error" x-cloak class="text-sm text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2.5" x-text="error"></div>

            <button
                type="submit"
                :disabled="loading"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
            >
                <span x-show="!loading">Sign In</span>
                <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Signing in…
                </span>
            </button>
        </form>
    </div>

    <button @click="$store.theme.toggle()" class="theme-toggle mt-6 mx-auto flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
        <svg x-show="!$store.theme.dark" class="sun-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
        </svg>
        <svg x-show="$store.theme.dark" class="moon-icon w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
        </svg>
        <span x-show="!$store.theme.dark">Switch to light mode</span>
        <span x-show="$store.theme.dark">Switch to dark mode</span>
    </button>

    <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-600">
        &copy; {{ date('Y') }} {{ $companyName ?: config('app.name', 'Aronium Lite') }}. All rights reserved.
    </p>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loginForm', () => ({
            email: '',
            password: '',
            loading: false,
            error: '',
            showPassword: false,

            async submit() {
                this.error = '';
                this.loading = true;

                try {
                    const response = await fetch('/api/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            email: this.email,
                            password: this.password,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.error = data.message || 'Invalid credentials. Please try again.';
                        return;
                    }

                    localStorage.setItem('auth_token', data.data?.token || data.token);
                    localStorage.setItem('access_level', data.data?.user?.access_level || data.user?.access_level || 0);
                    localStorage.setItem('branch_id', data.data?.user?.branch_id || data.user?.branch_id || '');
                    window.location.href = '/pos';
                } catch (err) {
                    this.error = 'Unable to connect to the server. Please try again.';
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>

</body>
</html>
