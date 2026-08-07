@extends('layouts.app')

@section('title', 'Branches')

@section('content')
<div x-data="branchesManager" x-init="fetchBranches()" class="px-6">
    <div class="flex items-center justify-between py-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Branches</h1>
        <button @click="openAdd()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Add Branch
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 font-medium">Branch Code</th>
                        <th class="px-6 py-3 font-medium">Name</th>
                        <th class="px-6 py-3 font-medium">Business Type</th>
                        <th class="px-6 py-3 font-medium">Address</th>
                        <th class="px-6 py-3 font-medium">Phone</th>
                        <th class="px-6 py-3 font-medium text-center">HQ?</th>
                        <th class="px-6 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="branch in branches" :key="branch.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="branch.branch_code"></td>
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-gray-100" x-text="branch.name"></td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': branch.business_type === 'cafe',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': branch.business_type === 'retail',
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': branch.business_type === 'supermarket',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300': branch.business_type === 'restaurant',
                                    }"
                                    x-text="branch.business_type_label || branch.business_type">
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-400 max-w-[200px] truncate" x-text="branch.address || '-'"></td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-400" x-text="branch.phone || '-'"></td>
                            <td class="px-6 py-3 text-center">
                                <template x-if="branch.is_headquarters">
                                    <svg class="w-5 h-5 text-yellow-500 inline-block" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd"/>
                                    </svg>
                                </template>
                                <template x-if="!branch.is_headquarters">
                                    <span class="text-gray-300 dark:text-gray-600">-</span>
                                </template>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button @click="openEdit(branch)" class="p-1.5 rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="branches.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 4.318A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                            </svg>
                            <p class="text-sm">No branches found</p>
                            <p class="text-xs mt-1">Click "Add Branch" to create your first location.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="showModal = false">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editing ? 'Edit Branch' : 'Add Branch'"></h2>
                <button @click="showModal = false" class="p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="save()" class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input x-model="form.name" type="text" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Branch name">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Code</label>
                    <input x-model="form.branch_code" type="text" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., HQ-001">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Business Type</label>
                    <input x-model="form.business_type" type="text" list="business-types" 
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Cafe, Pharmacy, Boutique...">
                                        <datalist id="business-types">
                        @foreach(config('business.types', []) as $key => $info)
                        <option value="{{ $info['label'] }}">{{ $info['label'] }}</option>
                        @endforeach
                        @foreach(config('business.other_types', []) as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                        <template x-for="b in uniqueBusinessTypes" :key="b">
                            <option :value="b" x-text="b"></option>
                        </template>
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea x-model="form.address" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Street address"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input x-model="form.phone" type="text" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Phone number">
                </div>

                <div class="flex items-center gap-2">
                    <input x-model="form.is_headquarters" type="checkbox" id="is_hq" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                    <label for="is_hq" class="text-sm font-medium text-gray-700 dark:text-gray-300">Is HQ?</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <template x-if="editing">
                        <button type="button" @click="deleteBranch(form.id)" class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors mr-auto">Delete</button>
                    </template>
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors" x-text="editing ? 'Update' : 'Save'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('sidebar-nav')
    <a href="{{ route('web.branches.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('web.branches.index') ? 'bg-blue-600/20 text-blue-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}" title="Branches">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
        </svg>
        <span x-show="sidebarOpen" x-cloak class="whitespace-nowrap">Branches</span>
    </a>
@endsection