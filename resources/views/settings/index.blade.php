@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div x-data="settingsManager" x-init="init()" class="px-6">

    <div class="py-4">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Settings</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your store configuration</p>
    </div>

    <div class="flex flex-wrap gap-1.5 border-b border-slate-200 dark:border-slate-700 pb-0">
        <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[1px] transition-colors">
            <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            General
        </button>
        <button @click="activeTab = 'pos'" :class="activeTab === 'pos' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[1px] transition-colors">
            <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            POS
        </button>
        <button @click="activeTab = 'receipt'" :class="activeTab === 'receipt' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[1px] transition-colors">
            <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
            Receipt
        </button>
        <button @click="activeTab = 'notifications'" :class="activeTab === 'notifications' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[1px] transition-colors">
            <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
            Notifications
        </button>
        <button @click="activeTab = 'system'" :class="activeTab === 'system' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[1px] transition-colors">
            <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            System
        </button>
    </div>

    <div x-show="activeTab === 'general'" x-cloak class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Company Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Company Name</label>
                    <input type="text" x-model="form.company_name" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Address</label>
                    <input type="text" x-model="form.company_address" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Phone</label>
                    <input type="text" x-model="form.company_phone" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                    <input type="email" x-model="form.company_email" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Regional Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Currency</label>
                    <select x-model="form.currency" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        @foreach(config('business.currencies', []) as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Timezone</label>
                    <select x-model="form.timezone" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        @foreach(config('business.timezones', []) as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'pos'" x-cloak class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Product Grid Layout</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Grid Columns</label>
                    <select x-model="form.grid_columns" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="3">3 Columns</option>
                        <option value="4">4 Columns</option>
                        <option value="5">5 Columns</option>
                        <option value="6">6 Columns</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Grid Rows</label>
                    <select x-model="form.grid_rows" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="3">3 Rows</option>
                        <option value="4">4 Rows</option>
                        <option value="5">5 Rows</option>
                        <option value="6">6 Rows</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Pricing & Tax</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Default Tax Rate (%)</label>
                    <input type="number" x-model.number="form.default_tax_rate" min="0" max="100" step="0.01" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Rounding Rule</label>
                    <select x-model="form.rounding_rule" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="none">No Rounding</option>
                        <option value="nearest_001">Nearest 0.01</option>
                        <option value="nearest_005">Nearest 0.05</option>
                        <option value="nearest_010">Nearest 0.10</option>
                        <option value="nearest_050">Nearest 0.50</option>
                        <option value="nearest_1">Nearest 1</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Interface</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Sound Effects</span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Play sounds on sale completion, alerts, and errors</p>
                    </div>
                    <button
                        type="button"
                        @click="form.sound_effects = !form.sound_effects"
                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                        :class="form.sound_effects ? 'bg-blue-600 dark:bg-blue-500' : 'bg-slate-300 dark:bg-slate-600'"
                        :aria-checked="form.sound_effects"
                        role="switch"
                    >
                        <span
                            class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                            :class="form.sound_effects ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Payment Confirmation</span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Require confirmation dialog before finalizing payment</p>
                    </div>
                    <button
                        type="button"
                        @click="form.payment_confirmation = !form.payment_confirmation"
                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                        :class="form.payment_confirmation ? 'bg-blue-600 dark:bg-blue-500' : 'bg-slate-300 dark:bg-slate-600'"
                        :aria-checked="form.payment_confirmation"
                        role="switch"
                    >
                        <span
                            class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                            :class="form.payment_confirmation ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'receipt'" x-cloak class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Receipt Content</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Header Text</label>
                    <textarea x-model="form.receipt_header" rows="2" placeholder="Thank you for your purchase!" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Footer Text</label>
                    <textarea x-model="form.receipt_footer" rows="2" placeholder="Please come again! | No returns after 7 days." class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Receipt Settings</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center bg-slate-50 dark:bg-slate-700 overflow-hidden">
                            <template x-if="!form.logo_preview">
                                <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                            </template>
                            <img x-show="form.logo_preview" :src="form.logo_preview" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <label class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-600 cursor-pointer transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                Upload Logo
                                <input type="file" accept="image/*" class="hidden" @change="handleLogoUpload($event)">
                            </label>
                            <button x-show="form.logo_preview" @click="form.logo_preview = null; form.logo = null" class="ml-2 text-xs text-red-500 hover:text-red-600 transition-colors">Remove</button>
                        </div>
                    </div>
                </div>
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Number of Copies</label>
                    <select x-model.number="form.receipt_copies" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="1">1 Copy</option>
                        <option value="2">2 Copies</option>
                        <option value="3">3 Copies</option>
                    </select>
                </div>
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Paper Width</label>
                    <select x-model.number="form.paper_width" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="80">80 mm (Standard)</option>
                        <option value="58">58 mm (Compact)</option>
                    </select>
                </div>
                <div class="max-w-xs">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Auto-Print Receipt</label>
                    <select x-model="form.receipt_auto_print" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="false">Off</option>
                        <option value="true">Print after sale</option>
                    </select>
                </div>
                <div class="max-w-xs">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" :checked="form.receipt_qr_enabled === 'true'" @change="form.receipt_qr_enabled = $event.target.checked ? 'true' : 'false'" class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable QR Code on Receipt</span>
                    </label>
                </div>
                <div class="max-w-xs" x-show="form.receipt_qr_enabled === 'true'">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">QR Base URL</label>
                    <input type="text" x-model="form.receipt_qr_base_url" placeholder="https://yourcompany.com" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'notifications'" x-cloak class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">Notification Display</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Duration (seconds)</label>
                    <input type="number" x-model.number="form.notification_duration" min="1" max="30" step="1" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">How long toast notifications stay visible</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Position</label>
                    <select x-model="form.notification_position" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
                        <option value="top-right">Top Right</option>
                        <option value="top-left">Top Left</option>
                        <option value="top-center">Top Center</option>
                        <option value="bottom-right">Bottom Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="bottom-center">Bottom Center</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'system'" x-cloak class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white mb-4">System Mode</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Choose how the system manages branches. Switching modes takes effect immediately and does not affect existing data.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative rounded-xl border-2 p-5 cursor-pointer transition-all"
                    :class="form.system_mode === 'multi_branch' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500'"
                    @click="form.system_mode = 'multi_branch'">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-9.75 4.5h10.5m-15.75 4.5h15.75"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">Multi-Branch Mode</span>
                            <span x-show="form.system_mode === 'multi_branch'" class="ml-2 text-xs text-blue-600 dark:text-blue-400 font-medium">Active</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Full branch management with independent inventories, stock transfers, and branch-scoped operations.</p>
                </div>
                <div class="relative rounded-xl border-2 p-5 cursor-pointer transition-all"
                    :class="form.system_mode === 'single' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500'"
                    @click="form.system_mode = 'single'">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">Single-Company Mode</span>
                            <span x-show="form.system_mode === 'single'" class="ml-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">Active</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Simplified operation without branch management. All inventory and transactions use a single company context.</p>
                </div>
            </div>

            <div class="mt-4 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800"
                x-show="form.system_mode === 'single' && form._initialSystemMode === 'multi_branch'">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    Switching to Single-Company Mode will keep all branch data but hide branch features from the interface. You can switch back any time.
                </p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 px-6 py-4">
        <div>
            <p x-show="saveStatus === 'saved'" class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Settings saved successfully</p>
            <p x-show="saveStatus === 'error'" class="text-sm text-red-600 dark:text-red-400 font-medium">Failed to save settings</p>
        </div>
        <button @click="saveSettings()" :disabled="saving" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <span x-show="!saving">Save Settings</span>
            <span x-show="saving" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Saving...
            </span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingsManager', () => ({
            activeTab: 'general',
            saving: false,
            saveStatus: null,
            form: {
                company_name: '',
                company_address: '',
                company_phone: '',
                company_email: '',
                currency: 'USD',
                timezone: 'UTC',
                grid_columns: '4',
                grid_rows: '4',
                default_tax_rate: 10,
                rounding_rule: 'none',
                sound_effects: true,
                payment_confirmation: true,
                receipt_header: '',
                receipt_footer: '',
                logo: null,
                logo_preview: null,
                receipt_copies: 1,
                paper_width: 80,
                receipt_auto_print: 'false',
                receipt_qr_enabled: 'false',
                receipt_qr_base_url: '',
                notification_duration: 3,
                notification_position: 'bottom-center',
                system_mode: 'multi_branch',
                _initialSystemMode: 'multi_branch',
            },

            async init() {
                await this.loadSettings();
            },

            async loadSettings() {
                try {
                    const data = await window.POS.api('/api/settings');
                    if (data && data.data) {
                        const settings = data.data;
                    if (settings && !Array.isArray(settings)) {
                        Object.keys(settings).forEach(k => {
                            if (this.form.hasOwnProperty(k)) {
                                const val = settings[k];
                                if (typeof this.form[k] === 'boolean') {
                                    this.form[k] = val === 'true' || val === '1' || val === true;
                                } else if (typeof this.form[k] === 'number') {
                                    this.form[k] = Number(val) || 0;
                                } else {
                                    this.form[k] = val ?? '';
                                }
                            }
                        });
                        this.form._initialSystemMode = this.form.system_mode || 'multi_branch';
                    } else if (Array.isArray(settings)) {
                        settings.forEach(s => {
                            if (this.form.hasOwnProperty(s.key)) {
                                if (typeof this.form[s.key] === 'boolean') {
                                    this.form[s.key] = s.value === 'true' || s.value === '1' || s.value === true;
                                } else if (typeof this.form[s.key] === 'number') {
                                    this.form[s.key] = Number(s.value) || 0;
                                } else {
                                    this.form[s.key] = s.value ?? '';
                                }
                            }
                        });
                        this.form._initialSystemMode = this.form.system_mode || 'multi_branch';
                    }
                    }
                } catch (e) {
                    console.error('Failed to load settings:', e);
                }
            },

            async saveSettings() {
                this.saving = true;
                this.saveStatus = null;
                try {
                    const settings = [];
                    const entries = Object.entries(this.form).filter(([k]) => !k.startsWith('_'));
                    for (const [key, value] of entries) {
                        settings.push({
                            key: key,
                            value: typeof value === 'boolean' ? (value ? 'true' : 'false') : String(value),
                        });
                    }
                    await window.POS.api('/api/settings', {
                        method: 'POST',
                        body: JSON.stringify({ settings }),
                    });
                    if (this.form.currency) {
                        const symbols = JSON.parse(document.querySelector('meta[name="currency-symbols"]')?.content || '{}');
                        const symbol = symbols[this.form.currency] || '$';
                        await window.POS.api('/api/settings', {
                            method: 'POST',
                            body: JSON.stringify({ key: 'currency_symbol', value: symbol }),
                        });
                        Alpine.store('currency').code = this.form.currency;
                        Alpine.store('currency').symbol = symbol;
                    }
                    const modeChanged = this.form.system_mode !== this.form._initialSystemMode;
                    this.form._initialSystemMode = this.form.system_mode || 'multi_branch';
                    this.saveStatus = 'saved';
                    setTimeout(() => { this.saveStatus = null; }, 3000);
                    if (modeChanged) {
                        setTimeout(() => { window.location.reload(); }, 1000);
                    }
                } catch (e) {
                    this.saveStatus = 'error';
                    setTimeout(() => { this.saveStatus = null; }, 3000);
                } finally {
                    this.saving = false;
                }
            },

            handleLogoUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.logo_preview = e.target.result;
                    this.form.logo = e.target.result;
                };
                reader.readAsDataURL(file);
            },

        }));
    });
</script>
@endpush
