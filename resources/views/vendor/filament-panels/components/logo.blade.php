@php
    $brandName = filament()->getBrandName();
    $brandLogo = filament()->getBrandLogo()
@endphp

@if (filled($brandLogo))
    <div class="flex items-center">
        <img
            src="{{ $brandLogo }}"
            loading="lazy"
            alt="{{ $brandName }}"
            width="200"
{{--            {{ $attributes->class(['fi-logo h-10']) }}--}}
        />
{{--        <div class="flex flex-col px-4 text-gray-950 dark:text-white">--}}
{{--            <span class="text-xs">{{ env('APP_VERSION') }}</span>--}}
{{--            {{ $brandName }}--}}
{{--        </div>--}}
    </div>

@else
    <div
        {{ $attributes->class(['fi-logo text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white']) }}
    >
        {{ $brandName }}
    </div>
@endif
