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
    <title>Set Up PIN — {{ $companyName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .pin-box { width: min(3.25rem, 13vw); height: 60px; text-align: center; font-size: 1.5rem; font-weight: 600; border-radius: 0.5rem; border: 2px solid #d1d5db; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .dark .pin-box { border-color: #4b5563; background-color: #374151; color: #f9fafb; }
        .pin-box:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.3); }
        .pin-box.filled { border-color: #3b82f6; background-color: #eff6ff; }
        .dark .pin-box.filled { background-color: #1e3a5f; }
        .pin-box.error { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }
        input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col items-center justify-center px-4 transition-colors">

<div class="w-full max-w-sm" x-data="setupPinForm">
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
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set up your 4-digit login PIN</p>
        </div>

        <template x-if="!step">
            <div class="space-y-5">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-medium mb-1">Create a PIN for faster logins</p>
                    <p>You'll still be able to use your password. The PIN lets you sign in quickly with just 4 digits.</p>
                </div>

                <button @click="step = 'setup'" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Set Up PIN
                </button>

                <button @click="skip" class="w-full py-2.5 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                    Skip for now
                </button>
            </div>
        </template>

        <template x-if="step === 'setup'">
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">New PIN</label>
                    <div class="flex justify-center gap-3">
                        <template x-for="(digit, index) in pinDigits" :key="index">
                            <input type="number" :id="'pin-' + index" x-model="pinDigits[index]"
                                @input="handlePinInput($event, index, 'pinDigits')" @keydown="handlePinKeydown($event, index, 'pinDigits')" @paste="handlePinPaste($event, 'pinDigits')" @focus="$event.target.select()"
                                inputmode="numeric" min="0" max="9" maxlength="1"
                                :class="pinMismatch ? 'pin-box error' : (pinDigits[index] !== '' ? 'pin-box filled' : 'pin-box')">
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Confirm PIN</label>
                    <div class="flex justify-center gap-3">
                        <template x-for="(digit, index) in confirmDigits" :key="'c' + index">
                            <input type="number" :id="'cpin-' + index" x-model="confirmDigits[index]"
                                @input="handlePinInput($event, index, 'confirmDigits')" @keydown="handlePinKeydown($event, index, 'confirmDigits')" @paste="handlePinPaste($event, 'confirmDigits')" @focus="$event.target.select()"
                                inputmode="numeric" min="0" max="9" maxlength="1"
                                :class="pinMismatch ? 'pin-box error' : (confirmDigits[index] !== '' ? 'pin-box filled' : 'pin-box')">
                        </template>
                    </div>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                    <p :class="pinCode.length === 4 ? 'text-green-600 dark:text-green-400' : ''">• Must be exactly 4 digits</p>
                    <p :class="pinCode.length === 4 && !hasRepeatedDigits() ? 'text-green-600 dark:text-green-400' : ''">• Cannot be repeated digits (e.g. 0000, 1111)</p>
                    <p :class="pinCode.length === 4 && !isSequential() ? 'text-green-600 dark:text-green-400' : ''">• Cannot be sequential (e.g. 1234, 4321)</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Verify your password</label>
                    <input id="password" type="password" x-model="password" required placeholder="Enter your current password"
                        class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                </div>

                <div x-show="error" x-cloak class="text-sm text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2.5" x-text="error"></div>

                <button type="submit" :disabled="loading || pinCode.length < 4 || confirmCode.length < 4 || !password"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors">
                    <span x-show="!loading">Save PIN</span>
                    <span x-show="loading" x-cloak class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Saving…
                    </span>
                </button>
            </form>
        </template>
    </div>

    <p class="mt-8 text-center text-xs text-gray-400 dark:text-gray-600">
        &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
    </p>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('setupPinForm', () => ({
            step: '',
            pinDigits: ['', '', '', ''],
            confirmDigits: ['', '', '', ''],
            password: '',
            pinMismatch: false,
            loading: false,
            error: '',

            get pinCode() { return this.pinDigits.join(''); },
            get confirmCode() { return this.confirmDigits.join(''); },

            hasRepeatedDigits() {
                const p = this.pinCode;
                if (p.length !== 4) return false;
                return p[0] === p[1] && p[1] === p[2] && p[2] === p[3];
            },

            isSequential() {
                const p = this.pinCode;
                if (p.length !== 4) return false;
                const nums = p.split('').map(Number);
                const asc = nums[1] === nums[0] + 1 && nums[2] === nums[1] + 1 && nums[3] === nums[2] + 1;
                const desc = nums[1] === nums[0] - 1 && nums[2] === nums[1] - 1 && nums[3] === nums[2] - 1;
                return asc || desc;
            },

            handlePinInput(event, index, target) {
                this.pinMismatch = false;
                this.error = '';
                const value = event.target.value.replace(/[^0-9]/g, '').slice(0, 1);
                this[target][index] = value;
                if (value && index < 3) {
                    const prefix = target === 'pinDigits' ? 'pin-' : 'cpin-';
                    const next = document.getElementById(prefix + (index + 1));
                    if (next) next.focus();
                }
            },

            handlePinKeydown(event, index, target) {
                if (event.key === 'Backspace' && !this[target][index] && index > 0) {
                    const prefix = target === 'pinDigits' ? 'pin-' : 'cpin-';
                    const prev = document.getElementById(prefix + (index - 1));
                    if (prev) { prev.focus(); prev.select(); }
                }
            },

            handlePinPaste(event, target) {
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/[^0-9]/g, '').slice(0, 4).split('');
                this[target] = ['', '', '', ''];
                digits.forEach((d, i) => { if (i < 4) this[target][i] = d; });
                const prefix = target === 'pinDigits' ? 'pin-' : 'cpin-';
                const last = document.getElementById(prefix + Math.min(digits.length, 3));
                if (last) last.focus();
            },

            skip() {
                window.location.href = '/pos';
            },

            async submit() {
                this.error = '';
                this.pinMismatch = false;

                if (this.pinCode !== this.confirmCode) {
                    this.error = 'PINs do not match';
                    this.pinMismatch = true;
                    return;
                }

                if (this.hasRepeatedDigits()) {
                    this.error = 'PIN cannot be repeated digits (e.g. 0000, 1111)';
                    return;
                }

                if (this.isSequential()) {
                    this.error = 'PIN cannot be sequential (e.g. 1234, 4321)';
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('/api/auth/setup-pin', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                        },
                        body: JSON.stringify({
                            pin: this.pinCode,
                            pin_confirmation: this.confirmCode,
                            password: this.password,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.error = data.message || 'Failed to set up PIN';
                        return;
                    }

                    window.location.href = '/pos';
                } catch (err) {
                    this.error = 'Unable to connect. Please try again.';
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
</script>

</body>
</html>
