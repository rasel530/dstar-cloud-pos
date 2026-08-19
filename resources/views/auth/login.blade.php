<!DOCTYPE html>
<html lang="en" x-data x-init="$store.theme.init()">
@php
    $companyLogo = \App\Models\ApplicationSetting::where('key', 'logo')->value('value');
    $companyName = \App\Models\ApplicationSetting::where('key', 'company_name')->value('value') ?: config('app.name', 'Point of Sale');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — {{ $companyName }}</title>
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
        .pin-cell {
            width: min(3.25rem, 13vw); height: 56px; text-align: center; font-size: 1.4rem; font-weight: 600;
            border-radius: 0.5rem; border: 2px solid #d1d5db; outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .dark .pin-cell { border-color: #4b5563; background-color: #374151; color: #f9fafb; }
        .pin-cell:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.3); }
        .pin-cell.filled { border-color: #3b82f6; background-color: #eff6ff; }
        .dark .pin-cell.filled { background-color: #1e3a5f; }
        .pin-cell.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.2); animation: shake 0.4s ease; }
        @keyframes shake {
            0%,100%{transform:translateX(0)}20%{transform:translateX(-4px)}40%{transform:translateX(4px)}60%{transform:translateX(-3px)}80%{transform:translateX(3px)}
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center px-4 transition-colors">

<div class="w-full max-w-sm" x-data="loginForm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700">

        <div class="flex flex-col items-center mb-8">
            @if($companyLogo)
                <div class="w-40 h-16 max-w-[200px] bg-white rounded-xl flex items-center justify-center px-2 py-1.5 shadow-sm mb-4">
                    <img src="/logo" class="max-w-full max-h-full object-contain" alt="Logo">
                </div>
            @else
                <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center mb-4 ring-2 ring-white/20 dark:ring-white/10">
                    <span class="text-white text-xl font-bold">{{ Str::substr($companyName, 0, 1) }}</span>
                </div>
            @endif
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $companyName }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sign in to your account</p>
        </div>

        {{-- Mode Toggle --}}
        <div class="flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1 mb-6">
            <button @click="switchTab('pin')"
                :class="tab === 'pin' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors duration-200">
                Quick PIN
            </button>
            <button @click="switchTab('password')"
                :class="tab === 'password' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors duration-200">
                Password
            </button>
        </div>

        {{-- View Container: grid forces both panels into same cell, zero layout shift --}}
        <div style="display: grid; grid-template: 'panel' 1fr / 1fr;">

        {{-- ==================== QUICK PIN TAB ==================== --}}
        <div x-show="tab === 'pin'" x-cloak style="grid-area: panel">
            <form @submit.prevent="submitPin" class="space-y-5" novalidate>
                <div>
                    <label for="empId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Employee ID</label>
                    <input id="empId" type="number" x-model.number="employeeNumber"
                        inputmode="numeric" min="1"
                        placeholder="Enter your employee ID"
                        class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Enter 4-digit PIN</label>
                    <div class="flex justify-center gap-3">
                        <template x-for="(digit, index) in pinDigits" :key="index">
                            <input type="number"
                                :id="'pin-' + index"
                                x-model="pinDigits[index]"
                                @input="handlePinInput($event, index)"
                                @keydown="handlePinKeydown($event, index)"
                                @paste="handlePinPaste($event)"
                                @focus="$event.target.select()"
                                inputmode="numeric" min="0" max="9" maxlength="1"
                                :class="pinError ? 'pin-cell error' : (pinDigits[index] !== '' ? 'pin-cell filled' : 'pin-cell')">
                        </template>
                    </div>
                </div>

                <div x-show="locked" x-cloak class="text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-4 py-3">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="font-medium" x-text="errorMsg"></span>
                    </div>
                    <button type="button" @click="switchTab('password')" class="text-xs underline hover:no-underline mt-1">Use password instead</button>
                </div>

                <div x-show="errorMsg && !locked" x-cloak class="text-sm text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2.5" x-text="errorMsg"></div>

                <button type="submit" :disabled="loading || !employeeNumber || pinCode.length < 4"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
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

        {{-- ==================== PASSWORD TAB ==================== --}}
        <div x-show="tab === 'password'" x-cloak style="grid-area: panel">
            <form @submit.prevent="submitPassword" class="space-y-5" novalidate>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email address</label>
                    <input id="email" type="email" x-model="email" required autocomplete="email"
                        placeholder="you@example.com"
                        class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                    <div class="relative">
                        <input id="password" :type="showPassword ? 'text' : 'password'" x-model="password" required autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 pr-10 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                </div>

                <div x-show="errorMsg" x-cloak class="text-sm text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2.5" x-text="errorMsg"></div>

                <button type="submit" :disabled="loading"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
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

        </div>{{-- End view container --}}
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
        &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
    </p>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loginForm', () => ({
            tab: 'pin',
            employeeNumber: '',
            email: '',
            password: '',
            showPassword: false,
            loading: false,
            errorMsg: '',
            pinDigits: ['', '', '', ''],
            pinError: false,
            locked: false,

            init() {
                this.$nextTick(() => {
                    const el = document.getElementById('empId');
                    if (el) el.focus();
                });
            },

            get pinCode() { return this.pinDigits.join(''); },

            switchTab(t) {
                this.tab = t;
                this.errorMsg = '';
                this.pinDigits = ['', '', '', ''];
                this.pinError = false;
                this.locked = false;
                this.$nextTick(() => {
                    if (t === 'pin') {
                        const el = document.getElementById('empId');
                        if (el) el.focus();
                    } else {
                        const el = document.getElementById('email');
                        if (el) el.focus();
                    }
                });
            },

            handlePinInput(event, index) {
                this.pinError = false;
                this.errorMsg = '';
                this.locked = false;
                const value = event.target.value.replace(/[^0-9]/g, '').slice(0, 1);
                this.pinDigits[index] = value;
                if (value && index < 3) {
                    const next = document.getElementById('pin-' + (index + 1));
                    if (next) next.focus();
                }
            },

            handlePinKeydown(event, index) {
                if (event.key === 'Backspace' && !this.pinDigits[index] && index > 0) {
                    const prev = document.getElementById('pin-' + (index - 1));
                    if (prev) { prev.focus(); prev.select(); }
                }
                if (event.key === 'ArrowLeft' && index > 0) {
                    event.preventDefault();
                    const prev = document.getElementById('pin-' + (index - 1));
                    if (prev) prev.focus();
                }
                if (event.key === 'ArrowRight' && index < 3) {
                    event.preventDefault();
                    const next = document.getElementById('pin-' + (index + 1));
                    if (next) next.focus();
                }
            },

            handlePinPaste(event) {
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/[^0-9]/g, '').slice(0, 4).split('');
                this.pinDigits = ['', '', '', ''];
                digits.forEach((d, i) => { if (i < 4) this.pinDigits[i] = d; });
                const lastIndex = Math.min(digits.length, 3);
                const last = document.getElementById('pin-' + lastIndex);
                if (last) last.focus();
            },

            async submitPin() {
                this.errorMsg = '';
                this.pinError = false;
                this.locked = false;
                this.loading = true;

                try {
                    const response = await fetch('/api/auth/employee-pin-login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ employee_number: this.employeeNumber, pin_code: this.pinCode }),
                    });

                    const data = await response.json();

                    if (response.status === 423) { this.locked = true; this.pinError = true; }
                    if (!response.ok) {
                        this.errorMsg = data.message || 'Invalid credentials. Please try again.';
                        this.pinError = true;
                        this.pinDigits = ['', '', '', ''];
                        this.$nextTick(() => { const el = document.getElementById('pin-0'); if (el) el.focus(); });
                        return;
                    }

                    localStorage.setItem('auth_token', data.data?.token || data.token);
                    localStorage.setItem('access_level', data.data?.user?.access_level || data.user?.access_level || 0);
                    localStorage.setItem('branch_id', data.data?.user?.branch_id || data.user?.branch_id || '');
                    window.location.href = '/pos';
                } catch (err) {
                    this.errorMsg = 'Unable to connect to the server. Please try again.';
                } finally {
                    this.loading = false;
                }
            },

            async submitPassword() {
                this.errorMsg = '';
                this.loading = true;

                try {
                    const response = await fetch('/api/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ email: this.email, password: this.password }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.errorMsg = data.message || 'Invalid credentials. Please try again.';
                        return;
                    }

                    localStorage.setItem('auth_token', data.data?.token || data.token);
                    localStorage.setItem('access_level', data.data?.user?.access_level || data.user?.access_level || 0);
                    localStorage.setItem('branch_id', data.data?.user?.branch_id || data.user?.branch_id || '');

                    const hasPin = data.data?.user?.has_pin || data.user?.has_pin;
                    window.location.href = hasPin ? '/pos' : '/setup-pin';
                } catch (err) {
                    this.errorMsg = 'Unable to connect to the server. Please try again.';
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>

</body>
</html>
