@extends('layouts.app')

@section('title', 'Users')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="usersManager" class="flex flex-col h-full">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage system users</p>
        </div>
        <button
            @click="openAdd()"
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Emp#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">PIN</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-white/5 bg-white dark:bg-[#1a1f3d]">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-400 dark:text-gray-500">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span class="text-sm">Loading users...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && users.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                </svg>
                                <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding your first user.</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="u in users" :key="u.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 flex-shrink-0 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400" x-text="(u.first_name?.charAt(0) + u.last_name?.charAt(0)).toUpperCase()"></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="u.username"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="(u.first_name || '') + ' ' + (u.last_name || '')"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono" x-text="u.employee_number || '—'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300" x-text="u.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span x-show="u.pin_set" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400">Set</span>
                                <span x-show="!u.pin_set" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400': u.access_level >= 9,
                                        'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400': u.access_level >= 5 && u.access_level < 9,
                                        'bg-gray-100 dark:bg-[#0f1535] text-gray-600 dark:text-gray-400': u.access_level < 5,
                                    }"
                                    x-text="getRoleName(u.access_level)"
                                ></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button
                                    @click="toggleStatus(u)"
                                    type="button"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    :class="u.is_enabled ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                                    :aria-checked="u.is_enabled"
                                    role="switch"
                                >
                                    <span
                                        class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                        :class="u.is_enabled ? 'translate-x-6' : 'translate-x-1'"
                                    ></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        @click="openEdit(u)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/15 border border-transparent transition whitespace-nowrap"
                                        title="Edit"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button
                                        @click="deleteUser(u.id)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20 border border-transparent transition whitespace-nowrap"
                                        title="Delete"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-6 py-3 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5" x-show="pagination.total > 0">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                <span class="mx-1 text-gray-400 dark:text-gray-500">&middot;</span>
                <span x-text="pagination.total + ' user' + (pagination.total !== 1 ? 's' : '')"></span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="fetchUsers(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Prev
                </button>
                <button
                    @click="fetchUsers(pagination.current_page + 1)"
                    :disabled="!pagination.next_page_url"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    Next
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div
        x-show="showModal"
        x-cloak
        x-trap.noscroll="showModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="showModal"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/70 backdrop-blur-sm"
            @click="showModal = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="showModal"
                x-transition
                @click.stop
                class="relative w-full max-w-lg bg-white dark:bg-[#1a1f3d] rounded-xl shadow-2xl border border-gray-200 dark:border-white/10"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit User' : 'Add User'"></h3>
                    <button
                        @click="showModal = false"
                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="save()" class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.first_name"
                                required
                                placeholder="First name"
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.last_name"
                                required
                                placeholder="Last name"
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.username"
                            required
                            placeholder="Username"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Employee ID <span class="text-xs text-gray-400">(for PIN login)</span>
                        </label>
                        <input
                            type="number"
                            x-model.number="form.employee_number"
                            min="1" max="65535"
                            placeholder="e.g. 101"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            PIN Code <span class="text-xs text-gray-400">(4 digits for quick login)</span>
                        </label>
                        <input
                            type="password"
                            x-model="form.pin_code"
                            maxlength="4"
                            placeholder="Leave blank to keep current"
                            autocomplete="off"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                        <p x-show="form.pin_code && form.pin_code.length !== 4" class="text-xs text-amber-500 mt-1">PIN must be exactly 4 digits</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            x-model="form.email"
                            required
                            placeholder="Email address"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Password <span x-text="editing ? '' : '*'" class="text-red-500"></span>
                        </label>
                        <div class="relative">
                            <input
                                :type="showPwd ? 'text' : 'password'"
                                x-model="form.password"
                                :required="!editing"
                                :placeholder="editing ? 'Leave blank to keep current' : 'Password'"
                                class="w-full px-3 py-2 pr-10 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                            <button type="button" @click="showPwd = !showPwd" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Access Level
                        </label>
                        <select
                            x-model="form.access_level"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        >
                            <template x-for="r in roles" :key="r.id">
                                <option :value="r.access_level" x-text="r.name + ' (Lv' + r.access_level + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Branch
                        </label>
                        <select
                            x-model="form.branch_id"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        >
                            <option value="">— All Branches —</option>
                            <template x-for="b in branches" :key="b.id">
                                <option :value="b.id" x-text="b.name + ' (' + (b.branch_code || 'No Code') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="form.branch_id === ''">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Or assign to multiple branches
                        </label>
                        <div class="grid grid-cols-2 gap-1.5 max-h-32 overflow-y-auto border border-gray-200 dark:border-white/10 rounded-lg p-2">
                            <template x-for="b in branches" :key="'mb' + b.id">
                                <label class="flex items-center gap-1.5 text-xs cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 px-1.5 py-1 rounded">
                                    <input type="checkbox" :value="b.id" :checked="form.branch_ids.includes(b.id)" @change="toggleBranch(b.id)" class="rounded">
                                    <span x-text="b.branch_code || b.name" class="truncate"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 py-1">
                        <button
                            type="button"
                            @click="form.is_enabled = !form.is_enabled"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            :class="form.is_enabled ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                            :aria-checked="form.is_enabled"
                            role="switch"
                        >
                            <span
                                class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                :class="form.is_enabled ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="form.is_enabled ? 'Enabled' : 'Disabled'"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Can Edit POS Price</label>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Allow this user to change cart item prices in POS</p>
                        </div>
                        <button
                            type="button"
                            @click="form.can_edit_price = !form.can_edit_price"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            :class="form.can_edit_price ? 'bg-emerald-600 dark:bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'"
                            :aria-checked="form.can_edit_price"
                            role="switch"
                        >
                            <span
                                class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                :class="form.can_edit_price ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                    </div>

                    <div
                        x-show="error"
                        x-text="error"
                        class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg px-4 py-3"
                    ></div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <svg
                                x-show="saving"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span x-text="saving ? 'Saving...' : 'Save'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
