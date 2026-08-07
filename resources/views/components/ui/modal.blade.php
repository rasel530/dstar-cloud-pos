@props(['maxWidth' => 'md', 'closeable' => true])

<div x-show="show" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    x-data="{ show: @entangle($attributes->wire('model')) }"
>
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        @@if($closeable) @@click="show = false" @@endif
    ></div>

    <div class="relative bg-white dark:bg-[#1a1f3d] rounded-xl w-full max-w-{{ $maxWidth }} mx-4 shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    >
        @if(isset($header))
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $header }}</h3>
            @if($closeable)
            <button @@click="show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors p-1 rounded-md hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            @endif
        </div>
        @endif

        <div class="px-6 py-4 overflow-y-auto max-h-[70vh]">
            {{ $slot }}
        </div>

        @if(isset($footer))
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f1535]/50">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>