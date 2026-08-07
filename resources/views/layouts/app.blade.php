<!DOCTYPE html>
<html lang="en" x-data x-init="$store.theme.init()">
@php
    $companyName = \App\Models\ApplicationSetting::where('key', 'company_name')->value('value');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="access-level" content="{{ auth('sanctum')->check() ? auth('sanctum')->user()->access_level : 0 }}">
    <meta name="currency-symbols" content="{{ json_encode(config('business.currency_symbols', [])) }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $companyName ?: config('app.name', 'Aronium Lite') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased" x-data="layoutData" x-init="init()">

<div class="flex h-screen overflow-hidden">

    <aside class="flex flex-col bg-[#0a0f28] text-white shrink-0 transition-all duration-300" :class="sidebarOpen ? 'w-56' : 'w-16'">
        <div class="flex flex-col items-center justify-center h-20 px-2 border-b border-white/10 overflow-hidden">
            <template x-if="companyLogo">
                <img :src="companyLogo" class="h-8 max-w-[100px] object-contain" alt="Logo">
            </template>
            <template x-if="!companyLogo">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0 ring-2 ring-white/20">{{ Str::substr(config('app.name', 'AL'), 0, 1) }}</div>
            </template>
            <span class="mt-1.5 text-xs font-semibold text-white/80 whitespace-nowrap truncate max-w-full" x-show="sidebarOpen" x-cloak x-text="companyName || '{{ config('app.name', 'Aronium Lite') }}'"></span>
        </div>

        <nav class="flex-1 py-4 space-y-1 px-2 overflow-y-auto">
            @php
                $sidebarIcons = [
                    'pos'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
                    'orders'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>',
                    'products'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>',
                    'customers' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
                    'reports'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
                    'promotions'=> '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>',
                    'taxes'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m1.5-1.5H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>',
                    'loyalty'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>',
                    'users'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
                    'roles'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>',
                    'inventory' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
                    'fiscal'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                    'printers'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>',
                    'activity'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>',
                    'branches'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z"/>',
                    'settings'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                ];

                $moduleRoutes = [
                    'pos'        => 'pos.index',
                    'orders'     => 'orders.index',
                    'products'   => 'web.products.index',
                    'customers'  => 'web.customers.index',
                    'reports'    => 'web.reports.index',
                    'promotions' => 'web.promotions.index',
                    'taxes'      => 'web.taxes.index',
                    'loyalty'    => 'web.loyalty.index',
                    'users'      => 'web.users.index',
                    'roles'      => 'web.roles.index',
                    'inventory'  => 'web.warehouses.index',
                    'activity'   => 'web.activity.index',
                    'fiscal'     => 'web.fiscal.index',
                    'printers'   => 'web.printers.index',
                    'branches'   => 'web.branches.index',
                    'settings'   => 'web.settings.index',
                    'dashboard'  => 'dashboard',
                ];

                $navModules = config('modules.list', []);
            @endphp

            @foreach ($navModules as $key => $mod)
                @php
                    $route = $moduleRoutes[$key] ?? null;
                    $minLevel = $mod['min_level'] ?? 0;
                    $show = true;
                @endphp
                @if ($route && isset($sidebarIcons[$key]) && $minLevel <= 9)
                <a href="{{ route($route) }}" class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs($route) ? 'bg-blue-600/20 text-blue-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}" title="{{ $mod['label'] }}" data-min-level="{{ $minLevel }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $sidebarIcons[$key] !!}</svg>
                    <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">{{ $mod['label'] }}</span>
                </a>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-2 space-y-1">
            <button @click="$store.theme.toggle()" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition-colors text-sm" title="Toggle theme">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path x-show="!$store.theme.dark" stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                    <path x-show="$store.theme.dark" stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
                <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Theme</span>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" onclick="localStorage.removeItem('auth_token')" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-gray-400 hover:bg-white/5 hover:text-white transition-colors text-sm">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden fixed top-3 left-3 z-50 p-2 rounded-md bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
    </button>

    <div class="flex-1 flex flex-col min-w-0">

        <header class="flex items-center justify-between h-16 px-4 lg:px-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
                <div class="relative flex-1 max-w-md hidden sm:block">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input type="search" placeholder="Search..." class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <template x-if="systemMode !== 'single' && branches.length === 1">
                    <span class="text-xs font-medium text-blue-400 bg-blue-500/10 px-2 py-1 rounded" x-text="branches[0].name"></span>
                </template>
                <template x-if="systemMode !== 'single' && branches.length > 1">
                    <select x-model="activeBranch" @change="switchBranch($event.target.value)" class="text-xs bg-gray-100 dark:bg-gray-700 border-none rounded px-2 py-1 text-gray-700 dark:text-gray-200">
                        <option value="">Select Branch</option>
                        <template x-for="b in branches" :key="b.id">
                            <option :value="b.id" x-text="b.name" :selected="b.id == currentBranchId"></option>
                        </template>
                    </select>
                </template>
                <template x-if="systemMode !== 'single' && branches.length === 0">
                    <span class="text-xs font-medium text-red-400 bg-red-500/10 px-2 py-1 rounded">Unable to load branches. Please refresh.</span>
                </template>
                <button @click="rtlMode = !rtlMode; localStorage.setItem('pos_dir', rtlMode ? 'rtl' : 'ltr'); document.documentElement.dir = rtlMode ? 'rtl' : 'ltr'" class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-500 dark:text-gray-400" title="Toggle RTL/LTR"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12m-5.25 0l1.5-1.5m-1.5 1.5l1.5 1.5m10.5-5.25h.008v.008h-.008V13.5zm0 5.25h.008v.008h-.008V18.75z"/></svg></button>
                <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="currentTime" x-init="currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); setInterval(() => currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), 1000)"></span>

                <div class="flex items-center gap-2" x-show="user">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium flex-shrink-0" x-text="(user?.first_name || user?.username || 'U').charAt(0)?.toUpperCase()"></div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden md:block" x-text="user?.first_name || user?.username || 'User'"></span>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('sys', {
            mode: localStorage.getItem('system_mode') || 'multi_branch',
            isSingle() { return this.mode === 'single'; },
            isMulti() { return this.mode !== 'single'; }
        });
        Alpine.data('layoutData', () => ({
            rtlMode: localStorage.getItem('pos_dir') === 'rtl',
            sidebarOpen: window.innerWidth >= 1024,
            currentTime: '',
            user: null,
            branches: [],
            activeBranch: '',
            currentBranchId: '',
            companyLogo: null,
            companyName: '',
            systemMode: localStorage.getItem('system_mode') || 'multi_branch',

            init() {
                document.documentElement.dir = this.rtlMode ? 'rtl' : 'ltr';
                const token = localStorage.getItem('auth_token');
                if (token) {
                    fetch('/api/auth/me', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        }
                    })
                    .then(res => res.ok ? res.json() : Promise.reject(res))
                    .then(data => { this.user = data.data || data; if (this.user.access_level !== undefined) localStorage.setItem('access_level', this.user.access_level); if (this.user.system_mode !== undefined) { this.systemMode = this.user.system_mode; localStorage.setItem('system_mode', this.user.system_mode); Alpine.store('sys').mode = this.user.system_mode; } })
                    .catch(() => {});

                    this.fetchBranches();
                    window.POS.loadCurrencySettings();
                    this.loadCompanyInfo();
                }

                window.addEventListener('resize', () => {
                    this.sidebarOpen = window.innerWidth >= 1024;
                });
            },

            async loadCompanyInfo() {
                try {
                    const data = await window.POS.api('/api/settings');
                    if (data?.data) {
                        if (data.data.logo) this.companyLogo = data.data.logo;
                        if (data.data.company_name) this.companyName = data.data.company_name;
                    }
                } catch (e) { /* ignore */ }
            },

            async fetchBranches() {
                const token = localStorage.getItem('auth_token');
                if (!token) return;
                let allBranches = [];
                try {
                    const res = await fetch('/api/branches', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        allBranches = data.data || data;
                        const userLevel = parseInt(localStorage.getItem('access_level') || '0');
                        if (userLevel >= 9) {
                            this.branches = allBranches;
                        } else {
                            await this.fetchMyBranches(allBranches, token);
                        }
                        if (this.branches.length === 1) {
                            const bid = this.branches[0].id;
                            this.activeBranch = bid;
                            this.currentBranchId = bid;
                            localStorage.setItem('active_branch_id', bid);
                            fetch('/api/branches/' + bid + '/switch', {
                                method: 'POST',
                                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                            }).catch(() => {});
                        } else {
                            const savedBranchId = localStorage.getItem('active_branch_id');
                            if (savedBranchId && this.branches.some(b => b.id == savedBranchId)) {
                                this.activeBranch = savedBranchId;
                                this.currentBranchId = savedBranchId;
                            }
                        }
                    }
                } catch (e) { this.branches = allBranches; }
            },

            async fetchMyBranches(allBranches, token) {
                try {
                    const res = await fetch('/api/auth/me', {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const udata = await res.json();
                        const user = udata.data || udata;
                        if (user.branches && user.branches.length > 0) {
                            const myIds = user.branches.map(b => b.id);
                            this.branches = allBranches.filter(b => myIds.includes(b.id));
                        } else if (user.branch_id) {
                            this.branches = allBranches.filter(b => b.id == user.branch_id);
                        } else {
                            this.branches = allBranches;
                        }
                    } else {
                        this.branches = allBranches;
                    }
                } catch (e) { this.branches = allBranches; }
            },

            switchBranch(branchId) {
                if (!branchId) {
                    localStorage.removeItem('active_branch_id');
                    this.currentBranchId = '';
                    this.activeBranch = '';
                    window.dispatchEvent(new CustomEvent('branch-changed'));
                    return;
                }
                this.currentBranchId = branchId;
                localStorage.setItem('active_branch_id', branchId);
                this.activeBranch = branchId;
                const token = localStorage.getItem('auth_token');
                if (token) {
                    fetch('/api/branches/' + branchId + '/switch', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    }).finally(() => {
                        window.location.reload();
                    });
                }
            }
        }));
});
</script>

<script>
(function() {
    var level = parseInt(localStorage.getItem('access_level') || '0');
    var sysMode = localStorage.getItem('system_mode') || 'multi_branch';
    document.querySelectorAll('.sidebar-nav-item').forEach(function(el) {
        var min = parseInt(el.getAttribute('data-min-level') || '0');
        if (level < min) el.style.display = 'none';
        if (sysMode === 'single' && el.getAttribute('title') === 'Branches') el.style.display = 'none';
    });
})();
</script>

@stack('scripts')

</body>
</html>
