@props([
    'isActive' => false,
    'title' => '',
    'collapsible' => false
])

@php
    use App\Models\Setting;
    
    $primaryColor = Setting::get('button_primary_color', Setting::get('theme_color', '#842eb8'));
    
    // Helper function to darken a hex color
    if (!function_exists('darkenColor')) {
        function darkenColor($hex, $percent = 15) {
            $hex = str_replace('#', '', $hex);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            
            $r = max(0, min(255, $r - ($r * $percent / 100)));
            $g = max(0, min(255, $g - ($g * $percent / 100)));
            $b = max(0, min(255, $b - ($b * $percent / 100)));
            
            return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
                       str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
                       str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
        }
    }
    
    $hoverColor = darkenColor($primaryColor, 15);
    
    if ($isActive) {
        $isActiveClasses = 'text-white shadow-lg';
        $inlineStyles = "background-color: {$primaryColor}; --sidebar-hover-color: {$hoverColor};";
    } else {
        $isActiveClasses = 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-dark-eval-2';
        $inlineStyles = '';
    }

    $classes = 'flex-shrink-0 flex items-center gap-2 p-2 transition-colors rounded-md overflow-hidden ' . $isActiveClasses;

    if($collapsible) $classes .= ' w-full';
@endphp

@if ($collapsible)
    <button type="button" {{ $attributes->merge(['class' => $classes]) }} @if($inlineStyles) style="{{ $inlineStyles }}" @endif>
        @if ($icon ?? false)
            {{ $icon }}
        @else
            <x-icons.empty-circle class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        @endif

        <span
            class="text-base font-medium whitespace-nowrap"
            x-show="isSidebarOpen || isSidebarHovered"
        >
            {{ $title }}
        </span>

        <span
            x-show="isSidebarOpen || isSidebarHovered"
            aria-hidden="true"
            class="relative block ml-auto w-6 h-6"
        >
            <span
                :class="open ? '-rotate-45' : 'rotate-45'"
                class="absolute right-[9px] bg-gray-400 mt-[-5px] h-2 w-[2px] top-1/2 transition-all duration-200"
            ></span>

            <span
                :class="open ? 'rotate-45' : '-rotate-45'"
                class="absolute left-[9px] bg-gray-400 mt-[-5px] h-2 w-[2px] top-1/2 transition-all duration-200"
            ></span>
        </span>
    </button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }} @if($inlineStyles) style="{{ $inlineStyles }}" @endif>
        @if ($icon ?? false)
            {{ $icon }}
        @else
            <x-icons.empty-circle class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
        @endif

        <span
            class="text-base font-medium"
            x-show="isSidebarOpen || isSidebarHovered"
        >
            {{ $title }}
        </span>
    </a>
@endif

@if($isActive && $inlineStyles)
    <style>
        [style*="--sidebar-hover-color"]:hover {
            background-color: var(--sidebar-hover-color) !important;
        }
    </style>
@endif
