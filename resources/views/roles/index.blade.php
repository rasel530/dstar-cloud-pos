@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div x-data="rolesManager" x-init="init()">

    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Roles & Permissions</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Create custom roles and assign module access</p>
        </div>
        <button @click="openAdd()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Role
        </button>
    </div>

    <div class="px-4 sm:px-6 flex-1 overflow-hidden flex flex-col pb-4">
        <template x-if="loading">
            <div class="flex justify-center py-12"><svg class="animate-spin h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg></div>
        </template>

        <template x-if="!loading">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="role in roles" :key="role.id">
                    <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white" x-text="role.name"></h3>
                                <p class="text-xs text-gray-400 mt-0.5">Level: <span x-text="role.access_level"></span></p>
                            </div>
                            <span :class="{
                                'bg-purple-500/20 text-purple-400': role.access_level >= 9,
                                'bg-blue-500/20 text-blue-400': role.access_level >= 5 && role.access_level < 9,
                                'bg-gray-500/20 text-gray-400': role.access_level < 5,
                            }" class="px-2 py-0.5 rounded-full text-xs font-medium">Lv <span x-text="role.access_level"></span></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3" x-text="role.description || 'No description'"></p>
                        <div class="flex flex-wrap gap-1 mb-3">
                            <template x-for="p in role.permissions" :key="p.module">
                                <span class="px-1.5 py-0.5 bg-blue-500/10 text-blue-400 rounded text-xs" x-text="moduleLabels[p.module] || p.module"></span>
                            </template>
                            <span x-show="!role.permissions || role.permissions.length === 0" class="text-xs text-gray-400">No modules assigned</span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openEdit(role)" class="flex-1 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 text-xs font-medium py-1.5 rounded transition">Edit</button>
                            <button @click="deleteRole(role.id)" x-show="role.tenant_id !== null" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-medium px-3 py-1.5 rounded transition">Delete</button>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showModal = false">
        <div class="fixed inset-0 bg-black/60"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-xl w-full max-w-lg border border-slate-200 dark:border-slate-700 shadow-2xl" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold text-slate-900 dark:text-white" x-text="editing ? 'Edit Role' : 'Add Role'"></h3>
                <button @click="showModal = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 text-slate-400">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Role Name</label>
                    <input x-model="form.name" class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm" placeholder="e.g., Supervisor">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Access Level (0-99)</label>
                    <input x-model="form.access_level" type="number" min="0" max="99" class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <input x-model="form.description" class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Module Permissions</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="(info, m) in allModules" :key="m">
                            <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                                <input type="checkbox" :value="m" :checked="form.permissions.includes(m)" @change="togglePermission(m)" class="rounded">
                                <span x-text="info.label || m" class="capitalize"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button @click="save()" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-lg transition" x-text="editing ? 'Update' : 'Create'"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rolesManager', () => ({
        roles: [], loading: true, showModal: false, editing: false, editId: null,
        allModules: {},
        moduleLabels: {},
        form: { name: '', access_level: 0, description: '', permissions: [] },
        async init() { await this.fetchModules(); await this.fetchRoles(); },
        async fetchModules() {
            try {
                const r = await window.POS.api('/api/modules');
                this.allModules = r?.data || {};
                this.moduleLabels = {};
                Object.entries(this.allModules).forEach(([k, v]) => { this.moduleLabels[k] = v.label || k; });
            } catch(e) { this.allModules = {}; }
        },
        async fetchRoles() {
            this.loading = true;
            try { const r = await window.POS.api('/api/roles'); this.roles = r?.data || []; } catch(e) { this.roles = []; } finally { this.loading = false; }
        },
        openAdd() { this.editing = false; this.editId = null; this.form = { name: '', access_level: 0, description: '', permissions: [] }; this.showModal = true; },
        openEdit(role) {
            this.editing = true; this.editId = role.id;
            this.form = { name: role.name, access_level: role.access_level, description: role.description || '', permissions: (role.permissions || []).map(p => p.module) };
            this.showModal = true;
        },
        togglePermission(module) {
            const idx = this.form.permissions.indexOf(module);
            if (idx >= 0) this.form.permissions.splice(idx, 1);
            else this.form.permissions.push(module);
        },
        async save() {
            try {
                const url = this.editing ? '/api/roles/' + this.editId : '/api/roles';
                const method = this.editing ? 'PUT' : 'POST';
                await window.POS.api(url, { method, body: JSON.stringify(this.form) });
                this.showModal = false; this.fetchRoles();
            } catch(e) { alert(e.message); }
        },
        async deleteRole(id) { if (!confirm('Delete this role?')) return; try { await window.POS.api('/api/roles/' + id, { method: 'DELETE' }); this.fetchRoles(); } catch(e) { alert(e.message); } },
    }));
});
</script>
@endsection
