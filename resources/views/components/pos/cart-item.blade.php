<div class="flex flex-col gap-1.5 py-2.5 border-b border-gray-100 dark:border-white/[0.05] group hover:bg-gray-50 dark:hover:bg-white/[0.02] px-1 rounded transition-colors">
    <div class="flex items-center gap-2.5">
        <span
            class="bg-blue-500 text-white min-w-[20px] h-5 rounded-full text-[10px] flex items-center justify-center font-bold shrink-0 px-1"
        >{{ $quantity ?? 1 }}</span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90 truncate">{{ $name ?? 'Item' }}</p>
            <p class="text-[11px] text-gray-400 dark:text-white/35 font-mono">{{ $unitPrice ?? '$0.00' }} &times; {{ $quantity ?? 1 }}</p>
        </div>
        <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white shrink-0 tabular-nums">{{ $lineTotal ?? '$0.00' }}</p>
        @if(isset($removable) && $removable)
        <button type="button"
            class="text-gray-300 dark:text-white/15 hover:text-red-400 dark:hover:text-red-400 text-lg leading-none shrink-0 transition-colors p-0.5 focus:outline-none"
            {{ $removeAction ?? '' }}
            title="Remove"
        >&times;</button>
        @endif
    </div>
    <div class="flex items-center gap-1 ml-7">
        @if(isset($editable) && $editable)
        <button type="button"
            class="w-6 h-6 rounded bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-white/40 hover:bg-gray-200 dark:hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed text-sm font-bold flex items-center justify-center transition-colors focus:outline-none"
            {{ $decrementAction ?? '' }}
            :disabled="({{ $quantity ?? 1 }}) <= 1"
        >&minus;</button>
        <input
            type="number"
            value="{{ $quantity ?? 1 }}"
            min="1"
            class="w-12 h-6 text-center text-xs rounded bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-800 dark:text-white/80 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500/50 focus:border-blue-500"
            {{ $quantityChangeAction ?? '' }}
        >
        <button type="button"
            class="w-6 h-6 rounded bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-white/40 hover:bg-gray-200 dark:hover:bg-white/10 text-sm font-bold flex items-center justify-center transition-colors focus:outline-none"
            {{ $incrementAction ?? '' }}
        >+</button>
        @endif
        @if(isset($slot) && trim($slot))
        <div class="flex-1">{{ $slot }}</div>
        @endif
    </div>
    @if(isset($comment) && $comment)
    <p class="text-[10px] text-gray-400 dark:text-white/25 ml-7 italic truncate">{{ $comment }}</p>
    @endif
</div>