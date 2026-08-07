@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div x-data="activityManager" class="flex flex-col h-full">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Activity Log</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Track all user activities</p>
        </div>
    </div>

    <div class="px-4 sm:px-6 flex-1 overflow-hidden flex flex-col pb-4">
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 p-3 sm:p-4 mb-3 sm:mb-4 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 shrink-0">
            <select x-model="filterModule" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <option value="">All Modules</option>
                <option value="POS">POS</option><option value="Orders">Orders</option><option value="Products">Products</option>
                <option value="Customers">Customers</option><option value="Reports">Reports</option><option value="Promotions">Promotions</option>
                <option value="Loyalty">Loyalty</option><option value="Users">Users</option><option value="Roles">Roles</option>
                <option value="Branches">Branches</option><option value="Taxes">Taxes</option><option value="Printers">Printers</option>
                <option value="Fiscal">Fiscal</option><option value="Settings">Settings</option>
                <option value="Inventory">Inventory</option>
            </select>
            <input type="date" x-model="filterDateFrom" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border">
            <input type="date" x-model="filterDateTo" @change="fetchLogs()" class="px-3 py-2 text-sm rounded-lg border">
            <button @click="filterModule=''; filterDateFrom=''; filterDateTo=''; fetchLogs()" class="px-3 py-2 text-sm text-blue-500 hover:text-blue-700">Clear Filters</button>
        </div>

        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 flex flex-col flex-1 min-h-0">
            <div class="overflow-auto flex-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Event Category</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Event Description</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Performed By</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Event Time</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Client IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="loading">
                            <tr><td colspan="5" class="text-center py-12 text-gray-400">Loading...</td></tr>
                        </template>
                        <template x-for="log in logs" :key="log.id">
                            <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-3 sm:px-6 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400" x-text="log.module"></span>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300" x-text="log.action"></td>
                                <td class="px-6 py-3 text-gray-900 dark:text-white">
                                    <div x-text="(log.user?.first_name || '') + ' ' + (log.user?.last_name || '')"></div>
                                    <div class="text-xs text-gray-400" x-text="log.user?.email"></div>
                                </td>
                                <td class="px-6 py-3 text-xs text-gray-500 font-mono" x-text="new Date(log.created_at).toLocaleString()"></td>
                                <td class="px-6 py-3 text-xs text-gray-400 font-mono" x-text="log.ip_address || '--'"></td>
                            </tr>
                        </template>
                        <tr x-show="!loading && logs.length === 0">
                            <td colspan="5" class="text-center py-12 text-gray-400">No activity logs found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div x-show="pagination" class="flex items-center justify-between px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-white/5 shrink-0">
                <button @click="fetchLogs(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="text-sm text-blue-500 disabled:opacity-30">Prev</button>
                <span class="text-xs text-gray-500" x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                <button @click="fetchLogs(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="text-sm text-blue-500 disabled:opacity-30">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection
