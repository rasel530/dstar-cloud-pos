<div x-data="virtualKeyboard" class="select-none">
    <div class="grid grid-cols-3 gap-2 w-full max-w-xs mx-auto">
        <template x-for="key in ['7','8','9']" :key="key">
            <button @click="append(key)" class="rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500" x-text="key"></button>
        </template>
        <template x-for="key in ['4','5','6']" :key="key">
            <button @click="append(key)" class="rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500" x-text="key"></button>
        </template>
        <template x-for="key in ['1','2','3']" :key="key">
            <button @click="append(key)" class="rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500" x-text="key"></button>
        </template>
        <button @click="append('0')" class="rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500">0</button>
        <button @click="append('.')" class="rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500">.</button>
        <button @click="backspace()" class="rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500">&larr;</button>
    </div>
    <div class="grid grid-cols-2 gap-2 mt-2 w-full max-w-xs mx-auto">
        <button @click="clear()" class="rounded-lg bg-red-700 hover:bg-red-600 text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-red-500 font-semibold">Clear</button>
        <button @click="confirm()" class="rounded-lg bg-green-700 hover:bg-green-600 text-white text-lg py-3 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-green-500 font-semibold">Enter</button>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('virtualKeyboard', () => ({
        value: '',

        append(char) {
            if (char === '.' && this.value.includes('.')) return;
            this.value += char;
            this.$dispatch('keyboard-input', { value: this.value });
        },

        clear() {
            this.value = '';
            this.$dispatch('keyboard-input', { value: this.value });
        },

        backspace() {
            this.value = this.value.slice(0, -1);
            this.$dispatch('keyboard-input', { value: this.value });
        },

        confirm() {
            this.$dispatch('keyboard-confirm', { value: this.value });
        },
    }));
});
</script>
