@props(['type' => 'success', 'message' => '', 'show' => false, 'duration' => 3000, 'position' => 'bottom-center'])

@php
$positionClasses = match($position) {
    'top-right' => 'top-6 right-6',
    'top-left' => 'top-6 left-6',
    'top-center' => 'top-6 left-1/2 -translate-x-1/2',
    'bottom-right' => 'bottom-6 right-6',
    'bottom-left' => 'bottom-6 left-6',
    default => 'bottom-6 left-1/2 -translate-x-1/2',
};
$bgColor = match($type) {
    'error' => 'bg-red-500',
    'warning' => 'bg-amber-500',
    'info' => 'bg-blue-500',
    default => 'bg-emerald-500',
};
$icon = match($type) {
    'error' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>',
    default => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
};
@endphp

<div
    x-data="{ visible: false }"
    x-init="
        $watch('message', msg => { if (msg) { visible = true; setTimeout(() => visible = false, {{ $duration }}); } });
        if ('{{ $message }}') { visible = true; setTimeout(() => visible = false, {{ $duration }}); }
    "
    x-show="visible"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed z-[100] {{ $positionClasses }} pointer-events-none"
>
    <div class="flex items-center gap-2.5 {{ $bgColor }} text-white px-5 py-3 rounded-xl shadow-2xl text-sm font-semibold min-w-[200px] max-w-[calc(100vw-2rem)]">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $icon !!}</svg>
        <span x-text="typeof {{ $message }} === 'string' ? {{ $message }} : (message || '')" class="min-w-0">{{ $message }}</span>
    </div>
</div>