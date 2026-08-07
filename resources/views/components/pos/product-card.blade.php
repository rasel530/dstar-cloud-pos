<div class="relative group"
    x-data="{ loaded: false }"
    x-init="loaded = true"
>
    <button {{ $attributes->merge([
        'class' => 'rounded-xl p-4 text-white text-center cursor-pointer hover:brightness-110 hover:scale-[1.02] active:scale-95 transition-all duration-150 shadow-md flex flex-col items-center justify-center gap-2 min-h-[96px] w-full'
    ]) }}
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mb-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
        </div>
        <span class="font-semibold text-sm leading-tight line-clamp-2">{{ $name ?? '' }}</span>
        @if(isset($price))
        <span class="text-xs opacity-85 font-mono tracking-tight mt-0.5">{{ $price }}</span>
        @endif
        @if(isset($stock) && $stock <= 0)
        <span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">OOS</span>
        @endif
        @if(isset($stock) && $stock > 0 && $stock <= 5)
        <span class="absolute top-2 right-2 bg-amber-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">Low</span>
        @endif
    </button>
</div>