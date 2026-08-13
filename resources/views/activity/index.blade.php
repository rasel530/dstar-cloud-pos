@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div x-data="activityManager" class="flex flex-col h-full">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Activity Log</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Track all user activities and changes</p>
        </div>
    </div>

    <div class="px-4 sm:px-6 flex-1 overflow-hidden flex flex-col pb-4">
        {{-- Category Tabs --}}
        <div class="flex gap-1 overflow-x-auto pb-2 shrink-0">
            <template x-for="t in tabs" :key="t.key">
                <button @click="switchTab(t.key)" class="shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap"
                    :class="activeTab === t.key ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-[#1a1f3d] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5'"
                    x-text="t.label"></button>
            </template>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 p-3 sm:p-4 mb-3 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 shrink-0">
            <input type="text" x-model="filterSearch" @input.debounce.300ms="fetchLogs()" placeholder="Search action / reference..." class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 col-span-2 lg:col-span-2">
            <select x-model="filterUser" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <option value="">All Users</option>
                <template x-for="u in users" :key="u.id">
                    <option :value="u.id" x-text="(u.first_name || '') + ' ' + (u.last_name || '') || u.username"></option>
                </template>
            </select>
            <select x-model="filterEvent" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <option value="">All Actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="refunded">Refunded</option>
                <option value="paid">Paid</option>
                <option value="register_opened">Register Opened</option>
                <option value="register_closed">Register Closed</option>
                <option value="cash_in_out">Cash In/Out</option>
            </select>
            <select x-model="filterBranch" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <option value="">All Branches</option>
                <template x-for="b in branches" :key="b.id">
                    <option :value="b.id" x-text="b.name"></option>
                </template>
            </select>
            <input type="date" x-model="filterDateFrom" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
            <input type="date" x-model="filterDateTo" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
            <button @click="filterSearch=''; filterUser=''; filterEvent=''; filterBranch=''; filterDateFrom=''; filterDateTo=''; fetchLogs()" class="px-3 py-2 text-sm text-blue-500 hover:text-blue-700 col-span-2 sm:col-span-1">Clear</button>
        </div>

        {{-- Activity Table --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 flex flex-col flex-1 min-h-0">
            <div class="overflow-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-[#1a1f3d] z-10">
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3">Date & Time</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3">User</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3">Action</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3">Module</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3 hidden md:table-cell">Reference</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3 hidden lg:table-cell">Branch</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-4 py-3 hidden lg:table-cell">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <template x-if="loading">
                            <tr><td colspan="7" class="text-center py-12 text-gray-400">Loading...</td></tr>
                        </template>
                        <template x-for="log in logs" :key="log.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer" @click="viewDetail(log)">
                                <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 font-mono whitespace-nowrap" x-text="new Date(log.created_at).toLocaleString()"></td>
                                <td class="px-3 sm:px-4 py-3 text-gray-900 dark:text-white">
                                    <div x-text="(log.user?.first_name || '') + ' ' + (log.user?.last_name || '')"></div>
                                    <div class="text-xs text-gray-400" x-text="log.user?.username || log.user?.email"></div>
                                </td>
                                <td class="px-3 sm:px-4 py-3 text-gray-700 dark:text-gray-300" x-text="log.action"></td>
                                <td class="px-3 sm:px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400" x-text="log.module"></span>
                                </td>
                                <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 font-mono hidden md:table-cell" x-text="log.reference || '—'"></td>
                                <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 hidden lg:table-cell" x-text="log.branch?.name || '—'"></td>
                                <td class="px-3 sm:px-4 py-3 text-xs text-gray-400 font-mono hidden lg:table-cell" x-text="log.ip_address || '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="!loading && logs.length === 0">
                            <td colspan="7" class="text-center py-12 text-gray-400">No activity logs found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between shrink-0">
                <span class="text-xs text-gray-500" x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                <div class="flex gap-2">
                    <button @click="fetchLogs(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1 text-sm rounded-lg disabled:opacity-30 bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300">Prev</button>
                    <button @click="fetchLogs(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1 text-sm rounded-lg disabled:opacity-30 bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300">Next</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showDetail = false">
        <div class="absolute inset-0 bg-black/60" @click="showDetail = false"></div>
        <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] flex flex-col animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
            <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
            <div class="flex items-center justify-between px-5 pt-1 pb-3 border-b border-gray-200 dark:border-white/10 shrink-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Activity Details</h3>
                <button @click="showDetail = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="overflow-y-auto flex-1 px-5 py-3" x-show="selectedLog">
                <template x-if="selectedLog">
                    <div class="space-y-3">
                        <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5 space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">User</span><span class="font-semibold text-gray-900 dark:text-white" x-text="(selectedLog.user?.first_name || '') + ' ' + (selectedLog.user?.last_name || '')"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Action</span><span class="font-semibold text-gray-900 dark:text-white" x-text="selectedLog.action"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Module</span><span class="font-semibold text-gray-900 dark:text-white" x-text="selectedLog.module"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Event</span><span class="font-semibold text-gray-900 dark:text-white" x-text="eventLabel(selectedLog.event)"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Reference</span><span class="font-mono text-xs text-gray-900 dark:text-white" x-text="selectedLog.reference || '—'"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Date</span><span x-text="new Date(selectedLog.created_at).toLocaleString()"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Branch</span><span x-text="selectedLog.branch?.name || '—'"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Device</span><span x-text="selectedLog.device || '—'"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">IP Address</span><span class="font-mono" x-text="selectedLog.ip_address || '—'"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/50">Method</span><span x-text="selectedLog.method"></span></div>
                        </div>
                        <div x-show="selectedLog.details && Object.keys(selectedLog.details).length" class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                            <p class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase mb-2">Details / Changes</p>
                            <div class="space-y-1">
                                <template x-for="(val, key) in selectedLog.details" :key="key">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500 dark:text-white/50" x-text="key"></span>
                                        <span class="font-mono text-xs text-gray-900 dark:text-white" x-text="typeof val === 'object' ? JSON.stringify(val) : val"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
