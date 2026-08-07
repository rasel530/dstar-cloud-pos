@extends('layouts.app')

@section('title', 'Loyalty Cards')

@section('content')
<div x-data="loyaltyManager" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Loyalty Cards</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage customer loyalty program</p>
        </div>
        <button @click="showAddCard = true" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Card
        </button>
    </div>

    {{-- Table --}}
    <div class="px-6">
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5">
            <template x-if="loading">
                <div class="flex justify-center py-12">
                    <svg class="animate-spin h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </template>

            <template x-if="!loading && cards.length">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-gray-100 dark:border-white/5">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Customer</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Card #</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Balance</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Total Earned</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Actions</th>
                        </tr></thead>
                        <tbody>
                            <template x-for="card in cards" :key="card.id">
                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-6 py-3" x-text="card.customer?.name || 'Unknown'"></td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500 dark:text-white/70" x-text="card.card_number || '--'"></td>
                                    <td class="px-6 py-3 text-right font-mono font-bold text-gray-900 dark:text-white" x-text="card.points_balance || 0"></td>
                                    <td class="px-6 py-3 text-right text-gray-500 dark:text-white/70" x-text="card.total_points_earned || 0"></td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click="openPointsModal(card, 'earn')" class="px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 hover:bg-green-200 transition">+ Earn</button>
                                            <button @click="openPointsModal(card, 'redeem')" class="px-2 py-1 text-xs rounded bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 hover:bg-red-200 transition">− Redeem</button>
                                            <button @click="deleteCard(card.id)" class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-red-500 transition">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <template x-if="!loading && !cards.length">
                <div class="text-center py-16 text-gray-400 dark:text-white/30">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    <p class="text-sm">No loyalty cards found.</p>
                    <button @click="showAddCard = true" class="mt-3 text-blue-500 hover:text-blue-400 text-sm">Create your first card</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Add Card Modal --}}
    <div x-show="showAddCard" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showAddCard = false">
        <div class="fixed inset-0 bg-black/60"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-xl w-full max-w-md border border-slate-200 dark:border-slate-700 shadow-2xl" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900 dark:text-white">Create Loyalty Card</h3>
                <button @click="showAddCard = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Customer</label>
                    <select x-model="newCard.customer_id" class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm">
                        <option value="">Select customer...</option>
                        <template x-for="c in customers" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>
                <button @click="createCard()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-lg transition">Create Card</button>
            </div>
        </div>
    </div>

    {{-- Earn/Redeem Points Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showModal = false">
        <div class="fixed inset-0 bg-black/60"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-xl w-full max-w-sm border border-slate-200 dark:border-slate-700 shadow-2xl" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900 dark:text-white" x-text="transactionType === 'earn' ? 'Earn Points' : 'Redeem Points'"></h3>
                <button @click="showModal = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-slate-500 dark:text-slate-400" x-text="'Current balance: ' + (selectedCard?.points_balance || 0) + ' pts'"></p>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Points</label>
                    <input type="number" x-model="points" min="1" class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm">
                </div>
                <button @click="processPoints()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-lg transition" x-text="transactionType === 'earn' ? 'Earn Points' : 'Redeem Points'"></button>
            </div>
        </div>
    </div>
</div>
@endsection
