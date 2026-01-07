@props([
    'title' => '',
    'active' => false
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
    
    $classes = 'transition-colors hover:text-gray-900 dark:hover:text-gray-100 block px-2 py-1 rounded-md';
    $inlineStyles = '';
    
    if ($active) {
        $classes .= ' text-white font-medium';
        $inlineStyles = "background-color: {$primaryColor}; --sublink-hover-color: {$hoverColor};";
    } else {
        $classes .= ' text-gray-500 dark:text-gray-400';
    }
@endphp

<li class="relative leading-8 m-0 pl-6 last:before:bg-white last:before:h-auto last:before:top-4 last:before:bottom-0 dark:last:before:bg-dark-eval-1 before:block before:w-4 before:h-0 before:absolute before:left-0 before:top-4 before:border-t-2 before:border-t-gray-200 before:-mt-0.5 dark:before:border-t-gray-600">
    <a {{ $attributes->merge(['class' => $classes]) }} @if($inlineStyles) style="{{ $inlineStyles }}" @endif>
        {{ $title }}
    </a>
</li>

@if($active && $inlineStyles)
    <style>
        [style*="--sublink-hover-color"]:hover {
            background-color: var(--sublink-hover-color) !important;
        }
    </style>
@endif
